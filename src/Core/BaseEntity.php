<?php

namespace App\Core;

use App\Core\Data\DataTransformer;
use App\Core\Database\QueryBuilder;
use App\Core\Validator\Validator;
use App\Entities\Unit;
use MongoDB\Driver\Query;
use ReflectionClass;
use ReflectionException;

class BaseEntity
{
    protected ?string $entity = null;
    protected ?string $createdAt;
    protected ?string $updatedAt;
    protected ?string $deletedAt;


    public function __construct()
    {
        if ($this->entity === null) {
            $class = explode('\\', get_class($this));
            $entity = preg_split('#(?=[A-Z])#', end($class));
            array_shift($entity);
            $this->entity = strtolower(implode('s_', $entity).'s');
        }
    }


    public function __set(string $name, $value): void
    {
        $this->hydrateField($name, $value);
    }


    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->hydrateField($key, $value);
        }
    }


    private function hydrateField(string $name, $value): void
    {
        if (substr($name, 0, 3) === 'id_') {
            $entity = 'App\\Entities\\'.ucfirst(substr($name, 3));
            $entity = new $entity();
            $value = $entity->find($value);
        }

        $method = $this->getMethodName($name, 'set');
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }


    private function getMethodName(string $name, string $type = 'get'): string
    {
        if (substr($name, 0, 3) === 'id_') {
            $name = substr($name, 3);
        }
        return $type . join(array_map('ucfirst', explode('_', $name)));
    }


    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }


    public function setCreatedAt(?string $createdAt): self
    {
        $validator = new Validator(['createdAt' => $createdAt]);
        $validator->isDateTime('createdAt');
        if ($validator->isValid()) {
            $this->createdAt = $createdAt;
        }

        return $this;
    }


    public function getUpdatedAt(): ?string
    {
        return $this->createdAt;
    }


    public function setUpdatedAt(?string $updatedAt): self
    {
        $validator = new Validator(['updatedAt' => $updatedAt]);
        $validator->isDateTime('updatedAt');
        if ($validator->isValid()) {
            $this->updatedAt = $updatedAt;
        }

        return $this;
    }


    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }


    public function setDeletedAt(?string $deletedAt): self
    {
        $validator = new Validator(['deletedAt' => $deletedAt]);
        $validator->isDateTime('deletedAt');
        if ($validator->isValid()) {
            $this->deletedAt = $deletedAt;
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getAll(): array
    {
        $q = new QueryBuilder();
        return $q->select()->from($this->entity)->execute(get_class($this));
    }


    /**
     * @return array|mixed|null
     */
    public function find(int $id)
    {
        $q = new QueryBuilder();
        return $q->select()->from($this->entity)->where('id = '.$id)->limit(1)->execute(get_class($this));
    }


    /**
     * @return array|mixed
     */
    public function findAll(string $column, $value)
    {
        $q = new QueryBuilder();
        return $q->select()->from($this->entity)->where($column.' = '.$value)->execute(get_class($this));
    }


    /**
     * @return bool|int
     */
    public function save()
    {
        $values = [];
        // Prepare persisted properties for the query
        foreach ($this->getPersistedEntityProperties() as $property => $type) {
            if ($type === 'array') {
                $property = rtrim($property, 'List').'s';
            }

            $method = $this->getMethodName($property);
            $property = strtolower(join('_', preg_split('#(?=[A-Z])#', $property)));
            if (method_exists($this, $method)) {
                if (strpos($type, '\\')) {
                    // If property is a OneToMany or ManyToMany relation, get the ID of the property
                    $values['id_'.$property] = $this->$method()->getId();
                } elseif ($type === 'array') {
                    // If property is an array, transform it to a string
                    $dataTransformer = new DataTransformer();
                    $values[$property] = $dataTransformer->arrayToStringArray($this->$method());
                } else {
                    $values[$property] = $this->$method();
                }
            }
        }

        $q = new QueryBuilder();
        if (!isset($values['id']) || $values['id'] === null) {
            $result = $q->insert($this->entity)->setValues($values)->execute();
            $type = 'insert';
        } else {
            if ($q->update($this->entity)->setValues($values)->where('id = '.$values['id'])->execute()) {
                $result = $values['id'];
            }
            $type = 'update';
        }

        if (!empty($this->getManyToManyProperties()) && $result) {
            $this->saveManyToMany($result, $type);
        }

        return $result;
    }


    public function remove(): bool
    {
        $q = new QueryBuilder();
        return $q->delete($this->entity)->where('id = '.$this->getId())->execute();
    }


    /**
     * @throws ReflectionException
     */
    private function saveManyToMany(int $id, string $type = 'insert')
    {
        foreach ($this->getManyToManyProperties() as $property) {
            $method = $this->getMethodName($property);

            foreach ($this->$method() as $field) {
                $mappingTable = $this->entity.'_'.$property;
                $values = [
                    'id_'.rtrim($this->entity, 's') => $id,
                    'id_'.rtrim($property, 's') => $field->getId()
                ];

                $q = new QueryBuilder();
                if ($type === 'update') {
                    $q->delete($mappingTable)->where('id_'.rtrim($this->entity, 's').' = '.$id)->execute();
                }
                $q->insert($mappingTable)->setValues($values)->execute();
            }
        }
    }


    /**
     * Get all persisted properties (excepted ManyToMany) and their type of the extended class (Ex: User class)
     * @throws ReflectionException
     */
    private function getPersistedEntityProperties(?string $entity = null): array
    {
        $properties = [];
        foreach ($this->getAllProperties($entity) as $property) {
            if ($property->class === get_class($this) || $property->class === $entity) {
                preg_match('#@([A-Za-z]+)#', $property->getDocComment(), $phpDoc);
                if (!array_key_exists(1, $phpDoc) || (array_key_exists(1, $phpDoc) && $phpDoc[1] !== 'ManyToMany' && $phpDoc[1] !== 'NotPersisted')) {
                    $properties[$property->getName()] = $property->getType()->getName();
                }
            }
        }

        return $properties;
    }


    /**
     * Get all ManyToMany properties of the extended class (Ex: User class)
     * @throws ReflectionException
     */
    private function getManyToManyProperties(): array
    {
        $properties = [];
        foreach ($this->getAllProperties() as $property) {
            if ($property->class === get_class($this)) {
                preg_match('#@ManyToMany ([A-Za-z]+)#', $property->getDocComment(), $phpDoc);
                if (array_key_exists(1, $phpDoc)) {
                    $properties[$property->getName()] = $phpDoc[1];
                }
            }
        }

        return $properties;
    }


    /**
     * @throws ReflectionException
     */
    private function getAllProperties(?string $entity = null): array
    {
        $class = get_class($this);
        if ($entity !== null) {
            $class = $entity;
        }

        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->getProperties();
    }
}
