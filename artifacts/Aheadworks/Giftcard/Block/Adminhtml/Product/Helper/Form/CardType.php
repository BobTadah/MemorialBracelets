<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType;

/**
 * Class CardType
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Helper\Form
 */
class CardType extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @var string
     */
    protected $weightId = 'weight';

    /**
     * Get the element Html.
     *
     * @return string
     */
    public function getElementHtml()
    {
        return parent::getElementHtml() . $this->getJsInitScripts();
    }

    protected function getJsInitScripts()
    {
        $options = \Zend_Json::encode([
            'weightSwitcherSelector' => '[data-role=weight-switcher]',
            'weightSelector' => '#' . $this->weightId,
            'allowWeightValues' => [
                (string)GiftcardType::VALUE_PHYSICAL,
                (string)GiftcardType::VALUE_COMBINED
            ]
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksGCCardTypeControl'], function($, cardTypeControl){
            $(document).ready(function() {
                cardTypeControl({$options}, $('#{$this->getHtmlId()}'));
            });
        });
    </script>
HTML;
    }
}
