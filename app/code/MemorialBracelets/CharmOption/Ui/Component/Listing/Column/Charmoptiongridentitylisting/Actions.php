<?php

namespace MemorialBracelets\CharmOption\Ui\Component\Listing\Column\Charmoptiongridentitylisting;

class Actions extends Walkable
{
    public function processItem(array &$item)
    {
        $name = $this->getData("name");
        $id = "X";
        if (isset($item["id"])) {
            $id = $item["id"];
        }
        $item[$name]["edit"] = [
            "href"  => $this->getContext()->getUrl("charmoption/index/edit", ["id" => $id]),
            "label" => __("Edit"),
        ];
        $item[$name]["delete"] = [
            "href" => $this->getContext()->getUrl("charmoption/index/delete", ["id" => $id]),
            'label' => __('Delete'),
            'confirm' => [
                'title' => __('Delete option'),
                'message' => __('Are you sure you want to delete this option?'),
            ]
        ];
    }
}
