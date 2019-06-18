<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MemorialBracelets\EngravingDisplay\Block\Product;

use Magento\Framework\View\Element\Template;
use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SupportiveMessage extends Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /** @var $filterGroup FilterBuilder */
    protected $filterBuilder;

    /** @var $searchCriteriaInterface SearchCriteriaInterface */
    protected $searchCriteriaInterface;

    /** @var $filterGroup FilterGroup */
    protected $filterGroup;

    /** @var SupportiveMessageRepositoryInterface */
    protected $repository;

    /** @var PriceHelper */
    protected $pricingHelper;

    /**
     * @param SupportiveMessageRepositoryInterface $repository
     * @param SearchCriteriaInterface              $searchCriteriaInterface
     * @param FilterBuilder                        $filterBuilder
     * @param FilterGroup                          $filterGroup
     * @param PriceHelper                          $pricingHelper
     */
    public function __construct(
        SupportiveMessageRepositoryInterface $repository,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        PriceHelper $pricingHelper
    ) {
        $this->repository = $repository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @return int|null
     */
    public function getSupportiveMessageList()
    {
        /** apply filters and page size to product repository. */
        $this->searchCriteriaInterface
            ->setFilterGroups([$this->filterGroup]);
        $messageListReal = $this->repository->getList($this->searchCriteriaInterface)->getItems();
        return $messageListReal;
    }

    /**
     * @return string
     */
    public function getSupportiveMessageFormattedPrice($option, $flag = false)
    {
        if ($option) {
            $price = $this->getSupportiveMessagePrice($option, $flag);

            return $this->_formatPrice(
                [
                    'is_percent' => $option->getPriceType() == 'percent',
                    'pricing_value' => $price,
                ],
                $option
            );
        }
        return '';
    }

    public function getSupportiveMessagePrice($option, $flag = false)
    {
        if ($flag && $option->getPriceType() == 'percent') {
            $basePrice = $option->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($option->_getData('supportive_message_price') / 100);
        } else {
            $price = $option->_getData('supportive_message_price');
        }
        return $price;
    }

    /**
     * @return string
     */
    public function getNameEngravingFormattedPrice($option, $flag = false)
    {
        if ($option) {
            $price = $this->getNameEngravingPrice($option, $flag);

            return $this->_formatPrice(
                [
                    'is_percent' => $option->getPriceType() == 'percent',
                    'pricing_value' => $price,
                ],
                $option
            );
        }
        return '';
    }

    public function getNameEngravingPrice($option, $flag = false)
    {
        if ($flag && $option->getPriceType() == 'percent') {
            $basePrice = $option->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($option->_getData('name_engraving_price') / 100);
        } else {
            $price = $option->_getData('name_engraving_price');
        }
        return $price;
    }

    public function getNonHtmlFormattedPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Return formated price
     *
     * @param array $value
     * @param bool $flag
     * @return string
     */
    protected function _formatPrice($value, $option, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $customOptionPrice = $option->getProduct()->getPriceInfo()->getPrice('custom_option_price');
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $this->getProduct()
        );

        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }
}
