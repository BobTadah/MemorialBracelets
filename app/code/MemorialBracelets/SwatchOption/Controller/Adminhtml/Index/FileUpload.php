<?php

namespace MemorialBracelets\SwatchOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\SwatchOption\Helper\ImageStorageConfiguration;

class FileUpload extends Action
{
    /** @var UploaderFactory  */
    protected $uploaderFactory;

    /** @var AdapterFactory  */
    protected $imageAdapterFactory;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var ImageStorageConfiguration  */
    protected $imageStorageConfiguration;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageAdapterFactory,
        JsonFactory $jsonFactory,
        ImageStorageConfiguration $imageStorageConfiguration
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->jsonFactory = $jsonFactory;
        $this->imageStorageConfiguration = $imageStorageConfiguration;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => $this->determineFileId()]);
            $uploader->setAllowedExtensions(['jpg','jpeg','gif','png']);

            $imageAdapter = $this->imageAdapterFactory->create();
            $uploader->addValidateCallback('charm_opt_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $path = $this->imageStorageConfiguration->getMediaPath();
            $result = $uploader->save($path);

            $this->_eventManager->dispatch(
                'swatch_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->imageStorageConfiguration->getMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $resultJson->setData($result);
    }

    protected function determineFileId()
    {
        $optionContainer = $_FILES['product']['name']['options'];
        $optionKey = array_keys($optionContainer)[0];
        $swatchContainer = $_FILES['product']['name']['options'][$optionKey]['swatches'];
        $swatchKey = array_keys($swatchContainer)[0];

        $file = [];
        foreach ($_FILES['product'] as $attributeName => $attributeValue) {
            $file[$attributeName] = $attributeValue['options'][$optionKey]['swatches'][$swatchKey]['image'];
        }

        return $file;
    }
}
