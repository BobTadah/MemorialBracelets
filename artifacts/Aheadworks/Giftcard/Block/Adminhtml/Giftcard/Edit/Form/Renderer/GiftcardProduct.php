<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Gift Card Product link renderer
 * @package Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\Form\Renderer
 */
class GiftcardProduct extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element
            ->setData('href', $this->getUrl('catalog/product/edit', ['id' => $element->getValue()]))
            ->setData('style', 'line-height: 3.2rem;')
        ;
        $product = $this->_productRepository->getById($element->getValue());
        if ($product->getId()) {
            $element->setValue($product->getName());
        }
        $this->_element = $element;
        return $this->toHtml();
    }
}