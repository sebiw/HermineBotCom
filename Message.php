<?php

namespace HermineBotCom;

/**
 *
 */
class Message extends AbstractMessage {

    /**
     * @param string $message
     * @param string $channel
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( protected readonly string $message , protected readonly string $channel , string $context , ?\DateTime $createdDateTime = null )
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
    public function getChannel(): string
    {
        return $this->channel;
    }

}