<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class AllowOpenAmount
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form
 */
class AllowOpenAmount extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var string
     */
    protected $_openAmountMinInputId = 'aw_gc_open_amount_min';

    /**
     * @var string
     */
    protected $_openAmountMaxInputId = 'aw_gc_open_amount_max';

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        $checked = $this->getValue() ? "checked=\"checked\"" : '';
        return <<<HTML
    <input
        id="{$this->getHtmlId()}_checkbox"
        name="{$this->getName()}_checkbox"
        type="checkbox"
        {$this->serialize($this->getHtmlAttributes())}
        {$checked}
        />
        {$this->_getJsInitScripts()}
    <input id="{$this->getHtmlId()}" type="hidden" name="{$this->getName()}" value="{$this->getValue()}" />
HTML;
    }

    protected function _getJsInitScripts()
    {
        $options = \Zend_Json::encode([
            'openAmountMinSelector' => '#' . $this->_openAmountMinInputId,
            'openAmountMaxSelector' => '#' . $this->_openAmountMaxInputId,
            'valueSelector' => '#' . $this->getHtmlId(),
            'errorMessageMaxAmount' => __('"Open Amount Max Value" should be greater than "Open Amount Min Value"')
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksGCAllowOpenAmountControl'], function($, allowOpenAmountControl){
            $(document).ready(function() {
                allowOpenAmountControl({$options}, $('#{$this->getHtmlId()}_checkbox'));
            });
        });
    </script>
HTML;
    }
}
