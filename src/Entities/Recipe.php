<?php

namespace App\Entities;

use App\Core\BaseEntity;
use App\Core\Data\DataTransformer;
use App\Core\Database\QueryBuilder;

class Recipe extends BaseEntity
{
    private ?int $id = null;
    private ?string $label = null;
    private ?string $description = null;
    private ?string $image = null;
    private ?array $instructionsList = [];
    private ?int $people = 4;
    private ?int $preparationTime = null;
    private ?int $cookingTime = null;
    private ?User $user = null;
    /**
     * @NotPersisted
     */
    private ?array $recipeIngredients = [];
    private ?Ingredient $ingredient;


    public function __construct()
    {
        parent::__construct();
        $this->ingredient = new Ingredient();
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


    public function getLabel(): ?string
    {
        return $this->label;
    }


    public function setLabel(?string $label): self
    {
        if (strlen($label) <= 255) {
            $this->label = $label;
        }
        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }


    public function getImage(): ?string
    {
        return $this->image;
    }


    public function setImage(?string $image): self
    {
        if (strlen($image) <= 255) {
            $this->image = $image;
        }
        return $this;
    }


    public function getInstructions(): array
    {
        return $this->instructionsList;
    }


    /**
     * @param array|string $instructionsList
     * @return Recipe
     */
    public function setInstructions($instructionsList): self
    {
        if ($instructionsList === null) {
            $instructionsList = [];
        }

        if (is_array($instructionsList)) {
            $this->instructionsList = $instructionsList;
        } else {
            $dataTransformer = new DataTransformer();
            $this->instructionsList = $dataTransformer->stringArrayToArray($instructionsList);
        }
        return $this;
    }


    public function addInstruction(?array $instructions): self
    {
        foreach ($instructions as $instruction) {
            if (!in_array($instruction, $this->instructionsList)) {
                $this->instructionsList[] = $instruction;
            }
        }

        return $this;
    }


    public function getPeople(): ?int
    {
        return $this->people;
    }


    public function setPeople(?int $people): self
    {
        $this->people = $people;
        return $this;
    }


    public function getPreparationTime(): ?int
    {
        return $this->preparationTime;
    }


    public function setPreparationTime(?string $preparationTime): self
    {
        if (preg_match('#^[0-9]+$#', $preparationTime)) {
            $this->preparationTime = $preparationTime;
        } else {
            $dataTransformer = new DataTransformer();
            $this->preparationTime = $dataTransformer->stringDurationToSeconds($preparationTime);
        }

        return $this;
    }


    public function getCookingTime(): ?int
    {
        return $this->cookingTime;
    }


    public function setCookingTime(?string $cookingTime): self
    {
        if (preg_match('#^[0-9]+$#', $cookingTime)) {
            $this->cookingTime = $cookingTime;
        } else {
            $dataTransformer = new DataTransformer();
            $this->cookingTime = $dataTransformer->stringDurationToSeconds($cookingTime);
        }

        return $this;
    }


    public function getRecipeIngredients(): array
    {
        return $this->recipeIngredients;
    }


    public function setRecipeIngredients(?array $recipeIngredients): self
    {
        $this->recipeIngredients = $recipeIngredients;
        return $this;
    }


    public function addRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if (!in_array($recipeIngredient, $this->recipeIngredients)) {
            $this->recipeIngredients[] = $recipeIngredient;
        }

        return $this;
    }


    public function removeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if ($key = in_array($recipeIngredient, $this->recipeIngredients, true)) {
            unset($this->recipeIngredients[$key]);
        }

        return $this;
    }


    public function getUser(): ?User
    {
        return $this->user;
    }


    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }


    public function getAllByUser(User $user): array
    {
        $q = new QueryBuilder();
        return $q->select()
            ->from($this->entity)
            ->where('id_user = "'.$user->getId().'"')
            ->execute(get_class($this));
    }


    public function find(int $id): self
    {
        $recipe = parent::find($id);

        $q = new QueryBuilder();
        $ingredients = $q->select('ingredients.id', 'label', 'id_unit', 'created_at', 'quantity')
            ->from('ingredients')
            ->join('recipes_ingredients', ['id', 'id_ingredient'])
            ->where('recipes_ingredients.id_recipe = "'.$id.'"')
            ->execute();

        foreach ($ingredients as $ingredient) {
            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setIdRecipe($recipe->getId());
            $recipeIngredient->setIngredient($this->ingredient->find($ingredient->id));
            $recipeIngredient->setQuantity($ingredient->quantity);
            $recipe->addRecipeIngredient($recipeIngredient);
        }

        return $recipe;
    }


    public function getIngredients(int $id): array
    {
        $q = new QueryBuilder();
        return $q->select('ingredients.id', 'ingredients.label', 'ingredients.image', 'id_unit')
            ->from('ingredients')
            ->join('recipes_ingredients', ['id', 'id_ingredient'])
            ->join('recipes', ['id_recipe', 'id'])
            ->where('recipes_ingredients.id_recipe = "'.$id.'"')
            ->execute(get_class(new Ingredient()));
    }
}
