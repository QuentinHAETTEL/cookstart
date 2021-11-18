<?php

namespace App\Entities;

use App\Core\BaseEntity;

class Unit extends BaseEntity
{
    private ?int $id = null;
    private ?string $code = null;
    private ?string $label = null;


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


    public function getCode(): ?string
    {
        return $this->code;
    }


    public function setCode(?string $code): self
    {
        if (strlen($code) <= 2) {
            $this->code = $code;
        }
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
}
