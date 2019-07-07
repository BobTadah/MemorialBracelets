<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Model\ResourceModel;

class Rule extends \Amasty\CommonRules\Model\ResourceModel\AbstractRule
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_shiprestriction_rule', 'rule_id');
    }
}
