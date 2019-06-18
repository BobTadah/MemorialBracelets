<?php

namespace MemorialBracelets\SwatchOption\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class ImageStorageConfiguration
{
    const MEDIA_DIR = 'option_swatch';

    protected $store;

    protected $filesystem;

    protected $assetRepo;

    public function __construct(StoreManagerInterface $storeManager, Filesystem $filesystem, Repository $assetRepo)
    {
        $this->store = $storeManager->getStore();
        $this->filesystem = $filesystem;
        $this->assetRepo = $assetRepo;
    }

    public function getMediaPath()
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath(self::MEDIA_DIR);

        return $path;
    }

    public function getMediaUrl($file)
    {
        $result = null;
        if ($file) {
            $base = $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result = $base.self::MEDIA_DIR.'/'.$this->prepareFile($file);
        }
        return $result;
    }

    public function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
