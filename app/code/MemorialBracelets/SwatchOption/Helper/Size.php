<?php

namespace MemorialBracelets\SwatchOption\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Size
 * @package MemorialBracelets\SwatchOption\Helper
 */
class Size extends AbstractHelper
{
    /** @var $cmsBlockFactory BlockRepositoryInterface */
    protected $cmsBlockInterface;

    /** @var $cmsBlockFactory FilterProvider */
    protected $filterProvider;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** CMS size block identifier */
    const SIZE_IDENTIFIER = 'attribute-size-block';

    /**
     * Size constructor.
     * @param Context $context
     * @param BlockRepositoryInterface $cmsBlockInterface
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        BlockRepositoryInterface $cmsBlockInterface,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->cmsBlockInterface = $cmsBlockInterface;
        $this->filterProvider    = $filterProvider;
        $this->storeManager      = $storeManager;
        parent::__construct($context);
    }

    /**
     * this will attempt to return the size CMS block html if it exists.
     * @return string
     */
    public function getSizeCmsBlock()
    {
        $blockHtml = '';

        $espot = $this->cmsBlockInterface->getById($this::SIZE_IDENTIFIER);
        if ($espot && $espot->isActive()) {
            $blockHtml = $this->filterProvider->getBlockFilter()
                ->setStoreId($this->storeManager->getStore()->getId())->filter($espot->getContent());
        }

        return $blockHtml;
    }
}
