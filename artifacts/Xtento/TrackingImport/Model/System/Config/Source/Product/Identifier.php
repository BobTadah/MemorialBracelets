<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-07T14:03:00+00:00
 * File:          app/code/Xtento/TrackingImport/Model/System/Config/Source/Product/Identifier.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\System\Config\Source\Product;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Identifier implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $identifiers[] = ['value' => 'sku', 'label' => __('SKU')];
        $identifiers[] = ['value' => 'entity_id', 'label' => __('Product ID (entity_id)')];
        $identifiers[] = ['value' => 'attribute', 'label' => __('Custom Product Attribute')];
        return $identifiers;
    }
}
