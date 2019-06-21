<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-13T20:01:05+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Source/Grid.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Source;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Xtento\TrackingImport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Xtento\TrackingImport\Model\ProfileFactory $profileFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->profileFactory = $profileFactory;
    }

    protected function getProfile()
    {
        return $this->profileFactory->create()->load($this->getRequest()->getParam('id'));
    }

    public function getSelectedSources()
    {
        $array = explode("&", $this->getProfile()->getSourceIds());
        return $array;
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __(
                    'Import sources control where files are retrieved (downloaded) from. Set up local directory, FTP, SFTP, etc. sources and enable them in the import profiles "Import Sources" tab.'
                )
            ]
        ];
        return $formMessages;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('ajax')) {
            return parent::_toHtml();
        }
        return $this->_getFormMessages() . parent::_toHtml();
    }

    protected function _getFormMessages()
    {
        $html = '<div id="messages"><div class="messages">';
        foreach ($this->getFormMessages() as $formMessage) {
            $html .= '<div class="message message-' . $formMessage['type'] . ' ' . $formMessage['type'] . '"><div>' . $formMessage['message'] . '</div></div>';
        }
        $html .= '</div></div>';
        return $html;
    }
}