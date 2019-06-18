<?php

namespace MemorialBracelets\IconOption\Ui\External;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;

class ProductFormModifier extends AbstractModifier
{

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        foreach ($data as $key => $datum) {
            if (!isset($datum['product']) || !isset($datum['product']['options'])) {
                continue;
            }

            $options = $data[$key]['product']['options'];
            foreach ($options as $optionKey => $option) {
                if ($option['type'] == 'iconpicker') {
                    unset($data[$key]['product']['options'][$optionKey]['values']);
                }
            }
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (!isset($meta[CustomOptions::GROUP_CUSTOM_OPTIONS_NAME])) {
            return $meta;
        }
        $meta
        [CustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children']
        [CustomOptions::GRID_OPTIONS_NAME]['children']
        ['record']['children']
        [CustomOptions::CONTAINER_OPTION]['children']
        [CustomOptions::CONTAINER_COMMON_NAME]['children']
        [CustomOptions::FIELD_TYPE_NAME]['arguments']['data']['config']['groupsConfig']
        ['icon'] = [
            'values' => ['iconpicker'],
            'indexes' => [
                CustomOptions::CONTAINER_TYPE_STATIC_NAME,
            ]
        ];

        return $meta;
    }
}
