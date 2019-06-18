<?php

namespace MemorialBracelets\NameProduct\Block\Product;

class View extends \Magento\Catalog\Block\Product\View
{
    public function getChildProducts()
    {
        $product = $this->getProduct();
        /** @var \MemorialBracelets\NameProduct\Model\Product\Type\Name $type */
        $type = $product->getTypeInstance();
        $children = $type->getConfiguredProducts($product);

        return $children;
    }

    public function getProductHtml(\Magento\Catalog\Model\Product $product)
    {
        $type = 'product_options';

        $renderer = $this->getChildBlock($type);
        $renderer->setProduct($product);
        $renderer->setNameProduct($this->getProduct());

        return $this->getChildHtml($type, false);
    }

    /**
     * This function will return an array of attribute labels and values. In order for a product attribute
     * to make into the returned array the option [Visible on Catalog Pages] on Storefront must be set to [yes].
     * @return array
     */
    public function getNameDetails()
    {
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        $data = [];

        foreach ($attributes as $attribute) { // cycle all available attributes.
            if ($attribute->getIsVisibleOnFront()) { // check if item is visible on catalog pages and storefront.
                switch ($attribute->getFrontendInput()) { // this deals with the different types of attributes.
                    case 'select': // we need to grab the select text option text value.
                        $value = $product->getAttributeText($attribute->getAttributeCode());
                        break;
                    case 'price': // age is saved as a price decimal so we need to shave off decimal places.
                        $value = number_format($product->getData($attribute->getAttributeCode()), 0);
                        break;
                    case 'date':
                        $o_date = $product->getData($attribute->getAttributeCode());
                        if ($o_date != null) {
                            $xx = strtotime($o_date);
                            $value = date('d M y', $xx);
                        } else {
                            $value = '';
                        }
                        break;
                    default: // default behavior to grab string values
                        $value = $product->getData($attribute->getAttributeCode());
                }

                array_push(
                    $data,
                    [
                        'label' => $attribute->getDefaultFrontendLabel(),
                        'value' => $value
                    ]
                );
            }
        }
        return $data;
    }

    /**
     * Get the parent product event attribute.
     *
     * @return null|string
     */
    public function getProductEvent()
    {
        return $this->getProductAttributeOptionValue($this->getProduct(), 'event');
    }

    /**
     * Get the child product producttype attribute.
     *
     * @param $childProduct
     * @return null|string
     */
    public function getChildProductType($childProduct)
    {
        return $this->getProductAttributeOptionValue($childProduct, 'producttype');
    }

    /**
     * Get the option label associated to an attribute of a product.
     *
     * @param $product
     * @param string $attribute
     * @return string|null
     */
    public function getProductAttributeOptionValue($product, $attribute)
    {
        $attributes = $product->getAttributes();
        $foundAttr = isset($attributes[$attribute]) ? $attributes[$attribute] : null;

        if (!$foundAttr) {
            return null;
        }

        //Get associated value.
        $attrCode = $product->getData($foundAttr->getAttributeCode());
        //Find attr label.
        $options = $foundAttr->getSource()->getAllOptions();
        $attributeValue = null;
        foreach ($options as $option) {
            if ((int) $option['value'] === (int) $attrCode) {
                $attributeValue = $option['label'];
                break;
            }
        }

        return $attributeValue;
    }
}
