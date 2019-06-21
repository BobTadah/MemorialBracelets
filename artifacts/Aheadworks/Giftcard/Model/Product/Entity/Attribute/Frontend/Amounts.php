<?php
namespace Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend;

/**
 * Class Amounts
 * @package Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend
 */
class Amounts extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * Retrieve Input Renderer Class
     *
     * @return string|null
     */
    public function getInputRendererClass()
    {
        return 'Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form\GiftcardAmounts';
    }
}
