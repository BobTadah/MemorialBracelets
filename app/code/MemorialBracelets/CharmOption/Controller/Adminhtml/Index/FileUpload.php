<?php

namespace MemorialBracelets\CharmOption\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MemorialBracelets\CharmOption\Model\CharmOption\IconStorageConfiguration;

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

    /** @var IconStorageConfiguration  */
    protected $iconStorageConfig;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageAdapterFactory,
        JsonFactory $jsonFactory,
        IconStorageConfiguration $iconStorageConfiguration
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->jsonFactory = $jsonFactory;
        $this->iconStorageConfig = $iconStorageConfiguration;
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
            $uploader = $this->uploaderFactory->create(['fileId' => 'general[icon]']);
            $uploader->setAllowedExtensions(['jpg','jpeg','gif','png']);

            $imageAdapter = $this->imageAdapterFactory->create();
            $uploader->addValidateCallback('charm_opt_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $path = $this->iconStorageConfig->getMediaPath();
            $result = $uploader->save($path);

            $this->_eventManager->dispatch(
                'option_charm_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->iconStorageConfig->getMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $resultJson->setData($result);
    }
}
