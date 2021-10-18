<?php

namespace App\Entities;

use App\Core\BaseEntity;
use App\Core\Data\DataTransformer;
use App\Core\Security\AuthController;
use App\Core\Validator\Validator;
use App\Core\Database\QueryBuilder;
use DateTime;
use Exception;

class User extends BaseEntity
{
    private ?int $id = null;
    private ?string $firstname = null;
    private ?string $lastname = null;
    private ?string $email = null;
    private ?string $password = null;
    private array $userroles = [];
    private ?string $lastLogin = null;
    private ?string $confirmedAt = null;
    private ?int $numberLogin = null;


    public function __construct()
    {
        parent::__construct();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }


    public function getFirstName(): ?string
    {
        return $this->firstname;
    }


    public function setFirstname(?string $firstname): self
    {
        if (strlen($firstname) <= 255) {
            $this->firstname = $firstname;
        }

        return $this;
    }


    public function getLastname(): ?string
    {
        return $this->lastname;
    }


    public function setLastname(?string $lastname): self
    {
        if (strlen($lastname) <= 255) {
            $this->lastname = $lastname;
        }

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(?string $email): self
    {
        $validator = new Validator(['email' => $email]);
        $validator->isEmail('email');
        if (strlen($email) <= 255 && $validator->isValid()) {
            $this->email = $email;
        }

        return $this;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function setPassword(?string $password): self
    {
        if (strlen($password) <= 255) {
            $this->password = $password;
        }

        return $this;
    }


    public function getRoles(): array
    {
        return $this->userroles;
    }


    /**
     * @param array|string $roles
     * @return User
     */
    public function setRoles($roles): self
    {
        if (is_array($roles)) {
            $this->userroles = $roles;
        } else {
            $dataTransformer = new DataTransformer();
            $this->userroles = $dataTransformer->stringArrayToArray($roles);
        }
        return $this;
    }


    public function addRoles(array $roles): self
    {
        if ($this->userroles[0] === '') {
            array_shift($this->userroles);
        }
        foreach ($roles as $role) {
            if (!in_array($role, $this->userroles)) {
                $this->userroles[] = $role;
            }
        }

        return $this;
    }


    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }


    /**
     * @throws Exception
     */
    public function getLastlogin(): ?DateTime
    {
        return new DateTime($this->lastLogin);
    }


    public function setLastlogin(?string $lastLogin): self
    {
        $validator = new Validator(['lastLogin' => $lastLogin]);
        $validator->isDateTime('lastLogin');
        if ($validator->isValid()) {
            $this->lastLogin = $lastLogin;
        }

        return $this;
    }


    /**
     * @throws Exception
     */
    public function getConfirmedAt(): ?DateTime
    {
        return new DateTime($this->confirmedAt);
    }


    public function setConfirmedAt(?string $confirmedAt): self
    {
        $validator = new Validator(['confirmedAt' => $confirmedAt]);
        $validator->isDateTime('confirmedAt');
        if ($validator->isValid()) {
            $this->confirmedAt = $confirmedAt;
        }

        return $this;
    }


    public function isConfirmed(): bool
    {
        return $this->confirmedAt ?? true;
    }


    public function getNumberlogin(): ?int
    {
        return $this->numberLogin;
    }


    public function setNumberlogin(?int $numberLogin = null): self
    {
        if ($numberLogin === null) {
            $this->numberLogin++;
        } else {
            $this->numberLogin = $numberLogin;
        }

        return $this;
    }


    public function exists(string $email): bool
    {
        $q = new QueryBuilder();
        $result = $q->select('COUNT(id) AS number')
            ->from($this->entity)
            ->where('email = "'.$email.'"')
            ->limit(1)
            ->execute();

        return (int)$result->number === 1;
    }


    public function isGranted(string $role): bool
    {
        $authController = new AuthController();
        if ($authController->isAuthenticated()) {
            if ($this->hasRole(strtoupper($role))) {
                return true;
            }
        }

        return false;
    }


    public function findByEmail(string $email): self
    {
        $q = new QueryBuilder();
        return $q->select()
            ->from('users')
            ->where('email = "'.$email.'"')
            ->limit(1)
            ->execute(get_class($this));
    }


    public function findActiveByEmail(string $email): self
    {
        $q = new QueryBuilder();
        return $q->select()
            ->from('users')
            ->where('email = "'.$email.'"', 'confirmed_at IS NOT NULL')
            ->limit(1)
            ->execute(get_class($this));
    }
}
