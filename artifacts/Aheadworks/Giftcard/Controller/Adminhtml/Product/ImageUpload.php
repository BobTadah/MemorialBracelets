<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageUpload extends \Aheadworks\Giftcard\Controller\Adminhtml\Product
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param RawFactory $resultRawFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        RawFactory $resultRawFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    ) {
        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
        $this->resultRawFactory = $resultRawFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);;
            $result = $uploader->save($mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath()));

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->mediaConfig->getTmpMediaUrl($result['file']);

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
