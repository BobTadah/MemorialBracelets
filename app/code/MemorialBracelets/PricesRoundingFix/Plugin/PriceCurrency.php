<?php

namespace MemorialBracelets\PricesRoundingFix\Plugin;

use Magento\Directory\Model\PriceCurrency as MagentoPriceCurrency;
use MemorialBracelets\PricesRoundingFix\Model\Payment\Method\AuthorizeCim;

class PriceCurrency
{
    /**
     * @var AuthorizeCim
     */
    private $authorizeCim;

    /**
     * PriceCurrency constructor.
     * @param AuthorizeCim $authorizeCim
     */
    public function __construct(
        AuthorizeCim $authorizeCim
    ) {
        $this->authorizeCim = $authorizeCim;
    }

    /**
     *
     * This plugin is intended to workaround an issue with IWD OrderManager that causes admin order edits to
     * show different price values between what Magento calculated and what IWD is calculating after an order edit.
     *
     * Round price
     *
     * @param MagentoPriceCurrency $subject
     * @param callable $proceed
     * @param float $price
     * @return float
     */
    public function aroundRound(MagentoPriceCurrency $subject, callable $proceed, $price)
    {
        $price = $this->authorizeCim->fixRounding($price);

        return round($price, 2);
    }
}
