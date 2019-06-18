<?php

namespace MemorialBracelets\CategoryLandingPage\Plugin;

use Magento\Framework\Exception\LocalizedException;

class Mode
{
    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    protected $messageManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Add constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->messageManager = $messageManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->objectManager = $objectManager;
    }

    /**
     * @param callable $proceed
     * @return array|void
     */
    public function aroundGetAllOptions(\Magento\Catalog\Model\Category\Attribute\Source\Mode $mode, callable $proceed)
    {
        $result = $proceed();

        $newResult = [
            ['value' => \Magento\Catalog\Model\Category::DM_PRODUCT, 'label' => __('Products only')],
            ['value' => \Magento\Catalog\Model\Category::DM_PAGE, 'label' => __('Static block only')],
            ['value' => \Magento\Catalog\Model\Category::DM_MIXED, 'label' => __('Static block and products')],
            ['value' => 'LANDING_PAGE', 'label' => __('Landing Page')],
        ];
        return $newResult;
    }
}
