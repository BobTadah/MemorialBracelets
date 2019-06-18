<?php

namespace MemorialBracelets\CharmOption\Ui\DataProvider\CharmOption\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Locale\CurrencyInterface;

class PriceBefore extends AbstractModifier
{
    /** @var \Magento\Framework\Currency  */
    protected $currency;

    public function __construct(CurrencyInterface $localeCurrency)
    {
        $this->currency = $localeCurrency->getCurrency($localeCurrency->getDefaultCurrency());
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        foreach ($data as &$form) {
            $item = &$form['general'];
            $price = $this->currency->toCurrency($item['price'], ['symbol' => '']);
            $item['price'] = $price;
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta['general']['children']
        ['price']['arguments']
        ['data']['config']
        ['addbefore'] = $this->currency->getSymbol();

        return $meta;
    }
}
