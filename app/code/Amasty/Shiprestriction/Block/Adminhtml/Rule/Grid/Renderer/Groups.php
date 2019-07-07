<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Grid\Renderer;

class Groups extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var \Amasty\CommonRules\Model\OptionProvider\Pool
     */
    protected $poolOptionProvider;

    /**
     * Carriers constructor.
     *
     * @param \Magento\Backend\Block\Context      $context
     * @param \Amasty\Shiprestriction\Helper\Data $helper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider,
        array $data = []
    ){
        $this->poolOptionProvider = $poolOptionProvider;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $groups = $row->getData('cust_groups');
        if (!$groups) {
            return __('Restricts For All');
        }
        $groups = explode(',', $groups);

        $html = '';
        $allGroups = $this->poolOptionProvider->getOptionsByProviderCode('customer_group');

        foreach($allGroups as $row) {
            if (in_array($row['value'], $groups)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }

}
