<?php

namespace App\Core\Mail;

use App\Core\BaseEntity;
use App\Core\App;
use App\Entities\User;

class Mail extends BaseEntity
{
    private ?array $recipients = [];
    private ?array $cc = [];
    private ?array $bcc = [];
    private ?User $user = null;
    private ?string $subject = null;
    private ?string $content = null;
    private ?string $from;
    private ?string $replyTo;
    private ?bool $send = false;
    private ?string $error = null;


    public function __construct()
    {
        parent::__construct();
        $config = App::getInstance()->getConfig();

        $this->from = $config->get('mail_from');
        $this->replyTo = $config->get('mail_to');
    }


    public function getRecipients(): ?array
    {
        return $this->recipients;
    }


    public function setRecipients(?array $recipients): self
    {
        $this->recipients = $recipients;
        return $this;
    }


    public function addRecipients(?array $recipients): self
    {
        foreach ($recipients as $recipient) {
            if (!in_array($recipient, $this->recipients)) {
                $this->recipients[] = $recipient;
            }
        }

        return $this;
    }


    public function getCc(): ?array
    {
        return $this->cc;
    }


    public function setCc(?array $cc): self
    {
        $this->cc = $cc;
        return $this;
    }


    public function addCc(?array $emails): self
    {
        foreach ($emails as $email) {
            if (!in_array($email, $this->cc)) {
                $this->cc[] = $email;
            }
        }

        return $this;
    }


    public function getBcc(): ?array
    {
        return $this->bcc;
    }


    public function setBcc(?array $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }


    public function addBcc(?array $emails): self
    {
        foreach ($emails as $email) {
            if (!in_array($email, $this->bcc)) {
                $this->bcc[] = $email;
            }
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


    public function getSubject(): ?string
    {
        return $this->subject;
    }


    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }


    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }


    public function getFrom(): ?string
    {
        return $this->from;
    }


    public function setFrom(?string $from): self
    {
        $this->from = $from;
        return $this;
    }


    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }


    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }


    public function getSend(): ?bool
    {
        return $this->send;
    }


    public function setSend(?bool $send): self
    {
        $this->send = $send;
        return $this;
    }


    public function getError(): ?string
    {
        return $this->error;
    }


    public function setError(?string $error): self
    {
        $this->error = $error;
        return $this;
    }
}
