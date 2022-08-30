<?php

namespace HermineBotCom;

class Message {

    protected string $message = '';

    protected string $channel = '';

    protected string $context = '';

    protected \DateTime $createdDateTime;

    public function __construct( string $message , string $channel , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->createdDateTime = $createdDateTime ?? new \DateTime();
        $this->message = $message;
        $this->channel = $channel;
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
    public function getChannel(): string
    {
        return $this->channel;
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