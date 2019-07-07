<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\OptionProvider\Provider;

class RulesOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    protected $options;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * RulesOptionProvider constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $rules =  [
                [
                    'value'=>'0', 'label' => ' '
                ]
            ];

            $rulesCollection = $this->ruleCollectionFactory->create();
            foreach ($rulesCollection as $rule) {
                $rules[] = ['value' => $rule->getRuleId(), 'label' => $rule->getName()];
            }

            $this->options = $rules;
        }

        return $this->options;
    }
}
