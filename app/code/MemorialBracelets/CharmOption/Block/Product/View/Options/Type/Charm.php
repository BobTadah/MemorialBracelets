<?php

namespace MemorialBracelets\CharmOption\Block\Product\View\Options\Type;

use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use \Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\View\Element\Template\Context;
use MemorialBracelets\CharmOption\Api\CharmOptionInterface;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Model\CharmOption\IconStorageConfiguration;
use MemorialBracelets\CharmOption\Model\ResourceModel\CharmOption;

class Charm extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /** @var CharmOptionRepositoryInterface  */
    protected $repository;

    protected $criteriaFactory;

    protected $configuration;

    protected $charms;

    public function __construct(
        Context $context,
        PriceHelper $pricingHelper,
        CatalogHelper $catalogData,
        CharmOptionRepositoryInterface $repository,
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
     * @return \MemorialBracelets\CharmOption\Model\CharmOption[]
     */
    public function getCharms()
    {
        if (!isset($this->charms)) {
            /** @var SearchCriteriaBuilder $criteriaBuilder */
            $criteriaBuilder = $this->criteriaFactory->create();
            $criteriaBuilder->addSortOrder(CharmOption::FIELD_POSITION, 'ASC');
            $criteria = $criteriaBuilder->create();
            $this->charms = $this->repository->getList($criteria)->getItems();
        }
        return $this->charms;
    }

    /**
     * @return IconStorageConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getCharmPrice(CharmOptionInterface $charm)
    {
        if ($charm->getPriceType() == CharmOptionInterface::PRICETYPE_PERCENT) {
            $basePrice = $this->getProduct()->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getValue();
            return $basePrice * ($charm->getPrice() / 100);
        } else {
            return $charm->getPrice();
        }
    }

    public function getFormattedCharmPrice(CharmOptionInterface $charm)
    {
        $value = $this->getCharmPrice($charm);

        return $this->pricingHelper->currency($value, true, false);
    }
}
