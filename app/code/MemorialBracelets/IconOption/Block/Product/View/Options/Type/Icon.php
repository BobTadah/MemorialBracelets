<?php

namespace MemorialBracelets\IconOption\Block\Product\View\Options\Type;

use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use \Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\View\Element\Template\Context;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\IconOption\Helper\IconStorageConfiguration;
use MemorialBracelets\IconOption\Model\IconOption;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption as ResourceModel;

class Icon extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /** @var IconOptionRepositoryInterface  */
    protected $repository;

    protected $criteriaFactory;

    protected $icons;

    protected $configuration;

    public function __construct(
        Context $context,
        PriceHelper $pricingHelper,
        CatalogHelper $catalogData,
        IconOptionRepositoryInterface $repository,
        SearchCriteriaBuilderFactory $criteriaFactory,
        IconStorageConfiguration $configuration,
        array $data = []
    ) {
        $this->repository = $repository;
        $this->criteriaFactory = $criteriaFactory;
        $this->configuration = $configuration;
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Returns default value to show in picker
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/'.$this->getOption()->getId());
    }

    /**
     * @return IconStorageConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return IconOption[]
     */
    public function getIcons()
    {
        if (!isset($this->icons)) {
            /** @var SearchCriteriaBuilder $criteriaBuilder */
            $criteriaBuilder = $this->criteriaFactory->create();
            $criteriaBuilder->addSortOrder(ResourceModel::FIELD_POSITION, 'ASC');
            $criteria = $criteriaBuilder->create();
            $this->icons = $this->repository->getList($criteria)->getItems();
        }
        return $this->icons;
    }

    public function getIconPrice(IconOption $icon)
    {
        if ($icon->getPriceType() == IconOption::PRICETYPE_PERCENT) {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            return $basePrice * ($icon->getPrice() / 100);
        } else {
            return $icon->getPrice();
        }
    }

    public function getFormattedIconPrice(IconOption $icon)
    {
        $value = $this->getIconPrice($icon);
        return $this->pricingHelper->currency($value, true, false);
    }
}
