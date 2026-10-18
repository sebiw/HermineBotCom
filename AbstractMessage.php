<?php

namespace HermineBotCom;

abstract class AbstractMessage {

    /**
     * @var \DateTime
     */
    protected readonly \DateTime $createdDateTime;

    /**
     * @param string $context
     * @param \DateTime|null $createdDateTime
     */
    public function __construct( protected readonly string $context , ?\DateTime $createdDateTime = null )
    {
        $this->createdDateTime = $createdDateTime !== null ? clone $createdDateTime : new \DateTime();
    }

    /**
     * Returns a copy, so callers cannot modify the stored timestamp
     *
     * @return \DateTime
     */
    public function getCreatedDateTime(): \DateTime
    {
        return clone $this->createdDateTime;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

}