<?php

/**
 * Product:       Xtento_TrackingImport (2.1.6)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-04-28T21:00:32+00:00
 * File:          app/code/Xtento/TrackingImport/Model/System/Config/Source/Import/Profile.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\System\Config\Source\Import;

class Profile
{
    /**
     * @var \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @param \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     */
    public function __construct(
        \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    public function toOptionArray($all = false, $entity = false)
    {
        $profileCollection = $this->profileCollectionFactory->create();
        if (!$all) {
            $profileCollection->addFieldToFilter('enabled', 1);
        }
        if ($entity) {
            $profileCollection->addFieldToFilter('entity', $entity);
        }
        $profileCollection->getSelect()->order('entity ASC');
        $returnArray = [];
        foreach ($profileCollection as $profile) {
            $returnArray[] = [
                'profile' => $profile,
                'value' => $profile->getId(),
                'label' => $profile->getName(),
                'entity' => $profile->getEntity(),
            ];
        }
        if (empty($returnArray)) {
            $returnArray[] = [
                'profile' => new \Magento\Framework\DataObject(),
                'value' => '',
                'label' => __(
                    'No profiles available. Add and enable import profiles for the %1 entity first.',
                    $entity
                ),
                'entity' => '',
            ];
        }
        return $returnArray;
    }
}
