<?php

namespace MemorialBracelets\IconOption\Plugin;

class WishListIconValidationFix
{
    public function aftervalidateUserValue(\Magento\Catalog\Model\Product\Option\Type\DefaultType $subject, $result)
    {
        //Remove iconpicker validation when adding to wishlist.
        if ($result->getOption()->getType() == 'iconpicker') {
            $result->setIsValid(true);
        }

        return $result;
    }
}
