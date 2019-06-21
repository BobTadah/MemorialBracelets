<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-10-20T14:10:29+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Address/Billing.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Condition\Address;

use Xtento\TrackingImport\Model\Import\Condition\ObjectCondition;

class Billing extends ObjectCondition
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode' => __('Billing Postcode'),
            'region' => __('Billing Region'),
            'region_id' => __('Billing State/Province'),
            'country_id' => __('Billing Country'),
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        if (!$address instanceof \Magento\Sales\Model\Order\Address) {
            $address = $object->getBillingAddress();
        }

        return $this->validateAttribute($address->getData($this->getAttribute()));
    }
}
