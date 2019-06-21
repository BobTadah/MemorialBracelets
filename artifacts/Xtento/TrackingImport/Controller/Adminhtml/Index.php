<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-04-28T15:48:10+00:00
 * File:          app/code/Xtento/TrackingImport/Controller/Adminhtml/Index.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Controller\Adminhtml;

abstract class Index extends \Xtento\TrackingImport\Controller\Adminhtml\Action
{
    /**
     * Check if user has enough privileges
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_TrackingImport::profiles');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Tracking Import'), __('Tracking Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Tracking Import'));
    }
}
