<?php

namespace App\Entities;

use App\Core\BaseEntity;

class Ingredient extends BaseEntity
{
    const IMAGE_PATH = BASE_URL.'/uploads/ingredients/';


    private ?int $id = null;
    private ?string $label;
    private ?string $image;
    private ?Unit $unit = null;


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


    public function getUnit(): ?Unit
    {
        return $this->unit;
    }


    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;
        return $this;
    }
}
