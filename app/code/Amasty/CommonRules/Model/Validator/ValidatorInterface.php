<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\Validator;

/**
 * Interface ModifierInterface
 */
interface ValidatorInterface
{
    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     * @param $items
     * @return boolean
     */
    public function validate($rule, $items);
}
