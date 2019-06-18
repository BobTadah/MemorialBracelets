<?php

namespace MemorialBracelets\CharmOption\Model;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use MemorialBracelets\CharmOption\Api\CharmOptionInterface;
use MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption as ResourceModel;
use MemorialBracelets\ExtensibleCustomOption\Api\ProductSpecificPriceConfigurationInterface;

/**
 * Class CharmOption
 * @package MemorialBracelets\CharmOption\Model
 */
class CharmOption extends AbstractModel implements IdentityInterface, CharmOptionInterface, ProductCustomOptionValuesInterface, ProductSpecificPriceConfigurationInterface
{
    const CACHE_TAG = 'memorialbracelets_charmoption_charmoption';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getTitle()
    {
        return $this->getData(ResourceModel::FIELD_TITLE);
    }

    public function getPosition()
    {
        return $this->getData(ResourceModel::FIELD_POSITION);
    }

    public function getPrice()
    {
        return $this->getData(ResourceModel::FIELD_PRICE);
    }

    public function getPriceType()
    {
        return $this->getData(ResourceModel::FIELD_PRICE_TYPE);
    }

    public function getIsActive()
    {
        return $this->getData(ResourceModel::FIELD_IS_ACTIVE);
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled') ];
    }

    public function getAvailablePriceTypes()
    {
        return [self::PRICETYPE_FIXED => __('Fixed'), self::PRICETYPE_PERCENT => __('Percentage')];
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setData(ResourceModel::FIELD_TITLE, $title);
        return $this;
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getPosition();
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(ResourceModel::FIELD_POSITION, $sortOrder);
        return $this;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->setData(ResourceModel::FIELD_PRICE, $price);
        return $this;
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType)
    {
        $this->setData(ResourceModel::FIELD_PRICE_TYPE, $priceType);
        return $this;
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return null;
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        // NOT IMPLEMENTED
        return $this;
    }

    /**
     * Get Option type id
     *
     * @return int|null
     */
    public function getOptionTypeId()
    {
        return $this->getId();
    }

    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     */
    public function setOptionTypeId($optionTypeId)
    {
        $this->setId($optionTypeId);
        return $optionTypeId;
    }

    public function getPriceBasedOnProduct($product)
    {
        $price = $this->getPrice();
        if ($this->getPriceType() == self::PRICETYPE_PERCENT) {
            $basePrice = $product->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            $price = $basePrice * ($price / 100);
        }
        return $price;
    }
}
