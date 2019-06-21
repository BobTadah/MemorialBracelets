<?php
namespace Aheadworks\Giftcard\Model;

use Aheadworks\Giftcard\Model\Source\Giftcard\Status;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class GiftcardRepository
 * @package Aheadworks\Giftcard\Model
 */
class GiftcardRepository
{
    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory
     */
    protected $_giftCardCollectionFactory;

    public function __construct(
        \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory $giftCardCollectionFactory
    ) {
        $this->_giftCardCollectionFactory = $giftCardCollectionFactory;
    }

    /**
     * Retrieves Gift Cards associated with the given customer email
     *
     * @param string $customerEmail
     * @param bool $quoteId
     * @return array
     */
    public function getCustomerGiftCards($customerEmail, $quoteId = false)
    {
        $result = [];
        /** @var \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Collection $giftCardCollection */
        $giftCardCollection = $this->_giftCardCollectionFactory->create();
        $giftCardCollection
            ->addFieldToFilter('recipient_email', ['eq' => $customerEmail])
            ->addFieldToFilter('state', ['eq' => Status::AVAILABLE_VALUE])
            ->addFieldToFilter('balance', ['gt' => 0])
        ;
        if ($quoteId) {
            $giftCardCollection->addNotInQuoteFilter($quoteId);
        }
        /** @var $giftCardModel \Aheadworks\Giftcard\Model\Giftcard */
        foreach ($giftCardCollection as $giftCardModel) {
            if ($giftCardModel->isValidForRedeem()) {
                $result[] = $giftCardModel;
            }
        }
        return $result;
    }
}