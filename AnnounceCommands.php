<?php

namespace HermineBotCom;

class AnnounceCommands extends AbstractMessage {

    protected array $commands = [];

    /**
     * @param array $commands
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( array $commands , string $context , ?\DateTime $createdDateTime = null )
    {
        $this->commands = $commands;
        parent::__construct( $context , $createdDateTime );
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

}