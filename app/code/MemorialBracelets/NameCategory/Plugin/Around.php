<?php

namespace MemorialBracelets\NameCategory\Plugin;

class Around
{
    public function aroundGetFilters($filterList, callable $proceed, \Magento\Catalog\Model\Layer $layer)
    {
        $result = $proceed($layer);
        // Go through the list and remove category from the filters
        foreach ($result as $key => $filter) {
            $name = $filter->getName();
            if (!is_string($name)) {
                $name = $name->getText(); //Category stores its name differently
            }
            if ($name == "Category") {
                unset($result[$key]);
            }
        }
        return $result;
    }
}
