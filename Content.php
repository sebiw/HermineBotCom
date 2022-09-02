<?php

namespace HermineBotCom;

class Content extends AbstractMessage {

    /**
     * @var string
     */
    protected string $message = '';

    /**
     * @var string
     */
    protected string $key = '';

    /**
     * @param string $message
     * @param string $key
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
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