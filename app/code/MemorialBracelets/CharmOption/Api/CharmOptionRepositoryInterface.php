<?php
namespace MemorialBracelets\CharmOption\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface CharmOptionRepositoryInterface
{
    /** @return CharmOptionInterface */
    public function save(CharmOptionInterface $page);

    /** @return CharmOptionInterface */
    public function getById($id);

    /** @return SearchResultsInterface */
    public function getList(SearchCriteriaInterface $criteria);

    /** @return boolean */
    public function delete(CharmOptionInterface $page);

    /** @return boolean */
    public function deleteById($id);
}
