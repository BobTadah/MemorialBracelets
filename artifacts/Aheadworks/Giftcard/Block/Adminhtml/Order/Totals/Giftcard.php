<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Order\Totals;

use Aheadworks\Giftcard\Model\ResourceModel\Giftcard as ResourceGiftCard;

/**
 * Class Giftcard
 * @package Aheadworks\Giftcard\Block\Adminhtml\Order\Totals
 */
class Giftcard extends \Magento\Sales\Block\Adminhtml\Order\Totals\Item
{
    /**
     * @var ResourceGiftCard\CollectionFactory
     */
    protected $giftCardCollectionFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $giftCardFactory;

    /**
     * @var null|array
     */
    protected $giftCardItems = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param ResourceGiftCard\CollectionFactory $giftCardCollectionFactory
     * @param \Aheadworks\Giftcard\Model\GiftcardFactory $giftCardFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        ResourceGiftCard\CollectionFactory $giftCardCollectionFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftCardFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->giftCardCollectionFactory = $giftCardCollectionFactory;
        $this->giftCardFactory = $giftCardFactory;
    }

    /**
     * @return array|null
     */
    public function getGiftCardItems()
    {
        if ($this->giftCardItems === null) {
            $source = $this->getSource();
            if (
                $source instanceof \Magento\Sales\Model\Order &&
                null === $source->getAwGiftCards()
            ) {
                $giftCardItems = $this->giftCardCollectionFactory->create()
                    ->addFieldToFilter('quote_id', $this->getSource()->getQuoteId())
                    ->getItems();
                if (count($giftCardItems) > 0) {
                    $source->setAwGiftCards($giftCardItems);
                }
            }

            $this->giftCardItems = [];
            if ($source->getAwGiftCards() && count($source->getAwGiftCards()) > 0) {
                foreach ($source->getAwGiftCards() as $card) {
                    $this->giftCardItems[] = $this->giftCardFactory->create()
                        ->load($card->getGiftcardId())
                        ->addData($card->getData())
                    ;
                }
            }

        }
        return $this->giftCardItems;
    }
}
