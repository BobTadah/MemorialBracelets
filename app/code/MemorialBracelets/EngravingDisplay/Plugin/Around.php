<?php

namespace MemorialBracelets\EngravingDisplay\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Option;
use MemorialBracelets\EngravingDisplay\Model\Product\Option\Type\Engravable;
use Magento\Catalog\Model\Product\Option\Type\Factory as TypeFactory;

class Around
{
    /** @var TypeFactory  */
    protected $optionTypeFactory;

    public function __construct(TypeFactory $optionTypeFactory)
    {
        $this->optionTypeFactory = $optionTypeFactory;
    }

    public function aroundGetGroupByType($option, callable $proceed, $type = null)
    {
        $result = $proceed($type);
        if ($result == '' && $type == 'engraving') {
            return 'engravable';
        }
        return $result;
    }

    public function aroundBeforeSave($option, callable $proceed)
    {

        $result = $proceed();
        $previousType = $option->getData('previous_type');
        if ($option->getGroupByType($previousType) == 'engravable') {
            parent::beforeSave();
            if ($option->getData('previous_type') != '') {
                $previousType = $option->getData('previous_type');

                /**
                 * if previous option has different group from one is came now
                 * need to remove all data of previous group
                 */
                if ($option->getGroupByType($previousType) != $option->getGroupByType($option->getData('type'))) {
                    switch ($option->getGroupByType($previousType)) {
                        case self::OPTION_GROUP_SELECT:
                            $option->unsetData('values');
                            if ($option->getId()) {
                                $option->getValueInstance()->deleteValue($option->getId());
                            }
                            break;
                        case self::OPTION_GROUP_FILE:
                            $option->setData('file_extension', '');
                            $option->setData('image_size_x', '0');
                            $option->setData('image_size_y', '0');
                            break;
                        case self::OPTION_GROUP_TEXT:
                            $option->setData('max_characters', '0');
                            break;
                        case 'engravable':
                            $option->setData('number_lines', '0');
                            $option->setData('supportive_message_price', '0.0000');
                            $option->setData('name_engraving_price', '0.0000');
                            $option->setData('max_characters', '0');
                            break;
                        case self::OPTION_GROUP_DATE:
                            break;
                    }
                    if ($option->getGroupByType($option->getData('type')) == self::OPTION_GROUP_SELECT) {
                        $option->setData('sku', '');
                        $option->unsetData('price');
                        $option->unsetData('price_type');
                        if ($option->getId()) {
                            $option->deletePrices($option->getId());
                        }
                    }
                }
            }
            return $option;
        }
        return $result;
    }

    public function aroundGroupFactory(Option $subject, callable $proceed, $type)
    {
        $group = $subject->getGroupByType($type);

        // It will have thrown an exception for the charm group, do something else when that happens
        if ($group == 'engravable') {
            return $this->optionTypeFactory->create(Engravable::class);
        }
        return $proceed($type);
    }
}
