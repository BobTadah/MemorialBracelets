<?php

namespace MemorialBracelets\CheckoutMessage\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CheckoutConfigProvider
 * @package MemorialBracelets\CheckoutMessage\Model
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /** @var BlockRepositoryInterface $cmsBlockFactory */
    protected $cmsBlockInterface;

    /** @var FilterProvider $cmsBlockFactory */
    protected $filterProvider;

    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var StoreManagerInterface $storeManager */
    protected $storeManager;

    protected $cmsBlocks;

    /**
     * CheckoutConfigProvider constructor.
     * @param BlockRepositoryInterface $cmsBlockInterface
     * @param FilterProvider           $filterProvider
     * @param LoggerInterface          $logger
     * @param StoreManagerInterface    $storeManager
     * @param                          $cmsBlocks
     */
    public function __construct(
        BlockRepositoryInterface $cmsBlockInterface,
        FilterProvider $filterProvider,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        $cmsBlocks
    ) {
        $this->cmsBlockInterface = $cmsBlockInterface;
        $this->filterProvider    = $filterProvider;
        $this->logger            = $logger;
        $this->storeManager      = $storeManager;
        $this->cmsBlocks         = $cmsBlocks;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $html = '';
        foreach ($this->buildCmsBlocks() as $blockHtml) {
            $html .= $blockHtml;
        }

        return [
            'cmsBlocks' => $html
        ];
    }

    /**
     * this will attempt to return all the cms blocks html.
     * @return array
     */
    protected function buildCmsBlocks()
    {
        $cmsBlocks = [];
        $blockIds  = $this->cmsBlocks;

        if ($blockIds && !empty($blockIds)) {
            try {
                foreach ($blockIds as $blockId) {
                    array_push($cmsBlocks, $this->getCmsBlock($blockId));
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $cmsBlocks;
    }

    /**
     * this will attempt to load an cms block by identifier and return its html content.
     * @param $id
     * @return null
     */
    protected function getCmsBlock($id)
    {
        $content = null;

        $espot = $this->cmsBlockInterface->getById($id);
        if ($espot->isActive()) {
            $content = $this->filterProvider->getBlockFilter()->setStoreId($this->storeManager->getStore()->getId())->filter($espot->getContent());
        }

        return $content;
    }
}
