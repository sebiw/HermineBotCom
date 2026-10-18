<?php

namespace HermineBotCom;

/**
 * Sends Actively placeholder information
 */
class Placeholder extends AbstractMessage {

    /**
     * @param string $message
     * @param string $key
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( protected readonly string $message , protected readonly string $key , string $context , ?\DateTime $createdDateTime = null )
    {
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