<?php
namespace Aheadworks\Giftcard\Model;

class Cron
{
    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory
     */
    protected $_giftCardCollectionFactory;

    /**
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory $giftCardCollectionFactory
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory $giftCardCollectionFactory
    ) {
        $this->_giftCardCollectionFactory = $giftCardCollectionFactory;
    }

    public function run()
    {
        $this->_giftCardCollectionFactory->create()
            ->addFieldToFilter('state', ['neq' => \Aheadworks\Giftcard\Model\Source\Giftcard\Status::EXPIRED_VALUE])
            ->walk('save')
        ;
    }
}
