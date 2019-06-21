<?php
namespace Aheadworks\Giftcard\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\System\Store;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Container;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;
use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate;
use Magento\Framework\File\Size;
use Magento\Framework\View\Asset\Repository;
use Magento\Catalog\Model\Product\Media\Config;

/**
 * Class Templates
 */
class Templates extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardEmailTemplate
     */
    protected $giftCardEmailTemplateSource;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * Templates constructor.
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param Data $directoryHelper
     * @param Store $systemStore
     * @param GiftcardEmailTemplate $giftCardEmailTemplateSource
     * @param Size $fileSize
     * @param Repository $assetRepository
     * @param Config $mediaConfig
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        Data $directoryHelper,
        Store $systemStore,
        GiftcardEmailTemplate $giftCardEmailTemplateSource,
        Size $fileSize,
        Repository $assetRepository,
        Config $mediaConfig
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->directoryHelper = $directoryHelper;
        $this->systemStore = $systemStore;
        $this->giftCardEmailTemplateSource = $giftCardEmailTemplateSource;
        $this->fileSize = $fileSize;
        $this->assetRepository = $assetRepository;
        $this->mediaConfig = $mediaConfig;
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->getGroupCodeByField($meta, static::CONTAINER_PREFIX . TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES)) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            static::CONTAINER_PREFIX . TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES,
            $meta
        );

        $meta = $this->arrayManager->set(
            $containerPath . '/children/' . TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'dynamicRows',
                            'label' => __('Email Templates'),
                            'required' => 1,
                            'renderDefaultRecord' => false,
                            'recordTemplate' => 'record',
                            'dataScope' => '',
                            'dndConfig' => [
                                'enabled' => false,
                            ],
                            'disabled' => false,
                            //'sortOrder' => 40,
                        ],
                    ],
                ],
                'children' => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'isTemplate' => true,
                                    'is_collection' => true,
                                    'component' => 'Magento_Ui/js/dynamic-rows/record',
                                    'dataScope' => '',
                                ],
                            ],
                        ],
                        'children' => [
                            'store_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => Text::NAME,
                                            'formElement' => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataScope' => 'store_id',
                                            'label' => __('Store'),
                                            'options' => $this->getStores(),
                                        ],
                                    ],
                                ],
                            ],
                            'template' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => Text::NAME,
                                            'formElement' => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataScope' => 'template',
                                            'label' => __('Email Template'),
                                            'options' => $this->getTemplates(),
                                        ],
                                    ],
                                ],
                            ],
                            'image' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label' => __('Image'),
                                            'componentType' => 'fileUploader',
                                            'component' => 'Aheadworks_Giftcard/js/product/template-image-uploader',
                                            'previewTmpl' => 'Aheadworks_Giftcard/ui/product/template-image-preview',
                                            'template' => 'Aheadworks_Giftcard/ui/product/template-image-uploader',
                                            'fileInputName' => 'image',
                                            'imageSpacer' => $this->assetRepository->getUrl('images/spacer.gif'),
                                            'imagePlaceholderText' => __('Click here or drag and drop to add images.'),
                                            'isMultipleFiles' => false,
                                            'uploaderConfig' => [
                                                'url' => 'aw_giftcard_admin/product/imageUpload'
                                            ],
                                            'maxFileSize' => $this->getFileMaxSize(),
                                            'dataScope' => 'image',
                                            'images' => $this->getTemplateImagesData(),
                                        ],
                                    ],
                                ],
                            ],
                            'actionDelete' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => 'actionDelete',
                                            'dataType' => Text::NAME,
                                            'label' => __('Action'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Get stores list
     *
     * @return array
     */
    protected function getStores()
    {
        $storeValues = $this->systemStore->getStoreValuesForForm(false, true);
        return $storeValues;
    }

    /**
     * Get templates list
     *
     * @return array
     */
    protected function getTemplates()
    {
        $templateValues = $this->giftCardEmailTemplateSource->toOptionArray();
        return $templateValues;
    }

    /**
     * Get email templates options
     *
     * @return array
     */
    protected function getTemplateOptions()
    {
        $templateOptions = [];
        $product = $this->locator->getProduct();
        $templates = $product->getData(TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES);
        if (is_array($templates)) {
            foreach ($templates as $data) {
                $templateOptions[] = [
                    'template' => $data['template'],
                    'image' => $data['image']
                ];
            }
        }
        return $templateOptions;
    }

    /**
     * Get template images data
     *
     * @return array
     */
    protected function getTemplateImagesData() {
        $templateImagesData = [];
        $templatesData = $this->getTemplateOptions();
        $imageData = [];
        foreach ($templatesData as $data) {
            $imageData['image'] = $data['image'];
            $imageData['image_url'] = $this->mediaConfig->getTmpMediaUrl($data['image']);
            $imageData['image_name'] = 'image';
            $templateImagesData[] = $imageData;
        }
        return $templateImagesData;
    }

    /**
     * @return int
     */
    protected function getFileMaxSize()
    {
        return $this->fileSize->getMaxFileSize();
    }
}
