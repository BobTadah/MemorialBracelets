<?php
namespace MemorialBracelets\SupportiveMessages\Api;

use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SupportiveMessageRepositoryInterface
{
    public function save(SupportiveMessageInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(SupportiveMessageInterface $page);

    public function deleteById($id);
}
