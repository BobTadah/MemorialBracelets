<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer;

use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType;

/**
 * Class GiftcardTemplates
 * @package Aheadworks\Giftcard\Block\Adminhtml\Product\Form\Renderer
 */
class GiftcardTemplates extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * Initialize block template
     */
    protected $_template = 'Aheadworks_Giftcard::product/edit/templates.phtml';

    /**
     * @var string
     */
    protected $templatesTableId = 'aw-giftcard-templates-table';

    /**
     * @var string
     */
    protected $addButtonId = 'aw-giftcard-product-add-template-btn';

    /**
     * @var string
     */
    protected $typeSelectId = 'aw_gc_type';

    /**
     * @var \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate
     */
    protected $giftCardEmailTemplateSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate $giftCardEmailTemplateSource
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate $giftCardEmailTemplateSource,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
        $this->fileSize = $fileSize;
        $this->mediaConfig = $mediaConfig;
        $this->coreRegistry = $registry;
        $this->systemStore = $systemStore;
        $this->giftCardEmailTemplateSource = $giftCardEmailTemplateSource;
    }

    /**
     * @return string
     */
    public function getTemplatesTableId()
    {
        return $this->templatesTableId;
    }

    /**
     * @return string
     */
    public function getRowsContainerId()
    {
        return $this->templatesTableId . '_container';
    }

    /**
     * @return string
     */
    public function getAmountsErrorId()
    {
        return $this->templatesTableId . '_error_container';
    }

    /**
     * Retrieve 'Add Template' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'label' => __('Add Template'),
                    'class' => 'add',
                    'id' => $this->addButtonId
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
            'templatesTableSelector' => '#' . $this->templatesTableId,
            'templatesRowTemplate' => $this->_getRowTemplate(),
            'templatesImageTemplate' => $this->_getImageTemplate(),
            'rowsContainerSelector' => '#' . $this->getRowsContainerId(),
            'errorContainerSelector' => '#' . $this->getAmountsErrorId(),
            'addBtnSelector' => '#' . $this->addButtonId,
            'values' => $this->_getTemplatesValues(),
            'formSelector' => 'form[data-form=edit-product]',
            'fieldContainerSelector' => '[data-attribute-code=' . $this->getElement()->getHtmlId() . ']',
            'typeSelect' => '#' . $this->typeSelectId,
            'fieldHideValue' => GiftcardType::VALUE_PHYSICAL
        ]);
        $amountsTableId = $this->templatesTableId;
        return <<<HTML
    <script>
        require(['jquery', 'aheadworksGCTemplatesControl'], function($, giftCardTemplatesControl){
            $(document).ready(function() {
                giftCardTemplatesControl({$options}, $('#{$amountsTableId}'));
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

        $storeOptions = '';
        $storeValues = $this->systemStore->getStoreValuesForForm(false, true);
        foreach ($storeValues as $key => $value) {
            if (!is_array($value)) {
                $storeOptions .= $this->_optionToHtml(['value' => $key, 'label' => $value], $storeValues);
            } elseif (is_array($value['value'])) {
                $storeOptions .= '<optgroup label="' . $value['label'] . '">' . "\n";
                foreach ($value['value'] as $groupItem) {
                    $storeOptions .= $this->_optionToHtml($groupItem, $storeValues);
                }
                $storeOptions .= '</optgroup>' . "\n";
            } else {
                $storeOptions .= $this->_optionToHtml($value, $storeValues);
            }
        }

        $templateOptions = '';
        foreach ($this->giftCardEmailTemplateSource->getOptionArray() as $value => $label) {
            $templateOptions .= "<option value=\"{$value}\">{$label}</option>";
        }
        $imageUploaderTemplate = $this->_getImageUploaderTemplate();
        $deleteBtnLabel = __('Delete');
        return <<<HTML
    <tr>
        <td>
            <select class="{$htmlClass} required-entry"
                name="{$htmlName}[<%- data.index %>][store_id]"
                id="aw_gc_template_row_<%- data.index %>_store"
                data-index="<%- data.index %>">{$storeOptions}</select>
        </td>
        <td>
            <select class="{$htmlClass} required-entry"
                name="{$htmlName}[<%- data.index %>][template]"
                id="aw_gc_template_row_<%- data.index %>_template"
                data-index="<%- data.index %>">{$templateOptions}</select>
        </td>
        <td>
            {$imageUploaderTemplate}
            <input type="hidden" name="{$htmlName}[<%- data.index %>][image]" value="" id="aw_gc_template_row_<%- data.index %>_image" />
        </td>
        <td class="col-delete">
            <input type="hidden" name="{$htmlName}[<%- data.index %>][delete]" class="delete" value="" id="aw_gc_template_row_<%- data.index %>_delete" />
            <button title="{$deleteBtnLabel}" type="button" class="action- scalable delete icon-btn delete-product-option" id="group_price_row_<%- data.index %>_delete_button">
                <span>{$deleteBtnLabel}</span>
            </button>
        </td>
    </tr>
HTML;
    }

    protected function _getImageUploaderTemplate()
    {
        $htmlId = $this->_escaper->escapeHtml($this->getElement()->getHtmlId());
        $uploadUrl = $this->_escaper->escapeHtml($this->_getUploadUrl());
        $spacerImage = $this->_assetRepo->getUrl('images/spacer.gif');
        $imagePlaceholderText = __('Click here or drag and drop to add images.');
        return <<<HTML
<div id="aw_gc_template_row_<%- data.index %>_image_container" class="images"
    data-role="image-uploader"
    data-mage-init='{"aheadworksGCImageUploader":{}}'
    data-max-file-size="{$this->_getFileMaxSize()}"
    >
    <div class="image image-placeholder">
        <input type="file" name="image" data-url="{$uploadUrl}" />
        <img class="spacer" src="{$spacerImage}"/>
        <p class="image-placeholder-text">{$imagePlaceholderText}</p>
    </div>
</div>
HTML;
    }

    protected function _getImageTemplate()
    {
        $spacerImage = $this->_assetRepo->getUrl('images/spacer.gif');
        $deleteImageText = __('Delete image');
        return <<<HTML
        <div class="image">
            <img class="spacer" src="{$spacerImage}"/>
            <img
                class="product-image"
                src="<%- data.url %>"
                alt="<%- data.label %>" />
            <div class="actions">
                <button type="button" class="action-delete" data-role="delete-button" title="{$deleteImageText}">
                    <span>{$deleteImageText}</span>
                </button>
            </div>
        </div>
HTML;

    }

    protected function _getTemplatesValues()
    {
        $result = [];
        $values = $this->getElement()->getValue();
        if (is_array($values)) {
            foreach ($values as $value) {
                $data = [
                    'template_id' => $value['template'],
                    'store_id' => $value['store_id']
                ];
                if ($value['image']) {
                    $data['image'] = $value['image'];
                    $data['image_url'] = $this->mediaConfig->getTmpMediaUrl($value['image']);
                }
                $result[] = $data;
            }
        }
        return $result;
    }

    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="' . $option['label'] . '">' . "\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .= '</optgroup>' . "\n";
        } else {
            $html = '<option value="' . $this->_escape($option['value']) . '"';
            $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
            $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
            if (in_array($option['value'], $selected)) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        }
        return $html;
    }

    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    protected function _getUploadUrl()
    {
        return $this->url->getUrl('aw_giftcard_admin/product/imageUpload');
    }

    /**
     * @return int
     */
    protected function _getFileMaxSize()
    {
        return $this->fileSize->getMaxFileSize();
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
