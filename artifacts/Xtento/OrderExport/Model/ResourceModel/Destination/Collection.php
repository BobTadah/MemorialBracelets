<?php

/**
 * Product:       Xtento_OrderExport (2.2.5)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2015-11-26T12:57:04+00:00
 * File:          app/code/Xtento/OrderExport/Model/ResourceModel/Destination/Collection.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\ResourceModel\Destination;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Xtento\OrderExport\Model\Destination', 'Xtento\OrderExport\Model\ResourceModel\Destination');
    }
}