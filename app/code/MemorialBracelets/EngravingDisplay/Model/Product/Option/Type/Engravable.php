<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MemorialBracelets\EngravingDisplay\Model\Product\Option\Type;

use Magento\Framework\Exception\LocalizedException;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Catalog product option text type
 */
class Engravable extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{
    const ENGRAVING_TYPE_CUSTOM = 'custom';
    const ENGRAVING_TYPE_SUPPORTIVE = 'supportive';
    const ENGRAVING_TYPE_NAME = 'name';

    /**
     * @var string|array
     */
    protected $_formattedOptionValue;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    /** @var $filterGroup FilterBuilder */
    protected $filterBuilder;

    /** @var $searchCriteriaInterface SearchCriteriaInterface */
    protected $searchCriteriaInterface;

    /** @var $filterGroup FilterGroup */
    protected $filterGroup;

    /** @var SupportiveMessageRepositoryInterface */
    protected $repository;
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\StringUtils $string,
        SupportiveMessageRepositoryInterface $repository,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->_escaper = $escaper;
        $this->string = $string;
        $this->repository = $repository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        parent::__construct($checkoutSession, $scopeConfig, $data);
        $this->appState = $appState;
    }

    public function validEngravingTypes()
    {
        return [
            static::ENGRAVING_TYPE_CUSTOM,
            static::ENGRAVING_TYPE_SUPPORTIVE,
            static::ENGRAVING_TYPE_NAME
        ];
    }

    /**
     * Validate user input for option
     *
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateUserValue($values)
    {
        $this->_checkoutSession->setUseNotice(false);
        $this->setIsValid(false);

        $option = $this->getOption();
        $serializedValue = isset($values[$option->getId()]) ? $values[$option->getId()] : '';
        $decodedValue = json_decode($serializedValue, true);
        $decodedValue = is_null($decodedValue) ? '' : $decodedValue;

        if (empty($decodedValue)) {
            return $this;
        }

        $engravingType = $decodedValue['type'];
        $engravingLines = [];
        $userInputtedEngravingLines = [];
        if (!empty($decodedValue) && isset($decodedValue['text'])) {
            $engravingLines             = explode("\r\n", $decodedValue['text']);
            //Make a copy to return if validations pass.
            $userInputtedEngravingLines = explode("\r\n", $decodedValue['text']);
            //For validation purpose, we uppercase the lines.
            $engravingLines = array_map('strtoupper', $engravingLines);
        }

        // Now that we have all the data, let's validate it

        // determine if lines are empty, and that they meet the size req
        $linesEmpty = true;
        $maxLength = $option->getMaxCharacters();

        // Default engraving lines. Used to compare against what has been posted.
        $defaultEngraving = explode("\r\n", $this->getRequest()->getNameString());

        // First we fix possible capitalization issues.
        $defaultEngraving = array_map('strtoupper', $defaultEngraving);

        // Array intersect will return an array with the matched values in both arrays.
        // We just need to know if the default engraving still exist in the posted engraving.
        $intersection = array_intersect($engravingLines, $defaultEngraving);

        //Reindex the intersection to fix scenario where default engraving is empty.
        sort($intersection);
        $defaultEngravingModified = $intersection != $defaultEngraving;

        //Prepare supportive messages list to check if line wording doesn't belong to the allowed supportive messages.
        $supportiveList = $this->getSupportiveMessageList();
        $supportiveMessages = [];
        foreach ($supportiveList as $supportiveItem) {
            $supportiveMessages[] = $supportiveItem->getData()['message'];
        }

        foreach ($engravingLines as $line) {
            $trimmedLine = trim($line);
            if (!empty($trimmedLine)) {
                $linesEmpty = false;

                //Do not execute engraving line length validation for admin area or prebuilt supportive messages or default engravings.
                if ($this->appState->getAreaCode() !== \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
                    && !in_array($line, $supportiveMessages)
                    && !in_array($line, $defaultEngraving)
                ) {
                    if ($this->string->strlen($line) > $maxLength
                        && $maxLength != 0
                        && $engravingType != 'name'
                        && $defaultEngravingModified
                    ) {
                        throw new LocalizedException(__('Some lines exceed the maximum allowed'));
                    }
                }
            }
        }

        // determine that lines aren't empty, or that this isn't required
        if ($linesEmpty && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            throw new LocalizedException(__('Please specify product\'s required option(s).'));
        }

        if (!in_array($engravingType, $this->validEngravingTypes())) {
            throw new LocalizedException(__('Invalid Engraving Type'));
        }

        $option = $this->getOption();
        if ($linesEmpty) { // if no text, then don't charge for engraving...
            $this->setUserValue('');
            $this->setIsValid(true);
            return $this;
        }

        $serializableValue = [];
        $serializableValue['type'] = $engravingType;
        $serializableValue['text'] = implode("\r\n", $userInputtedEngravingLines);
        $serializedValue = json_encode($serializableValue);
        $this->setUserValue($serializedValue);
        $this->setIsValid(true);

        return $this;
    }

    /**
     * Prepare option value for cart
     *
     * @return string|null Prepared option value
     */
    public function prepareForCart()
    {
        if ($this->getIsValid() && $this->getUserValue()) {
            return $this->getUserValue();
        } else {
            return null;
        }
    }

    /**
     * Flag to indicate that custom option has own customized output (blocks, native html etc.)
     *
     * @return boolean
     */
    public function isCustomizedView()
    {
        return true;
    }

    /**
     * Return option html
     *
     * @param array $optionInfo
     * @return string|void
     */
    public function getCustomizedView($optionInfo)
    {
        try {
            if (isset($optionInfo['print_value'])) {
                return $this->_getOptionHtml($optionInfo['print_value']);
            } elseif (isset($optionInfo['value'])) {
                return $optionInfo['value'];
            }
        } catch (\Exception $e) {
            return $optionInfo['value'];
        }
    }

    /**
     * Format File option html
     *
     * @param string|array $optionValue Serialized string of option data or its data array
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getOptionHtml($optionValue)
    {
        $value = nl2br($optionValue);
        return $value;
    }


    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            $this->_formattedOptionValue = $this->_escaper->escapeHtml($this->getEditableOptionValue($optionValue));
        }
        return $this->_formattedOptionValue;
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getPrintableOptionValue($optionValue)
    {
        return $this->getFormattedOptionValue($optionValue);
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getEditableOptionValue($optionValue)
    {
        $decode = json_decode($optionValue);
        return $decode->text;
    }

    /**
     * Parse user input value and return cart prepared value, i.e. "one, two" => "1,2"
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        return null;
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return string
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        return $optionValue;
    }

    /**
     * @param string $optionValue
     * @param float $basePrice
     * @return float|int
     */
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();
        $product = $option->getProduct();
        $unserialized = json_decode($optionValue, true);
        $formattedType = $unserialized['type'];
        $formattedValue = $unserialized['text'];
        $valueArray = explode("\r\n", $formattedValue);
        $messageList = $this->getSupportiveMessageList();
        $price = 0;

        if ($formattedType == 'name') {
            $product = $this->getData('configuration_item_option')->getProduct();
            $productCustomOptions = $product->getCustomOptions();
            $buyRequest = $productCustomOptions['info_buyRequest'];
            $serializedValue = $buyRequest->getData('value');
            $buyRequestData = json_decode($serializedValue);
            if (!isset($buyRequestData['name_string'])) {
                //I don't love it, but If I can't find the string to compare, let it go through. TODO!
                $price = $this->getOption()->getData('name_engraving_price');
                return $price;
            }
            $nameString = $buyRequestData['name_string'];
            if ($formattedValue != $nameString) { //If it's not the Name String, then charge the Custom rate.
                return $this->_getChargableOptionPrice(
                    $option->getPrice(),
                    $option->getPriceType() == 'percent',
                    $basePrice
                );
            }
            $price = $this->getOption()->getData('name_engraving_price');
            return $price;
        }
        if ($formattedType == 'supportive') {
            $price = $this->getOption()->getData('supportive_message_price');
            return $price;
        }

        foreach ($valueArray as $text) {
            $cleanText = str_replace(array("\r\n", "\r", "\n"), "", $text);
            $match = false;
            foreach ($messageList as $value) {
                $message = $value->getData()['message'];
                if (($message == $cleanText) || ($cleanText == '')) {
                    $price = $this->getOption()->getData('supportive_message_price');
                    $match = true;
                    break;
                }
            }
            // If any message is not a Supportive message, use Custom price
            if ($match == false) {
                return $this->_getChargableOptionPrice(
                    $option->getPrice(),
                    $option->getPriceType() == 'percent',
                    $basePrice
                );
            }
        }
        return $price;
    }


    /**
     * @return mixed
     */
    public function getSupportiveMessageList()
    {
        $this->searchCriteriaInterface
            ->setFilterGroups([$this->filterGroup]);
        $messageListReal = $this->repository->getList($this->searchCriteriaInterface)->getItems();
        return $messageListReal;
    }
}
