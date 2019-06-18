<?php

namespace MemorialBracelets\IconOption\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;

class IconStorageConfiguration
{
    const MEDIA_DIR = 'option_icon';

    protected $store;

    protected $filesystem;

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
        if ($file) {
            $base = $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result = $base.self::MEDIA_DIR.'/'.$this->prepareFile($file);
        } else {
            $result = $this->getDefaultPlaceholder();
        }
        return $result;
    }

    public function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    public function getDefaultPlaceholder()
    {
        return $this->assetRepo->getUrl('MemorialBracelets_IconOption::images/placeholder.png');
    }
}
