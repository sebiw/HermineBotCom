<?php

namespace HermineBotCom;

abstract class AbstractMessage {

    /**
     * @var string
     */
    protected string $context = '';

    /**
     * @var \DateTime
     */
    protected \DateTime $createdDateTime;

    /**
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( string $context , ?\DateTime $createdDateTime = null )
    {
        $this->createdDateTime = $createdDateTime ?? new \DateTime();
        $this->context = $context;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDateTime(): \DateTime
    {
        return $this->createdDateTime;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

}