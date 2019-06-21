<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-13T19:40:23+00:00
 * File:          app/code/Xtento/TrackingImport/Model/System/Config/Source/Source/Type.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\System\Config\Source\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Type implements ArrayInterface
{
    /**
     * @var \Xtento\TrackingImport\Model\Source
     */
    protected $sourceModel;

    /**
     * @param \Xtento\TrackingImport\Model\Source $sourceModel
     */
    public function __construct(\Xtento\TrackingImport\Model\Source $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->sourceModel->getTypes();
    }

    public function getName($type)
    {
        foreach ($this->toOptionArray() as $optionType => $name) {
            if ($optionType == $type) {
                return $name;
            }
        }
        return '';
    }
}
