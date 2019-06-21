<?php

namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\Form\Renderer;

use Magento\Framework\Escaper;

class Expireafter extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Backend\Model\UrlInterface $url;
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->url = $url;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getToggleCode()
    {
        $htmlId = 'use_config_' . $this->getHtmlId();
        return "toggleValueElements(this, this.parentNode.parentNode);" .
        "if (!this.checked) toggleValueElements(\$('{$htmlId}'), \$('{$htmlId}').parentNode);";
    }

    public function getElementHtml()
    {
        parent::addClass('validate-digits');
        $html = parent::getElementHtml();
        $htmlId = 'use_config_' . $this->getHtmlId();
        $html .= '<br/><input id="' . $htmlId . '" name="use_config_expire_after"' ;
        $html .= ' checked="checked"';

        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';

        $settingsLink = $this->url->getUrl('adminhtml/system_config/edit', ['section' => 'aw_giftcard']);
        $html .= " <label for=\"{$htmlId}\" class=\"normal\">Use <a href='{$settingsLink}'>Config Settings</a></label>";
        $html .= '<script>' .
            'require(["prototype"], function(){'.
            'toggleValueElements($(\'' .
            $htmlId .
            '\'), $(\'' .
            $htmlId .
            '\').parentNode);' .
            '});'.
            '</script>';

        return $html;
    }
}
