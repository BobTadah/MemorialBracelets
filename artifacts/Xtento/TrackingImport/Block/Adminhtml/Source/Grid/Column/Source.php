<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-13T19:40:19+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Source/Grid/Column/Source.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Source\Grid\Column;

class Source extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Xtento\TrackingImport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * Source constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Xtento\TrackingImport\Model\ProfileFactory $profileFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileFactory = $profileFactory;
    }

    protected function getProfile()
    {
        return $this->profileFactory->create()->load(
            $this->getRequest()->getParam('id')
        );
    }

    public function getValues()
    {
        $array = [];
        foreach (explode("&", $this->getProfile()->getSourceIds()) as $key => $sourceId) {
            if ($sourceId === '') {
                continue;
            }
            $array[] = ['label' => $sourceId, 'value' => $sourceId];
        }
        return $array;
    }
}
