<?php

namespace MemorialBracelets\SwatchOption\Model;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Factory as TypeFactory;

/**
 * Class OptionInterceptor
 * @package MemorialBracelets\SizeOption\Model
 */
class OptionInterceptor
{
    /** @var  array */
    protected $optionValues;

    /** @var TypeFactory */
    protected $optionTypeFactory;

    /**
     * OptionInterceptor constructor.
     * @param TypeFactory $optionTypeFactory
     */
    public function __construct(TypeFactory $optionTypeFactory)
    {
        $this->optionTypeFactory = $optionTypeFactory;
    }

    public function aroundGetGroupByType(Option $subject, callable $proceed, $type = null)
    {
        $result = $proceed($type);
        if (is_null($type)) {
            $type = $subject->getType();
        }
        if ($result == '' && in_array($type, OptionType::subTypes())) {
            return 'swatch';
        }
        return $result;
    }

    /**
     * @param Option $subject
     * @param        $result
     * @return bool
     */
    public function afterHasValues(Option $subject, $result)
    {
        if (!$result && $subject->getGroupByType() == 'swatch') {
            return true;
        }
        return $result;
    }

    /**
     * @param Option   $subject
     * @param callable $proceed
     * @param          $type
     * @return Option\Type\DefaultType
     */
    public function aroundGroupFactory(Option $subject, callable $proceed, $type)
    {
        /*
         * Previously we attempted to try() catch(ReflectionException) this but on some installations it was returning
         * a PHP fatal error about not finding the expectedly wrong class.  I'm not sure why this is, but we have to
         * do it this way to be safe.
         */
        $group = $subject->getGroupByType($type);
        if ($group == 'swatch') {
            return $this->optionTypeFactory->create(OptionType::class);
        }
        return $proceed($type);
    }
}
