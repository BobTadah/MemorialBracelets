<?php

namespace MemorialBracelets\CheckHandling\Helper;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class Data
 * @package MemorialBracelets\CheckoutHandling\Helper
 */
class Data extends AbstractHelper
{
    /** @var ProductCustomOptionRepositoryInterface $customOptionRepo */
    protected $customOptionRepo;

    /** @var CharmOptionRepositoryInterface $charmOptionRepo */
    protected $charmOptionRepo;

    /** @var IconOptionRepositoryInterface $iconOptionRepo */
    protected $iconOptionRepo;

    /** @var  PriceHelper $priceHelper */
    protected $priceHelper;

    /**
     * Data constructor.
     * @param Context                                $context
     * @param ProductCustomOptionRepositoryInterface $customOptionRepository
     * @param CharmOptionRepositoryInterface         $charmOptionRepository
     * @param IconOptionRepositoryInterface          $iconOptionRepository
     * @param PriceHelper                            $priceHelper
     */
    public function __construct(
        Context $context,
        ProductCustomOptionRepositoryInterface $customOptionRepository,
        CharmOptionRepositoryInterface $charmOptionRepository,
        IconOptionRepositoryInterface $iconOptionRepository,
        PriceHelper $priceHelper
    ) {
        $this->customOptionRepo = $customOptionRepository;
        $this->charmOptionRepo  = $charmOptionRepository;
        $this->iconOptionRepo   = $iconOptionRepository;
        $this->priceHelper      = $priceHelper;
        parent::__construct($context);
    }

    /**
     * this function will attempt to retrieve the custom option arguments price. This expects
     * a sales model item and a product option array.
     * @param Item $product
     * @param      $option
     * @return mixed
     */
    public function getOptionPrice(Item $product, $option)
    {
        $price = '';

        if ($product && isset($option['option_value'])) {
            try {
                switch ($option['option_type']) {
                    case 'picker': // charms
                        $tempOption = $this->charmOptionRepo->getById($option['option_value']);
                        break;
                    case 'iconpicker': // icons
                        $tempOption = $this->iconOptionRepo->getById($option['option_value']);
                        break;
                    case 'engraving':
                        //Check if default or custom. If it's default, then we return 0 (since it's free of charge)
                        if (json_decode($option['option_value'])->type == 'name') {
                            $price = $this->priceHelper->currency(0, true, false);
                        } else {
                            $tempOption = $this->defaultCase($product, $option);
                        }
                        break;
                    default: // other custom options
                        $tempOption = $this->defaultCase($product, $option);
                }

                if ($tempOption) {
                    $price = $tempOption->getPrice();
                    $price = ($price) ? $this->priceHelper->currency($price, true, false) : '';
                }
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        return $price;
    }

    /**
     * Default case for switch.
     *
     * @param Item $product
     * @param array $option
     * @return ProductCustomOptionRepositoryInterface $tempOption
     */
    protected function defaultCase($product, $option)
    {
        $tempOption  = $this->customOptionRepo->get($product->getSku(), $option['option_id']);
        $attrOptions = $tempOption->getValues();
        // cycle options (more then 1 are sometimes returned)
        foreach (($attrOptions ? $attrOptions : []) as $attrOption) {
            if ($attrOption->getTitle() == $option['value']) {
                $tempOption = $attrOption;
            }
        }

        return $tempOption;
    }
}
