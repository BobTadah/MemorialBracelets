<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-13T19:40:23+00:00
 * File:          app/code/Xtento/TrackingImport/Model/System/Config/Source/Import/Entity.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\System\Config\Source\Import;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Entity implements ArrayInterface
{
    /**
     * @var \Xtento\TrackingImport\Model\Import
     */
    protected $importModel;

    /**
     * Entity constructor.
     *
     * @param \Xtento\TrackingImport\Model\Import $importModel
     */
    public function __construct(\Xtento\TrackingImport\Model\Import $importModel)
    {
        $this->importModel = $importModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->importModel->getEntities();
    }
}
