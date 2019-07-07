<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\Rule\Condition\Product;

use Amasty\CommonRules\Model\Rule\Condition\ConditionBuilder as Conditions;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{
    /**
     * @var Conditions
     */
    private $conditionBuilder;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Amasty\CommonRules\Model\Rule\Condition\Product $ruleConditionProduct
     * @param Conditions $conditionBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\CommonRules\Model\Rule\Condition\Product $ruleConditionProduct,
        \Amasty\CommonRules\Model\Rule\Condition\ConditionBuilder $conditionBuilder,
        array $data = []
    ) {
        $this->conditionBuilder = $conditionBuilder;
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(Conditions::AMASTY_COMMON_RULES_PATH_TO_CONDITIONS . 'Product\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();

        return $this->conditionBuilder->getChangedNewChildSelectOptions($conditions);
    }
}
