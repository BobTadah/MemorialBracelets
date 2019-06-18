<?php

namespace MemorialBracelets\SupportiveMessages\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageInterface;

class SupportiveMessage extends AbstractModel implements SupportiveMessageInterface, IdentityInterface
{
    const CACHE_TAG = 'memorialbracelets_supportivemessages_supportivemessage';

    protected function _construct()
    {
        $this->_init(ResourceModel\SupportiveMessage::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled') ];
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->getData('position');
    }

    /**
     * One of the STATUS_ constants on {@see SupportiveMessageInterface}
     *
     * @see SupportiveMessageInterface::STATUS_ENABLED
     * @see SupportiveMessageInterface::STATUS_ENABLED
     * @return int
     */
    public function getStatus()
    {
        return $this->getData('is_active');
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData('message');
    }
}
