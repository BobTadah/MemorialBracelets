<?php

namespace MemorialBracelets\ReviewAdditions\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory as ReviewCollectionFactory;

/**
 * Class Reflections
 * @package MemorialBracelets\ReviewAdditions\Block
 */
class Reflections extends Template implements IdentityInterface
{
    const CACHE_TAG = 'reflections_list';

    /** @var ReviewCollectionFactory $collectionFactory */
    protected $collectionFactory;

    protected $collection = null;

    /**
     * Reflections constructor.
     * @param Template\Context           $context
     * @param ReviewCollectionFactory    $collectionFactory
     * @param array                      $data
     */
    public function __construct(
        Template\Context $context,
        ReviewCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory     = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * this will return the reflections for only name products
     * @return array
     */
    public function getReflections()
    {
        $collection = $this->getCollection();

        $data = $this->formatReflections($collection);

        return $data;
    }

    /**
     * Get the collection of approved reflections
     * @return Collection
     */
    public function getCollection()
    {
        if (is_null($this->collection)) {
            /** @var \Magento\Review\Model\ResourceModel\Review\Product\Collection $collection */
            $this->collection = $this->collectionFactory->create();
            $this->collection->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addStatusFilter(Review::STATUS_APPROVED)
                ->setDateOrder()// DESC by default
                ->load();
        }
        return $this->collection;
    }


    /**
     * this will build out and format the reflection data array. This will make sure to
     * only include the name product reviews/reflections. This will return the reflections
     * in a associative array with all with the years acting as the key.
     * @param Collection $collection
     * @return array
     */
    protected function formatReflections(Collection $collection)
    {
        $reviews = [];

        if ($collection->getSize()) { // only if there are reviews
            foreach ($collection as $item) { // parse review items
                if ($item->getTypeId() == 'name') { // only include name products
                    /** @var \Magento\Catalog\Model\Product $item */
                    $tempYear            = explode('-', $item->getReviewCreatedAt());
                    $tempKey             = $tempYear[0];
                    $reviews[$tempKey][] = [
                        'review_title'   => $item->getTitle(),
                        'review_name'    => $item->getNickname(),
                        'review_summary' => $item->getDetail(),
                        'review_date'    => date("F j, Y", strtotime($item->getReviewCreatedAt())),
                        'product_id'     => $item->getEntityId(),
                        'product_name'   => $item->getName(),
                        'product_url'    => $item->getProductUrl()
                    ];
                }
            }
        }

        return $reviews;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG];
    }
}
