<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Edit\Tab;

use Amasty\Shiprestriction\Model\RegistryConstants;
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\General as CommonRulesGeneral;

class General extends CommonRulesGeneral
{

    public function _construct()
    {
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Shipping Methods');
    }

    /**
     * @inheritdoc
     */
    protected function formInit($model)
    {
        $form = parent::formInit($model);

        $fieldset = $form->getElement('apply_in');
        $fieldset->addField(
            'methods',
            'textarea',
            [
                'label' => __('Restrict Shipping Methods'),
                'title' => __('Restrict Shipping Methods'),
                'name' => 'methods',
                'note' => __('One method name per line, e.g Next Day Air'),
            ]
        );

        $fieldset->addField(
            'carriers',
            'multiselect',
            [
                'label' => __('Restrict ALL METHODS from Carriers'),
                'title' => __('Restrict ALL METHODS from Carriers'),
                'name' => 'carriers[]',
                'values' => $this->poolOptionProvider->getOptionsByProviderCode('carriers'),
                'note' => __('Select if you want to restrict ALL methods from the given carrirers'),
            ]
        );

        $fieldset->addField(
            'message',
            'text',
            [
                'label' => __('Error Message'),
                'title' => __('Error Message'),
                'name' => 'message',
            ]
        );

        return $form;
    }
}
