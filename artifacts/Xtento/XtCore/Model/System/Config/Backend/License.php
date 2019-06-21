<?php

/**
 * Product:       Xtento_XtCore (2.0.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2015-07-07T10:52:07+00:00
 * File:          app/code/Xtento/XtCore/Model/System/Config/Backend/License.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Backend;

class License extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $this->_registry->register('xtento_configuration_license_key', $this->getValue(), true);
    }
}
