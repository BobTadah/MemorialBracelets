<?php

namespace MemorialBracelets\CharmOption\Api;

interface CharmOptionInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const PRICETYPE_FIXED = 'fixed';
    const PRICETYPE_PERCENT = 'percent';

    public function getId();
    public function getTitle();
    public function getPosition();
    public function getPrice();
    public function getPriceType();
    public function getIsActive();
}
