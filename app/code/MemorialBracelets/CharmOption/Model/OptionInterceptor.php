<?php

namespace MemorialBracelets\CharmOption\Model;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Factory as TypeFactory;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use MemorialBracelets\CharmOption\Api\CharmOptionRepositoryInterface;

class OptionInterceptor
{
    /** @var CharmOptionRepositoryInterface */
    protected $repository;

    protected $searchFactory;

    protected $optionValues;

    protected $optionTypeFactory;

    public function __construct(
        CharmOptionRepositoryInterface $repo,
        SearchCriteriaInterfaceFactory $searchFactory,
        TypeFactory $optionTypeFactory
    ) {
        $this->searchFactory = $searchFactory;
        $this->repository = $repo;
        $this->optionTypeFactory = $optionTypeFactory;
    }

    public function aroundGetGroupByType(Option $subject, callable $proceed, $type = null)
    {
        $result = $proceed($type);
        if (is_null($type)) {
            $type = $subject->getType();
        }
        if ($result == '' && $type == 'picker') {
            return 'charm';
        }
        return $result;
    }

    public function afterHasValues(Option $subject, $result)
    {
        if (!$result && $subject->getGroupByType() == 'charm') {
            return true;
        }
        return $result;
    }

    public function aroundGetValues(Option $subject, callable $proceed)
    {
        if ($subject->getGroupByType() == 'charm') {
            return $this->getOptionValues();
        }
        return $proceed();
    }

    public function aroundGroupFactory(Option $subject, callable $proceed, $type)
    {
        /*
         * Previously we attempted to try() catch(ReflectionException) this but on some installations it was returning
         * a PHP fatal error about not finding the expectedly wrong class.  I'm not sure why this is, but we have to
         * do it this way to be safe.
         */
        $group = $subject->getGroupByType($type);
        if ($group == 'charm') {
            return $this->optionTypeFactory->create(OptionType::class);
        }
        return $proceed($type);
    }

    private function getOptionValues()
    {
        if (!isset($this->optionValues)) {
            $values = $this->repository->getList($this->searchFactory->create());
            $returnValues = [];
            /** @var CharmOption $item */
            foreach ($values->getItems() as $item) {
                $returnValues[$item->getId()] = $item;
            }
            $this->optionValues = $returnValues;
        }
        return $this->optionValues;
    }
}
