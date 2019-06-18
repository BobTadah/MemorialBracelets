<?php

namespace MemorialBracelets\EngravingDisplay\Plugin;

class SavePrice
{
    public function aroundShouldRecordPrice($subject, callable $proceed, $type)
    {
        $result = $proceed($type);
        if (!$result && $type == 'engraving') {
            return true;
        }
        return $result;
    }
}
