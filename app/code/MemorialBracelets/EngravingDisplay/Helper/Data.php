<?php

namespace MemorialBracelets\EngravingDisplay\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;

/**
 * Class Data
 * @package MemorialBracelets\EngravingDisplay\Helper
 */
class Data extends AbstractHelper
{
    /** @var $scopeConfig ScopeInterface */
    protected $scopeConfig;

    /** @var $categoryRepo CategoryRepositoryInterface */
    protected $categoryRepo;

    /** @var Registry */
    protected $coreRegistry;

    /**
     * Data constructor.
     * @param Context                     $context
     * @param CategoryRepositoryInterface $categoryRepo
     * @param Registry                    $coreRegistry
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepo,
        Registry $coreRegistry
    ) {
        $this->scopeConfig  = $context->getScopeConfig();
        $this->categoryRepo = $categoryRepo;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * this will return if the engraving module is enabled or not.
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'catalog/engravable/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * this will check if the current registered product has an event that excludes it from customization.
     * @return bool
     */
    public function isExcluded()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getCurrentProduct();
        $flag    = false;

        if ($product) {
            $excludedEvents = $this->getExcludedEvents();
            $productEvent   = $product->getEvent();

            if (!empty($excludedEvents) && $productEvent && in_array($productEvent, $excludedEvents)) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * this will grab the events that are selected in the multi-select in the admin.
     * path: store -> configuration -> catalog -> engraving options -> excluded events
     * @return array
     */
    protected function getExcludedEvents()
    {
        $data   = [];
        $events = $this->scopeConfig->getValue(
            'catalog/engravable/excluded_events',
            ScopeInterface::SCOPE_STORE
        );

        if ($events) {
            $data = explode(',', $events);
        }

        return $data;
    }

    /**
     * this will attempt to return the current product.
     * @return \Magento\Catalog\Model\Product
     */
    protected function getCurrentProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * this will return the engraving message.
     * @return mixed
     */
    public function getEngravingMessage()
    {
        return $this->scopeConfig->getValue(
            'catalog/engravable/pdp_message',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * this will return the Vietnam engraving message.
     * @return mixed
     */
    public function getVietnamEngravingMessage()
    {
        return $this->scopeConfig->getValue(
            'catalog/engravable/pdp_message_vietnam',
            ScopeInterface::SCOPE_STORE
        );
    }
}
