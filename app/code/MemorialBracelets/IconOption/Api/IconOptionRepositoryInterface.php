<?php

namespace MemorialBracelets\IconOption\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface IconOptionRepositoryInterface
{
    /**
     * @param IconOptionInterface $icon
     * @throws CouldNotSaveException
     * @return IconOptionInterface
     */
    public function save(IconOptionInterface $icon);

    /**
     * @param int $id
     * @throws NoSuchEntityException
     * @return IconOptionInterface
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param IconOptionInterface $icon
     * @throws CouldNotDeleteException
     * @return bool
     */
    public function delete(IconOptionInterface $icon);

    /**
     * @param int $id
     * @throws CouldNotDeleteException
     * @return bool
     */
    public function deleteById($id);
}
