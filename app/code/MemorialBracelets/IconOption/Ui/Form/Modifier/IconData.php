<?php

namespace MemorialBracelets\IconOption\Ui\Form\Modifier;

use Magento\Framework\File\Mime;
use Magento\Framework\File\Size;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use MemorialBracelets\IconOption\Helper\IconStorageConfiguration;

class IconData implements ModifierInterface
{
    /** @var IconStorageConfiguration  */
    protected $config;

    /** @var Mime  */
    protected $mimeType;

    public function __construct(
        IconStorageConfiguration $config,
        Mime $mimeType
    ) {
        $this->config = $config;
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
                $this->config->getMediaPath(),
                $this->config->prepareFile($item['icon'])
            ]);

            $item['icon'] = [
                0 => [
                    'name' => basename($item['icon']),
                    'type' => $this->mimeType->getMimeType($path),
                    'error' => '0',
                    'size' => filesize($path),
                    'file' => $item['icon'],
                    'url' => $this->config->getMediaUrl($item['icon']),
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
