<?php

namespace MemorialBracelets\IconOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\IconOption\Helper\IconStorageConfiguration;

class FileUpload extends Action
{
    /** @var UploaderFactory */
    protected $uploaderFactory;

    /** @var AdapterFactory */
    protected $imageAdapterFactory;

    /** @var JsonFactory */
    protected $jsonFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var IconStorageConfiguration */
    protected $config;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        JsonFactory $jsonFactory,
        IconStorageConfiguration $config
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $adapterFactory;
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            /** @var Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'general[icon]']);
            $uploader->setAllowedExtensions(['jpg','jpeg','gif','png']);

            $imageAdapter = $this->imageAdapterFactory->create();
            $uploader->addValidateCallback('icon_opt_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $path = $this->config->getMediaPath();
            $result = $uploader->save($path);

            $this->_eventManager->dispatch(
                'option_icon_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->config->getMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $resultJson->setData($result);
    }
}
