<?php

namespace MemorialBracelets\CheckHandling\Model;

use Magento\Email\Model\Source\Variables;
use Magento\TestFramework\Event\Magento;

class VariablePlugin
{
    private function getData()
    {
        return [
            ['value' => 'payment/checkmo/payable_to', 'label' => __('Make Check Payable to')],
            ['value' => 'payment/checkmo/mailing_address', 'label' => __('Send Check to')],
        ];
    }

    /**
     * @param Variables $subject
     * @param callable  $proceed
     * @param bool      $withGroup
     * @return array
     */
    public function aroundToOptionArray(Variables $subject, callable $proceed, $withGroup = false)
    {
        $result = $proceed($withGroup);

        $optionArray = array_map(function ($item) {
            return [
                'value' => '{{config path="'.$item['value'].'"}}',
                'label' => $item['label'],
            ];
        }, $this->getData());

        if ($withGroup && $result) {
            $result['value'] = array_merge($result['value'], $optionArray);
        } else {
            $result = array_merge($result, $optionArray);
        }
        return $result;
    }

    /**
     * @param Variables $subject
     * @param string[]  $result
     * @return array
     */
    public function afterGetData(Variables $subject, $result)
    {
        return array_merge($result, $this->getData());
    }
}
