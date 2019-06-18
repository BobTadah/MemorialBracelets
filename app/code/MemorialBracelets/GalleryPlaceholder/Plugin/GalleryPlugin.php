<?php

namespace MemorialBracelets\GalleryPlaceholder\Plugin;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Action\Context;

/**
 * Class GalleryPlugin
 * @package MemorialBracelets\GalleryPlaceholder\Plugin
 */
class GalleryPlugin
{
    /** @var Image $imageHelper */
    protected $imageHelper;

    /**
     * GalleryPlugin constructor.
     * @param Context $context
     * @param Image   $imageHelper
     */
    public function __construct(
        Context $context,
        Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param Gallery $subject
     * @param         $result
     * @return string
     */
    public function afterGetGalleryImagesJson(Gallery $subject, $result)
    {
        $product     = $subject->getProduct();
        $imagesItems = [];
        foreach ($subject->getGalleryImages() as $image) { // cycle product images
            $imagesItems[] = [
                'thumb'    => $image->getData('small_image_url'),
                'img'      => $image->getData('medium_image_url'),
                'full'     => $image->getData('large_image_url'),
                'caption'  => $image->getLabel(),
                'position' => $image->getPosition(),
                'isMain'   => $subject->isMainImage($image),
            ];
        }
        if (empty($imagesItems)) { // product does not have any images
            $configPlaceholder = $this->imageHelper->init($product, 'product_base_image')->getUrl();
            if ($configPlaceholder) { // this will grab the admin config set placeholder image
                $imagesItems[] = [
                    'thumb'    => $configPlaceholder,
                    'img'      => $configPlaceholder,
                    'full'     => $configPlaceholder,
                    'caption'  => '',
                    'position' => '0',
                    'isMain'   => true,
                ];
            } else { // base default if no placeholder config is set
                $imagesItems[] = [
                    'thumb'    => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                    'img'      => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                    'full'     => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                    'caption'  => '',
                    'position' => '0',
                    'isMain'   => true,
                ];
            }
        }

        return json_encode($imagesItems);
    }
}
