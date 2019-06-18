<?php

namespace MemorialBracelets\SwatchOption\Helper;

use Magento\Framework\File\Mime;

class MediaElementArray
{
    /** @var ImageStorageConfiguration  */
    protected $imageStorageConfiguration;

    /** @var Mime  */
    protected $mimeType;

    public function __construct(
        ImageStorageConfiguration $imageStorageConfiguration,
        Mime $mimeType
    ) {
        $this->imageStorageConfiguration = $imageStorageConfiguration;
        $this->mimeType = $mimeType;
    }

    public function getArray($imagePath)
    {
        $path = implode('/', [
            $this->imageStorageConfiguration->getMediaPath(),
            $this->imageStorageConfiguration->prepareFile($imagePath)
        ]);

        return [0 => [
            'name' => basename($imagePath),
            'type' => $this->mimeType->getMimeType($path),
            'error' => 0,
            'size' => filesize($path),
            'file' => $imagePath,
            'url' => $this->imageStorageConfiguration->getMediaUrl($imagePath),
        ]];
    }
}
