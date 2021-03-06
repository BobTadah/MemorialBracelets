<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class GiftcardAmounts
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form
 */
class GiftcardAmounts extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer\GiftcardAmounts $renderer
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer\GiftcardAmounts $renderer,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_renderer = $renderer;
    }

    public function setRenderer(RendererInterface $renderer)
    {
        return $this;
    }
}
