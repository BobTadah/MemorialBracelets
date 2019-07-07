<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\OptionSource\Policy;

use Magento\Framework\Option\ArrayInterface;
use Amasty\Gdpr\Model\Policy;

class Status implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Policy::STATUS_DISABLED, 'label' => __('Disabled')],
            ['value' => Policy::STATUS_ENABLED, 'label' => __('Enabled')]
        ];
    }
}
