<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\OptionProvider\Provider;

class BackorderOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const ALL_ORDERS = 0;
    const BACKORDERS_ONLY = 1;
    const NON_BACKORDERS = 2;

    /**
     * @var array|null
     */
    protected $options;

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                self::ALL_ORDERS => __('All orders'),
                self::BACKORDERS_ONLY => __('Backorders only'),
                self::NON_BACKORDERS => __('Non backorders')
            ];
        }

        return $this->options;
    }
}
