<?php

namespace HermineBotCom;

class RequestContent extends AbstractMessage {

    protected string $command = '';

    /**
     * @param string $command
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( string $command , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->command = $command;
        parent::__construct( $context , $createdDateTime );
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

}