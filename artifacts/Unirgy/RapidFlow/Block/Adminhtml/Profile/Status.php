<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template;

/**
 * Class Status
 *
 * @method \Unirgy\RapidFlow\Model\Profile getProfile()
 * @package Unirgy\RapidFlow\Block\Adminhtml\Profile
 */
class Status extends Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Unirgy_RapidFlow::urapidflow/status.phtml');
    }
}
