<?php

namespace HermineBotCom;

/**
 *
 */
class Message extends AbstractMessage {

    /**
     * @var string
     */
    protected string $message = '';

    /**
     * @var string
     */
    protected string $channel = '';

    /**
     * @param string $message
     * @param string $channel
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( string $message , string $channel , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->message = $message;
        $this->channel = $channel;
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
    public function getChannel(): string
    {
        return $this->channel;
    }

}