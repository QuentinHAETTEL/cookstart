<?php

namespace App\Core\Security;

use App\Core\BaseEntity;
use App\Core\Validator\Validator;
use App\Entities\User;
use DateTime;
use Exception;

class Token extends BaseEntity
{
    private ?int $id = null;
    private ?User $user = null;
    private ?string $type = null;
    private ?string $hash = null;
    private ?string $expireAt = null;


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


    public function getUser(): ?User
    {
        return $this->user;
    }


    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }


    public function getType(): ?string
    {
        return $this->type;
    }


    public function setType(?string $type): self
    {
        if (strlen($type) <= 255) {
            $this->type = $type;
        }

        return $this;
    }


    public function getHash(): ?string
    {
        return $this->hash;
    }


    public function setHash(?string $hash): self
    {
        if (strlen($hash) <= 255) {
            $this->hash = $hash;
        }

        return $this;
    }


    /**
     * @throws Exception
     */
    public function getExpireAt(): ?DateTime
    {
        return new DateTime($this->expireAt);
    }


    public function setExpireAt(?string $expireAt): self
    {
        $validator = new Validator(['expireAt' => $expireAt]);
        $validator->isDateTime('expireAt');
        if (strlen($expireAt) <= 255 && $validator->isValid()) {
            $this->expireAt = $expireAt;
        }

        return $this;
    }


    /**
     * @throws Exception
     */
    public function isExpired(): bool
    {
        return time() > $this->getExpireAt()->getTimestamp();
    }
}
