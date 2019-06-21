<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Order\Items\Column\Name;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Giftcard
 * @package Aheadworks\Giftcard\Block\Adminhtml\Order\Items\Column\Name
 */
class Giftcard extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    /**
     * @var null|array
     */
    protected $_giftCardOptions = null;

    /**
     * @var \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration
     */
    protected $_giftCardConfiguration;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration,
        array $data = []
    ) {
        $this->_giftCardConfiguration = $giftCardConfiguration;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $optionFactory,
            $data
        );
    }

    /**
     * @return array
     */
    public function getOrderOptions()
    {
        return $this->_giftCardConfiguration->getGiftCardOptionsData(
            $this->_getGiftCardOptions()
        );
    }

    /**
     * @return array|bool|null|string
     */
    protected function _getGiftCardOptions()
    {
        return $this->getItem()->getProductOptions();
    }
}
