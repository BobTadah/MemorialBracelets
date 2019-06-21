<?php

/**
 * Product:       Xtento_OrderExport (2.2.5)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2016-03-02T18:14:21+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Data/Order/Payment.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Order;

class Payment extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Payment information',
            'category' => 'Order Payment',
            'description' => 'Export payment information from the sales_flat_order_payment table.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO, \Xtento\OrderExport\Model\Export::ENTITY_QUOTE],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['payment']; // Write on payment level
        // Fetch fields to export
        $order = $collectionItem->getOrder();
        $payment = $order->getPayment();

        if (!$this->fieldLoadingRequired('payment')) {
            return $returnArray;
        }

        // General Payment Data
        if ($payment) {
            foreach ($payment->getData() as $key => $value) {
                if ($key == 'additional_information') continue;
                $this->writeValue($key, $value);
            }

            try {
                if ($this->fieldLoadingRequired('method_title')) {
                    if ($payment->getMethodInstance()) {
                        $this->writeValue('method_title', $payment->getMethodInstance()->getTitle());
                    }
                }
            } catch (\Exception $e) {
                // Could not get payment method instance - probably payment module was removed.
            }

            // Additional data - serialized array
            $additionalData = $payment->getAdditionalData();
            if (!empty($additionalData) && $this->fieldLoadingRequired('additional_fields')) {
                $additionalData = @unserialize($additionalData);
                if ($additionalData && is_array($additionalData)) {
                    $this->writeArray = & $returnArray['payment']['additional_fields'];
                    foreach ($additionalData as $key => $value) {
                        if (!is_array($value)) {
                            $this->writeValue($key, $value);
                        }
                    }
                    if (isset($additionalData['transactions']) && is_array($additionalData['transactions'])) {
                        $this->writeArray = & $returnArray['payment']['additional_fields']['transaction'];
                        foreach ($additionalData['transactions'] as $transaction) {
                            // M2e fields
                            foreach ($transaction as $tKey => $tValue) {
                                $this->writeValue($tKey, $tValue);
                            }
                        }
                    }
                }
            }

            // Additional information - serialized array
            $additionalInformation = $payment->getAdditionalInformation();
            if (is_array($additionalInformation) && $this->fieldLoadingRequired('additional_fields')) {
                $this->writeArray = & $returnArray['payment']['additional_fields'];
                foreach ($additionalInformation as $key => $value) {
                    $this->writeValue($key, $value);
                }
                if (isset($additionalInformation['transactions']) && is_array($additionalInformation['transactions'])) {
                    $this->writeArray = & $returnArray['payment']['additional_fields']['transaction'];
                    foreach ($additionalInformation['transactions'] as $transaction) {
                        // M2e fields
                        foreach ($transaction as $tKey => $tValue) {
                            $this->writeValue($tKey, $tValue);
                        }
                    }
                }
            }

            // Authorize.net authorize_cards
            if ($this->fieldLoadingRequired('authorize_cards')) {
                $additionalData = $payment->getAdditionalData();
                $additionalData = @unserialize($additionalData);
                if ($additionalData && is_array($additionalData)) {
                    if (isset($additionalData['authorize_cards'])) {
                        $this->writeArray = & $returnArray['payment']['authorize_cards'];
                        foreach ($additionalData['authorize_cards'] as $cardInfo) {
                            if (!is_array($cardInfo)) continue;
                            foreach ($cardInfo as $key => $value) {
                                $this->writeValue($key, $value);
                            }
                            break;
                        }
                    }
                }
                $additionalData = $payment->getAdditionalInformation('authorize_cards');
                if ($additionalData && is_array($additionalData)) {
                    $this->writeArray = & $returnArray['payment']['authorize_cards'];
                    foreach ($additionalData as $cardInfo) {
                        if (!is_array($cardInfo)) continue;
                        foreach ($cardInfo as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                        break;
                    }
                }
            }
        }
        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}