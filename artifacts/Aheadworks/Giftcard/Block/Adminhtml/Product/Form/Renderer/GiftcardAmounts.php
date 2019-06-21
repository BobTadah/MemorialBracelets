<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer;

/**
 * Class GiftcardAmounts
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer
 */
class GiftcardAmounts extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var array|null
     */
    protected $_websites = null;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Initialize block template
     */
    protected $_template = 'Aheadworks_Giftcard::product/edit/amounts.phtml';

    /**
     * @var string
     */
    protected $_amountsTableId = 'aw-giftcard-amounts-table';

    /**
     * @var string
     */
    protected $_addButtonId = 'aw-giftcard-product-add-amount-btn';

    /**
     * @var string
     */
    protected $_allowOpenAmountCheckboxId = 'aw_gc_allow_open_amount_checkbox';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
    }

    /**
     * @return string
     */
    public function getAmountsTableId()
    {
        return $this->_amountsTableId;
    }

    /**
     * @return string
     */
    public function getRowsContainerId()
    {
        return $this->_amountsTableId . '_container';
    }

    /**
     * @return string
     */
    public function getAmountsErrorId()
    {
        return $this->_amountsTableId . '_error_container';
    }

    /**
     * Retrieve 'Add Amount' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'label' => __('Add Amount'),
                    'class' => 'add',
                    'id' => $this->_addButtonId
                ]
            )
            ->toHtml();
    }

    /**
     * @return string
     */
    public function getJsInitScripts()
    {
        $options = \Zend_Json::encode([
            'amountsTableSelector' => '#' . $this->_amountsTableId,
            'amountsRowTemplate' => $this->_getRowTemplate(),
            'rowsContainerSelector' => '#' . $this->getRowsContainerId(),
            'errorContainerSelector' => '#' . $this->getAmountsErrorId(),
            'allowOpenAmountCheckboxSelector' => '#' . $this->_allowOpenAmountCheckboxId,
            'addBtnSelector' => '#' . $this->_addButtonId,
            'defaultWebsiteId' => $this->getDefaultWebsite(),
            'values' => $this->_getAmountsValues(),
            'formSelector' => 'form[data-form=edit-product]',
            'errorMessageDuplicateAmount' => __('Duplicate amount found.'),
            'errorMessageEmptyAmount' => __('Specify amount options.')
        ]);
        $amountsTableId = $this->_amountsTableId;
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksGCAmountControl'], function($, giftCardAmountsControl){
            $(document).ready(function() {
                giftCardAmountsControl({$options}, $('#{$amountsTableId}'));
            });
        });
    </script>
HTML;
    }

    /**
     * @return string
     */
    protected function _getRowTemplate()
    {
        $htmlClass = $this->getElement()->getClass();
        $htmlName = $this->getElement()->getName();
        $websiteOptions = '';
        foreach ($this->getWebsites() as $value => $info) {
            $label = sprintf(
                "%s [%s]",
                $this->escapeJsQuote($this->escapeHtml($info['name'])),
                !empty($info['currency']) ? $this->escapeHtml($info['currency']) : ''
            );
            $websiteOptions .= "<option value=\"{$value}\">{$label}</option>";
        }
        $deleteBtnLabel = __('Delete');
        return <<<HTML
    <tr>
        <td>
            <select class="{$htmlClass} required-entry"
                name="{$htmlName}[<%- data.index %>][website_id]"
                id="aw_gc_amount_row_<%- data.index %>_website"
                data-index="<%- data.index %>"
                data-type="website">{$websiteOptions}</select>
        </td>
        <td>
            <input class="{$htmlClass} required-entry validate-zero-or-greater aw-gc-amounts-duplicate"
                type="text"
                name="{$htmlName}[<%- data.index %>][price]"
                value="<%- data.price %>"
                id="group_price_row_<%- data.index %>_price"
                data-index="<%- data.index %>"
                data-type="amount" />
            </td>
        <td class="col-delete">
            <input type="hidden" name="{$htmlName}[<%- data.index %>][delete]" class="delete" value="" id="aw_gc_amount_row_<%- data.index %>_delete" />
            <button title="{$deleteBtnLabel}" type="button" class="action- scalable delete icon-btn delete-product-option" id="group_price_row_<%- data.index %>_delete_button">
                <span>{$deleteBtnLabel}</span>
            </button>
        </td>
    </tr>
HTML;
    }

    protected function _getAmountsValues()
    {
        $data = [];
        if ($values = $this->getElement()->getValue()) {
            foreach ($values as $value) {
                $data[] = [
                    'website_id' => $value['website_id'],
                    'price' => $value['price']
                ];
            }
        }
        return $data;
    }

    /**
     * @return array|null
     */
    public function getWebsites()
    {
        if ($this->_websites !== null) {
            return $this->_websites;
        }
        $this->_websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()]
        ];
        $websites = $this->_storeManager->getWebsites(false);
        $productWebsiteIds = $this->getProduct()->getWebsiteIds();
        foreach ($websites as $website) {
            /** @var $website \Magento\Store\Model\Website */
            if (!in_array($website->getId(), $productWebsiteIds)) {
                continue;
            }
            $this->_websites[$website->getId()] = [
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            ];
        }
        return $this->_websites;
    }

    /**
     * @return int|null|string
     */
    public function getDefaultWebsite()
    {
        return $this->_storeManager->getStore($this->getProduct()->getStoreId())->getWebsiteId();
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }
}
