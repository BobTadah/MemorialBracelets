<?php

namespace MemorialBracelets\CustomEmail\Block;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class Email
 * @package MemorialBracelets\CustomEmail\Block
 */
class Email extends Template
{
    /** @var $cmsBlockFactory BlockRepositoryInterface */
    protected $cmsBlockInterface;

    /** @var $cmsBlockFactory FilterProvider */
    protected $filterProvider;

    /** cms static block identifiers: */
    const CHECK_BLOCK_IDENTIFIER = 'check-email-block';
    const OTHER_BLOCK_IDENTIFIER = 'other-email-block';

    /**
     * Email constructor.
     * @param Template\Context $context
     * @param BlockRepositoryInterface $cmsBlockInterface
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        BlockRepositoryInterface $cmsBlockInterface,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->cmsBlockInterface = $cmsBlockInterface;
        $this->filterProvider    = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * This will attempt to return the cms static block content.
     * @return string
     */
    public function getContent()
    {
        if ($this->getOrder()->getPayment()->getMethod() == 'checkmo') {
            $content = $this->getEspot($this::CHECK_BLOCK_IDENTIFIER);
        } else {
            $content = $this->getEspot($this::OTHER_BLOCK_IDENTIFIER);
        }

        return $content;
    }

    /**
     * this will attempt to load the espot by identifier.
     * @param $identifier
     * @return null
     */
    protected function getEspot($identifier)
    {
        $content = null;

        $espot = $this->cmsBlockInterface->getById($identifier);
        if ($espot->isActive()) {
            $content = $this->filterProvider->getBlockFilter()
                ->setStoreId($this->_storeManager->getStore()->getId())->filter($espot->getContent());
        }

        return $content;
    }
}
