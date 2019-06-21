<?php
namespace Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend;

/**
 * Class AllowOpenAmount
 * @package Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend
 */
class AllowOpenAmount extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * Retrieve Input Renderer Class
     *
     * @return string|null
     */
    public function getInputRendererClass()
    {
        return 'Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form\AllowOpenAmount';
    }
}
