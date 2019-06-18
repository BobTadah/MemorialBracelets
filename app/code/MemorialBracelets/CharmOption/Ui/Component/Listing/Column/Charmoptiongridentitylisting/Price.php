<?php

namespace MemorialBracelets\CharmOption\Ui\Component\Listing\Column\Charmoptiongridentitylisting;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Price extends Walkable
{
    /** @var CurrencyInterface  */
    protected $currency;

    /** @var string */
    protected $fieldName;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components,
        array $data
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->currency = $localeCurrency->getCurrency($localeCurrency->getDefaultCurrency());
        $this->fieldName = $this->getData('name');
    }


    public function processItem(array &$item)
    {
        $field = $this->fieldName;

        $item[$field] = $this->currency->toCurrency(sprintf('%f', $item[$field]));
    }
}
