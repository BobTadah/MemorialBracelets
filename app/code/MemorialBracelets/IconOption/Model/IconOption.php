<?php

namespace MemorialBracelets\IconOption\Model;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use MemorialBracelets\ExtensibleCustomOption\Api\ProductSpecificPriceConfigurationInterface;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption as ResourceModel;
use MemorialBracelets\IconOption\Api\IconOptionInterface;

class IconOption extends AbstractModel implements IdentityInterface, IconOptionInterface, ProductCustomOptionValuesInterface, ProductSpecificPriceConfigurationInterface
{
    const CACHE_TAG = 'option_icon';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getTitle()
    {
        return $this->getData(ResourceModel::FIELD_TITLE);
    }

    public function getIcon()
    {
        return $this->getData(ResourceModel::FIELD_ICON);
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

    public function getCreationTime()
    {
        return $this->getData(ResourceModel::FIELD_CREATION_TIME);
    }

    public function getUpdateTime()
    {
        return $this->getData(ResourceModel::FIELD_UPDATE_TIME);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG. '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /** @return array */
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
