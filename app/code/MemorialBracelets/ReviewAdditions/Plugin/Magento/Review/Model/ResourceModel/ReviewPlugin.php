<?php

namespace MemorialBracelets\ReviewAdditions\Plugin\Magento\Review\Model\ResourceModel;

use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Review\Model\ResourceModel\Review as ResourceModel;
use Magento\Review\Model\Review as Model;
use MemorialBracelets\ReviewAdditions\Block\Reflections;

class ReviewPlugin
{
    protected $cachePool;

    public function __construct(FrontendPool $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function aroundSave(ResourceModel $subject, callable $next, Model $object)
    {
        $result = $next($object);

        if ($object->getStatusId() == Model::STATUS_APPROVED) {
            $this->cleanCache();
        }

        return $result;
    }

    public function cleanCache()
    {
        $this->cachePool
            ->get(BlockCache::TYPE_IDENTIFIER)
            ->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Reflections::CACHE_TAG]);

        $this->cachePool
            ->get('full_page')
            ->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Reflections::CACHE_TAG]);
    }
}
