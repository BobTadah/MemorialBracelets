<?php
namespace Aheadworks\Giftcard\Block\Order\Item\Email;

use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Gift Card items Renderer
 * @package Aheadworks\Giftcard\Block\Order\Item\Email
 */
class Renderer extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration
     */
    protected $giftCardConfiguration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Aheadworks\Giftcard\Helper\Catalog\Product\Configuration $giftCardConfiguration,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->giftCardConfiguration = $giftCardConfiguration;
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        return array_merge(
            $this->getGiftCardOptions(),
            parent::getItemOptions()
        );
    }

    /**
     * Get list of Gift Card options for product
     *
     * @return array
     */
    protected function getGiftCardOptions()
    {
        return $this->giftCardConfiguration->getGiftCardOptionsData(
            $this->getCustomOptions()
        );
    }

    /**
     * @return array|bool|string
     */
    protected function getCustomOptions()
    {
        $options = $this->getItem()->getProductOptions();
        unset($options[TypeGiftCard::ATTRIBUTE_CODE_TYPE]);
        unset($options['aw_gc_created_codes']);
        return $options;
    }
}
