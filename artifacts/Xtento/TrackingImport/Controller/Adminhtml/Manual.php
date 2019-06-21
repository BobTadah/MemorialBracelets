<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Controller/Adminhtml/Manual.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Controller\Adminhtml;

abstract class Manual extends \Xtento\TrackingImport\Controller\Adminhtml\Action
{
    /**
     * @var \Xtento\TrackingImport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\TrackingImport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\TrackingImport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig);
        $this->profileFactory = $profileFactory;
    }

    /**
     * Check if user has enough privileges
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_TrackingImport::manual');
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_TrackingImport::manual');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Manual Import'), __('Manual Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Tracking Import - Manual Import'));
    }
}
