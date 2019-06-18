<?php

namespace MemorialBracelets\CharmOption\Ui\DataProvider\CharmOption\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\File\Mime;
use Magento\Framework\File\Size;
use MemorialBracelets\CharmOption\Model\CharmOption\IconStorageConfiguration;

class IconData extends AbstractModifier
{
    /** @var IconStorageConfiguration  */
    protected $iconStorageConfiguration;

    /** @var Mime  */
    protected $mimeType;

    public function __construct(
        IconStorageConfiguration $iconStorageConfiguration,
        Mime $mimeType
    ) {
        $this->iconStorageConfiguration = $iconStorageConfiguration;
        $this->mimeType = $mimeType;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        foreach ($data as &$form) {
            $item = &$form['general'];
            if (strlen($item['icon']) < 1) {
                $item['icon'] = null;
                continue;
            }

            $path = implode('/', [
                $this->iconStorageConfiguration->getMediaPath(),
                $this->iconStorageConfiguration->prepareFile($item['icon'])
            ]);

            $item['icon'] = [
                0 => [
                    'name' => basename($item['icon']),
                    'type' => $this->mimeType->getMimeType($path),
                    'error' => '0',
                    'size' => filesize($path),
                    'file' => $item['icon'],
                    'url' => $this->iconStorageConfiguration->getMediaUrl($item['icon']),
                ]
            ];
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
