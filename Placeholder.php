<?php

namespace HermineBotCom;

class Placeholder {

    protected string $message = '';

    protected string $key = '';

    protected string $context = '';

    protected \DateTime $createdDateTime;

    public function __construct( string $message , string $key , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->createdDateTime = $createdDateTime ?? new \DateTime();
        $this->message = $message;
        $this->key = $key;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDateTime(): \DateTime
    {
        return $this->createdDateTime;
    }

}