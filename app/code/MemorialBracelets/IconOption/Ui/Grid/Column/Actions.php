<?php

namespace MemorialBracelets\IconOption\Ui\Grid\Column;

use MemorialBracelets\IconOption\Ui\Component\Column\Walkable;

class Actions extends Walkable
{
    public function processItem(array &$item)
    {
        $name = $this->getData("name");
        $id = "X";
        if (isset($item["entity_id"])) {
            $id = $item["entity_id"];
        }
        $item[$name]["edit"] = [
            "href"  => $this->getContext()->getUrl("iconoption/index/edit", ["id" => $id]),
            "label" => __("Edit"),
        ];
        $item[$name]["delete"] = [
            "href" => $this->getContext()->getUrl("iconoption/index/delete", ["id" => $id]),
            'label' => __('Delete'),
            'confirm' => [
                'title' => __('Delete icon'),
                'message' => __('Are you sure you want to delete this icon?'),
            ]
        ];
    }
}
