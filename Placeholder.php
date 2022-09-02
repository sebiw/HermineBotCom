<?php

namespace HermineBotCom;

/**
 * Sends Actively placeholder information
 */
class Placeholder extends AbstractMessage {

    protected string $message = '';

    protected string $key = '';

    public function __construct( string $message , string $key , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->message = $message;
        $this->key = $key;
        parent::__construct( $context , $createdDateTime );
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

}