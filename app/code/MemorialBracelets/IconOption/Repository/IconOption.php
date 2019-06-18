<?php

namespace MemorialBracelets\IconOption\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use MemorialBracelets\IconOption\Api\IconOptionInterface;
use MemorialBracelets\IconOption\Api\IconOptionRepositoryInterface;
use MemorialBracelets\IconOption\Model\IconOption as Model;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption as ResourceModel;
use MemorialBracelets\IconOption\Model\IconOptionFactory;
use MemorialBracelets\IconOption\Model\ResourceModel\IconOption\CollectionFactory;

/**
 * Class IconOption
 * @package MemorialBracelets\IconOption\Repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IconOption implements IconOptionRepositoryInterface
{
    /** @var IconOptionFactory */
    protected $factory;

    /** @var SearchResultsInterfaceFactory */
    protected $resultFactory;

    /** @var CollectionFactory */
    protected $collectionFactory;

    public function __construct(
        IconOptionFactory $factory,
        SearchResultsInterfaceFactory $resultFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->factory = $factory;
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /** {@inheritdoc} */
    public function save(IconOptionInterface $icon)
    {
        /** @var Model $dbOption */
        $data = [
            // FIELD_ID Intentionally excluded.  Instead we use ->setId(->getId())
            ResourceModel::FIELD_TITLE      => $icon->getTitle(),
            ResourceModel::FIELD_ICON       => $icon->getIcon(),
            ResourceModel::FIELD_IS_ACTIVE  => $icon->getIsActive(),
            ResourceModel::FIELD_POSITION   => $icon->getPosition(),
            ResourceModel::FIELD_PRICE      => $icon->getPrice(),
            ResourceModel::FIELD_PRICE_TYPE => $icon->getPriceType(),
            ResourceModel::FIELD_CREATION_TIME => $icon->getCreationTime(),
            // FIELD_UPDATE_TIME Intentionally Excluded (so it auto updates)
        ];

        $dbOption = $this->factory->create();
        $dbOption->setData($data);
        $dbOption->setId($icon->getId());
        try {
            $dbOption->getResource()->save($dbOption);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }

        return $dbOption;
    }

    public function getById($id)
    {
        /** @var Model $dbOption */
        $dbOption = $this->factory->create();
        $dbOption->getResource()->load($dbOption, $id);

        return $dbOption;
    }

    public function delete(IconOptionInterface $icon)
    {
        return $this->deleteById($icon->getId());
    }

    public function deleteById($id)
    {
        /** @var Model $dbOption */
        $dbOption = $this->factory->create();
        $dbOption->setId($id);

        try {
            $dbOption->getResource()->delete($dbOption);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()), $e);
        }
    }

    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->resultFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \MemorialBracelets\IconOption\Model\ResourceModel\IconOption\Collection $collection */
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $dir = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'ASC' : 'DESC';
                $collection->addOrder($sortOrder->getField(), $dir);
            }
        }

        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $objects = [];
        foreach ($collection as $model) {
            $objects[] = $model;
        }
        $searchResults->setItems($objects);

        return $searchResults;
    }
}
