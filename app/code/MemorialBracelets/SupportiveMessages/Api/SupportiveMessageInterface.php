<?php

namespace MemorialBracelets\SupportiveMessages\Api;

interface SupportiveMessageInterface
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int
     */
    public function getPosition();

    /**
     * One of the STATUS_ constants on {@see SupportiveMessageInterface}
     *
     * @see SupportiveMessageInterface::STATUS_ENABLED
     * @see SupportiveMessageInterface::STATUS_ENABLED
     * @return int
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getMessage();
}
