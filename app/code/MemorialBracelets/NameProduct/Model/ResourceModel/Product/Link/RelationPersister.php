<?php

namespace MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;

use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link as NameLink;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ProductLink\LinkFactory;

class RelationPersister
{
    private $relationProcessor;
    private $linkFactory;

    public function __construct(Relation $relationProcessor, LinkFactory $linkFactory)
    {
        $this->relationProcessor = $relationProcessor;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Save the associated products to the product relation table
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @param int $parentId
     * @param array $data
     * @param int $typeId
     * @return Link
     */
    public function aroundSaveProductLinks(Link $subject, \Closure $proceed, $parentId, $data, $typeId)
    {
        $result = $proceed($parentId, $data, $typeId);
        if ($typeId == NameLink::LINK_TYPE_NAME) {
            foreach ($data as $linkData) {
                $this->relationProcessor->addRelation(
                    $parentId,
                    $linkData['product_id']
                );
            }
        }
        return $result;
    }

    /**
     * Remove the associated products from product relation table
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @param int $linkId
     * @return Link
     */
    public function aroundDeleteProductLink(Link $subject, \Closure $proceed, $linkId)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Link $link */
        $link = $this->linkFactory->create();
        $subject->load($link, $linkId, $subject->getIdFieldName());
        $result = $proceed($linkId);
        if ($link->getLinkTypeId() == NameLink::LINK_TYPE_NAME) {
            $this->relationProcessor->removeRelations(
                $link->getProductId(),
                $link->getLinkedProductId()
            );
        }
        return $result;
    }
}
