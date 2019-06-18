<?php

namespace MemorialBracelets\NameProduct\Block\Product\View\Type;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Model\Product;

class Name extends AbstractView
{
    public function getConfiguredProducts()
    {
        return $this->getProduct()->getTypeInstance()->getConfiguredProducts($this->getProduct());
    }

    /**
     * Set preconfigured values to grouped associated products
     *
     * @return $this
     */
    public function setPreconfiguredValue()
    {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getConfiguredProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }

    public function displayProductStockStatus()
    {
        return false;
    }

    public function getNameInfo()
    {
        $product = $this->getProduct();
        $instance = $product->getTypeInstance();
        if ($instance instanceof \MemorialBracelets\NameProduct\Model\Product\Type\Name) {
            return $instance->getNameInfo($product);
        } else {
            return '';
        }
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getImageJson(Product $product)
    {
        $images = $product->getMediaGalleryImages();
        $result = [];
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $small = $this->_imageHelper
                    ->init($product, 'product_page_image_small')
                    ->setImageFile($image->getFile())
                    ->getUrl();
                $medium = $this->_imageHelper
                    ->init($product, 'product_page_image_medium')
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->setImageFile($image->getFile())
                    ->getUrl();
                $large = $this->_imageHelper
                    ->init($product, 'product_page_image_large')
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->setImageFile($image->getFile())
                    ->getUrl();

                $result[] = [
                    'thumb' => $small,
                    'img' => $medium,
                    'full' => $large,
                    'caption' => $image->getLabel(),
                    'position' => $image->getPosition(),
                    'isMain' => $product->getImage() == $image->getFile(),
                ];
            }
        }
        if (empty($result)) { // product does not have any images
            $configPlaceholder = $this->_imageHelper->init($product, 'product_base_image')->getUrl();
            if ($configPlaceholder) { // this will grab the admin config set placeholder image
                $result[] = [
                    'thumb'    => $configPlaceholder,
                    'img'      => $configPlaceholder,
                    'full'     => $configPlaceholder,
                    'caption'  => '',
                    'position' => '0',
                    'isMain'   => true,
                ];
            } else { // base default if no placeholder config is set
                $result[] = [
                    'thumb'    => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                    'img'      => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                    'full'     => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                    'caption'  => '',
                    'position' => '0',
                    'isMain'   => true,
                ];
            }
        }

        return json_encode($result);
    }
}
