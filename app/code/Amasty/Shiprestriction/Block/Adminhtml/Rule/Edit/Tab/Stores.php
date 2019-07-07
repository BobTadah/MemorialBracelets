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
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\StoresGroups as CommonRulesStoresGroups;

class Stores extends CommonRulesStoresGroups
{

    public function _construct()
    {
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }
}
