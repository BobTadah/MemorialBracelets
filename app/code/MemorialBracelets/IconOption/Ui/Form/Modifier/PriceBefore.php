<?php

namespace MemorialBracelets\IconOption\Ui\Form\Modifier;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class PriceBefore implements ModifierInterface
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
