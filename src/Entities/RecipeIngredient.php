<?php

namespace App\Entities;

use App\Core\BaseEntity;
use App\Core\Database\QueryBuilder;

class RecipeIngredient extends BaseEntity
{
    private ?int $id = null;
    private ?int $idRecipe = null;
    private ?Ingredient $ingredient = null;
    private ?int $quantity = null;


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


    public function getIdRecipe(): ?int
    {
        return $this->idRecipe;
    }


    public function setIdRecipe(?string $idRecipe): self
    {
        $this->idRecipe = $idRecipe;
        return $this;
    }


    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }


    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;
        return $this;
    }


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }


    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }


    public function findBy(int $recipe, int $ingredient): self
    {
        $q = new QueryBuilder();
        return $q->select()
            ->from($this->entity)
            ->where('id_recipe = '.$recipe, 'id_ingredient = '.$ingredient)
            ->limit(1)
            ->execute(get_class($this));
    }
}
