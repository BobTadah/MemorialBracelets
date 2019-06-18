<?php

namespace MemorialBracelets\SupportiveMessages\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $name = $this->getData('name');
            $id = isset($item['entity_id']) ? $item['entity_id'] : 'X';
            $item[$name]['edit'] = [
                'href' => $this->getContext()->getUrl('supportivemessages/index/edit', ['id' => $id]),
                'label' => __('Edit'),
            ];
            $item[$name]['delete'] = [
                'href' => $this->getContext()->getUrl('supportivemessages/index/delete', ['id' => $id]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete Message'),
                    'message' => __('Are you sure you want to delete this message?'),
                ],
            ];
        }

        return $dataSource;
    }
}
