<?php

namespace MemorialBracelets\CharmOption\Model;

use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use MemorialBracelets\CharmOption\Api\CharmOptionInterface;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;

class OptionType extends DefaultType
{
    protected $optionValues;

    protected $charmOptionRepository;

    protected $searchFactory;
    protected $_formattedOptionValue;
    protected $escaper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SearchCriteriaInterfaceFactory $searchFactory,
        \Magento\Framework\Escaper $escaper,
        CharmOptionRepositoryInterface $charmOptionRepository,
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->charmOptionRepository = $charmOptionRepository;
        $this->searchFactory = $searchFactory;
        parent::__construct($checkoutSession, $scopeConfig, $data);
    }

    protected function getOptionValues()
    {
        if (!isset($this->optionValues)) {
            $list = $this->charmOptionRepository->getList($this->searchFactory->create())->getItems();
            $this->optionValues = [];
            /** @var CharmOption $charm */
            foreach ($list as $charm) {
                $this->optionValues[$charm->getId()] = $charm;
            }
        }
        return $this->optionValues;
    }

    protected function getValueById($optionValue)
    {
        $values = $this->getOptionValues();
        return isset($values[$optionValue]) ? $values[$optionValue] : null;
    }


    /** {@inheritdoc} */
    public function validateUserValue($values)
    {
        parent::validateUserValue($values);

        $option = $this->getOption();
        $value = $this->getUserValue();

        if (empty($value) && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            throw new LocalizedException(__('Please specify product\'s required option(s).'));
        }

        return $this;
    }

    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            $this->_formattedOptionValue = $this->escaper->escapeHtml($this->getEditableOptionValue($optionValue));
        }
        return $this->_formattedOptionValue;
    }

    public function getEditableOptionValue($optionValue)
    {
        $result = '';

        $_result = $this->getValueById($optionValue);
        if ($_result) {
            $result = $_result->getTitle();
        } elseif ($this->getListener()) {
            $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
        }
        return $result;
    }

    public function getOptionPrice($optionValue, $basePrice)
    {
        $result = 0;

        $_result = $this->getValueById($optionValue);
        if ($_result) {
            $isPercent = $_result->getPriceType() == CharmOptionInterface::PRICETYPE_PERCENT;
            $result = $this->_getChargableOptionPrice($_result->getPrice(), $isPercent, $basePrice);
        } elseif ($this->getListener()) {
            $this->getListener()->setHasError(true)->setMessage($this->_getWrongConfigurationMessage());
        }

        return $result;
    }

    public function parseOptionValue($optionValue, $productOptionValues)
    {
        $test = true;
        return null;
    }

    /**
     * Return currently unavailable product configuration message
     *
     * @see \Magento\Catalog\Model\Product\Option\Type\Select::_getWrongConfigurationMessage()
     * @return \Magento\Framework\Phrase
     */
    protected function _getWrongConfigurationMessage()
    {
        return __('Some of the selected item options are not currently available.');
    }
}
