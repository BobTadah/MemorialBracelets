<?php

namespace MemorialBracelets\ExtensibleCustomOption\Api;

/**
 * Interface ProductSpecificPriceConfigurationInterface
 * @package MemorialBracelets\ExtensibleCustomOption\Api
 * @api
 */
interface ProductSpecificPriceConfigurationInterface
{
    public function getPriceBasedOnProduct($product);
}
