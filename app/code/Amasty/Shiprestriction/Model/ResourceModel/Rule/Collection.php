<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Model\ResourceModel\Rule;

class Collection extends \Amasty\CommonRules\Model\ResourceModel\Rule\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Shiprestriction\Model\Rule', 'Amasty\Shiprestriction\Model\ResourceModel\Rule');
    }
}
