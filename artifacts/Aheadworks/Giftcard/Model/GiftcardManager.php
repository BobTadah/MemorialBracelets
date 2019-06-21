<?php
namespace Aheadworks\Giftcard\Model;

use Aheadworks\Giftcard\Model\Giftcard;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;
use Aheadworks\Giftcard\Model\Source\Giftcard\Status;
use Aheadworks\Giftcard\Model\Source\Giftcard\EmailTemplate;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class GiftcardManager
 * @package Aheadworks\Giftcard\Model
 */
class GiftcardManager
{
    /**
     * @var GiftcardFactory
     */
    protected $_giftCardFactory;

    /**
     * @var Giftcard\QuoteFactory
     */
    protected $_giftCardQuoteFactory;

    /**
     * @var Giftcard\InvoiceFactory
     */
    protected $_giftCardInvoiceFactory;

    /**
     * @var Giftcard\CreditmemoFactory
     */
    protected $_giftCardCreditMemoFactory;

    /**
     * @var Status
     */
    protected $_statusSource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param GiftcardFactory $giftCardFactory
     * @param Giftcard\QuoteFactory $giftCardQuoteFactory
     * @param Giftcard\InvoiceFactory $giftCardInvoiceFactory
     * @param Giftcard\CreditmemoFactory $giftCardCreditMemoFactory
     * @param Status $statusSource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        GiftcardFactory $giftCardFactory,
        Giftcard\QuoteFactory $giftCardQuoteFactory,
        Giftcard\InvoiceFactory $giftCardInvoiceFactory,
        Giftcard\CreditmemoFactory $giftCardCreditMemoFactory,
        Status $statusSource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_giftCardFactory = $giftCardFactory;
        $this->_giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->_giftCardInvoiceFactory = $giftCardInvoiceFactory;
        $this->_giftCardCreditMemoFactory = $giftCardCreditMemoFactory;
        $this->_statusSource = $statusSource;
        $this->_productRepository = $productRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeDate = $localeDate;
        $this->_escaper = $escaper;
    }

    /**
     * Create new Gift Card
     *
     * @param int $type
     * @param float $balance
     * @param int $expiredAfter
     * @param int $websiteId
     * @param array $senderData
     * @param array $recipientData
     * @param string $emailTemplate
     * @param int $productId
     * @param \Magento\Sales\Model\Order|bool $order
     * @return Giftcard
     */
    public function create(
        $type,
        $balance,
        $expiredAfter,
        $websiteId,
        $senderData,
        $recipientData,
        $emailTemplate = EmailTemplate::DO_NOT_SEND_VALUE,
        $productId = 0,
        $order = false
    ) {
        /** @var $giftCardModel Giftcard */
        $giftCardModel = $this->_giftCardFactory->create();
        $giftCardModel
            ->setType($type)
            ->setInitialBalance($balance)
            ->setActive(true)
            ->setSenderData($senderData)
            ->setRecipientData($recipientData)
            ->setEmailTemplate($emailTemplate)
            ->setWebsiteId($websiteId)
            ->setProductId($productId)
        ;
        if ($order) {
            $giftCardModel->setOrder($order);
            $giftCardModel->setOrderId($order->getId());
        }
        $this->setExpiredAt($giftCardModel, $expiredAfter);
        $giftCardModel->save();
        return $giftCardModel;
    }

    /**
     * Update balance of Gift Card
     *
     * @param $giftCard
     * @param $balance
     * @param $storeId
     * @param bool $delta
     * @return bool
     */
    public function updateBalance($giftCard, $balance, $storeId, $delta = false)
    {
        $giftCardModel = is_numeric($giftCard) ?
            $this->_giftCardFactory->create()->load($giftCard) :
            $giftCard
        ;
        if ($giftCardModel->getId()) {
            if ($giftCardModel->isUsed() && $giftCardModel->getProductId()) {
                /** @var $product \Magento\Catalog\Model\Product */
                $product = $this->_productRepository->getById($giftCardModel->getProductId());
                $options = $product->getTypeInstance()->getGiftCardProductOptions($product, $storeId);
                $this->setExpiredAt($giftCard, $options[TypeGiftCard::ATTRIBUTE_CODE_EXPIRE]);
            }
            $giftCardModel
                ->setBalance($delta ? $giftCardModel->getBalance() + $balance : $balance)
                ->save()
            ;
        }
        return true;
    }

    /**
     * @param $giftCard
     * @return bool
     * @throws LocalizedException
     */
    public function activate($giftCard)
    {
        /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCardModel */
        $giftCardModel = is_numeric($giftCard) ?
            $this->_giftCardFactory->create()->load($giftCard) :
            $giftCard
        ;
        if ($giftCardModel->getId()) {
            if ($giftCardModel->getState() == Status::DEACTIVATED_VALUE && $giftCardModel->getBalance() != 0) {
                $giftCardModel
                    ->setState(Status::AVAILABLE_VALUE)
                    ->save()
                ;
            } else {
                throw new LocalizedException(__('Unable to activate gift card code "%1".', $giftCardModel->getCode()));
            }
        } else {
            throw new LocalizedException(__('Gift card code doesn\'t exist.'));
        }
        return true;
    }

    /**
     * @param $giftCard
     * @return bool
     * @throws LocalizedException
     */
    public function deactivate($giftCard)
    {
        /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCardModel */
        $giftCardModel = is_numeric($giftCard) ?
            $this->_giftCardFactory->create()->load($giftCard) :
            $giftCard
        ;
        if ($giftCardModel->getId()) {
            if ($giftCardModel->getState() == Status::AVAILABLE_VALUE) {
                $giftCardModel
                    ->setState(Status::DEACTIVATED_VALUE)
                    ->save()
                ;
            } else {
                throw new LocalizedException(__('Unable to deactivate gift card code "%1".', $giftCardModel->getCode()));
            }
        } else {
            throw new LocalizedException(__('Gift card code doesn\'t exist.'));
        }
        return true;
    }

    /**
     * Refund of Gift Card
     *
     * @param array $codes
     * @param int $qty
     * @param float $basePrice
     * @param \Magento\Sales\Model\Order\Creditmemo|bool $creditmemo
     * @throws LocalizedException
     * @return bool
     */
    public function refund($codes, $qty, $basePrice, $creditmemo = false)
    {
        if (is_array($codes) && $qty > 0) {
            $errors = [];
            foreach ($codes as $code) {
                /** @var $giftCardModel Giftcard */
                $giftCardModel = $this->_giftCardFactory->create()->loadByCode($code);
                if (!$giftCardModel->getId()) {
                    $errors[] = __(
                        'Unable to refund card "%1" due to it\'s removal',
                        $this->_escaper->escapeHtml($code)
                    );
                    continue;
                }
                if ($giftCardModel->getState() == Status::DEACTIVATED_VALUE) {
                    continue;
                }
                if ($giftCardModel->getState() == Status::AVAILABLE_VALUE && $giftCardModel->getBalance() < $basePrice) {
                    $errors[] = __(
                        'Unable to refund card "%1" due to it is already used',
                        $this->_escaper->escapeHtml($code)
                    );
                    continue;
                }
                if ($giftCardModel->getState() != Status::AVAILABLE_VALUE) {
                    $errors[] = __(
                        'Unable to refund card "%1" due to it is already %2',
                        $this->_escaper->escapeHtml($code),
                        $giftCardModel->getState() == Status::USED_VALUE ? "out of balance" : $this->_statusSource->getOptionByValue($giftCardModel->getState())
                    );
                    continue;
                }
                $giftCardModel
                    ->setState(Status::DEACTIVATED_VALUE)
                    ->setBalance(0)
                ;
                if ($creditmemo) {
                    $giftCardModel->setCreditmemo($creditmemo);
                }
                $giftCardModel->save();
            }
            if (count($errors) > 0) {
                // todo: log errors
            }
        }
        return true;
    }

    /**
     * Set expiration date of Gift Card
     *
     * @param Giftcard|int $giftCard
     * @param int $expiredAfter
     * @return bool
     */
    public function setExpiredAt($giftCard, $expiredAfter)
    {
        $giftCardModel = is_numeric($giftCard) ?
            $this->_giftCardFactory->create()->load($giftCard) :
            $giftCard
        ;
        if ($expiredAfter > 0) {
            $giftCardModel->setExpireAt(
                $this->_localeDate->date(null, null, false)
                    ->add(new \DateInterval('P' . $expiredAfter . 'D'))
                    ->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT)
            );
        }
        return true;
    }

    /**
     * Add Gift Card to quote
     *
     * @param string|\Aheadworks\Giftcard\Model\Giftcard|int $giftCard
     * @return \Aheadworks\Giftcard\Model\Data\AddToQuoteResult
     * @throws LocalizedException
     */
    public function addToQuote($giftCard)
    {
        $giftCardModel = $giftCard;
        if (is_numeric($giftCard)) {
            $giftCardModel = $this->_giftCardFactory->create()->load($giftCard);
        } elseif (is_string($giftCard)) {
            $giftCardModel = $this->_giftCardFactory->create()->loadByCode(trim($giftCard));
        }
        try {
            if ($giftCardModel->getId()) {
                $giftCardModel->validate();
                /** @var $giftCardQuoteModel Giftcard\Quote */
                $giftCardQuoteModel = $this->_giftCardQuoteFactory->create();
                if (!$giftCardQuoteModel->exists($giftCardModel->getId(), $this->_getQuote()->getId())) {
                    $giftCardQuoteModel
                        ->setData([
                            'quote_id' => $this->_getQuote()->getId(),
                            'giftcard_id' => $giftCardModel->getId()
                        ])
                        ->save()
                    ;
                } else {
                    throw new LocalizedException(__('This gift card is already in the quote.'));
                }
            } else {
                throw new LocalizedException(__('Gift Card Code is not valid.'));
            }
        } catch (LocalizedException $e) {
            return new \Aheadworks\Giftcard\Model\Data\AddToQuoteResult(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
        $this->_getQuote()
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save()
        ;
        return new \Aheadworks\Giftcard\Model\Data\AddToQuoteResult(
            [
                'success' => true,
                'message' => "Gift Card Code \"{$this->_escaper->escapeHtml($giftCardModel->getCode())}\" has been applied."
            ]
        );
    }

    /**
     * @param int|Giftcard\Quote $giftCardQuote
     * @return bool
     */
    public function removeFromQuote($giftCardQuote, $giftCardCode = null)
    {
        $giftCardQuoteModel = is_numeric($giftCardQuote) ?
            $this->_giftCardQuoteFactory->create()->load($giftCardQuote) :
            $giftCardQuote
        ;
        if ($giftCardQuoteModel->getId()) {
            $giftCardQuoteModel->delete();
            $this->_getQuote()
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save()
            ;
        }
        return true;
    }

    /**
     * @param int $sourceQuoteId
     * @param int $destQuoteId
     * @return bool
     */
    public function replaceToQuote($sourceQuoteId, $destQuoteId)
    {
        $quoteGiftCards = $this->_giftCardQuoteFactory->create()->getCollection()
            ->addFieldToFilter('quote_id', ['eq' => $sourceQuoteId])
        ;
        foreach ($quoteGiftCards as $quoteGiftCard) {
            /** @var $quoteGiftCard Giftcard\Quote */
            $quoteGiftCard
                ->setQuoteId($destQuoteId)
                ->save()
            ;
        }
        return true;
    }

    /**
     * @param Invoice $invoice
     * @param \Magento\Framework\DataObject $invoiceGiftCard
     * @return bool
     */
    public function addToInvoice(Invoice $invoice, \Magento\Framework\DataObject $invoiceGiftCard)
    {
        $this->_giftCardInvoiceFactory->create()
            ->setGiftcardId($invoiceGiftCard->getId())
            ->setInvoiceId($invoice->getId())
            ->setOrderId($invoice->getOrderId())
            ->setBaseGiftcardAmount($invoiceGiftCard->getBaseGiftcardAmount())
            ->setGiftcardAmount($invoiceGiftCard->getGiftcardAmount())
            ->save()
        ;
        return true;
    }

    /**
     * @param Creditmemo $creditmemo
     * @param \Magento\Framework\DataObject $creditmemoGiftCard
     * @return bool
     */
    public function addToCreditmemo(Creditmemo $creditmemo, \Magento\Framework\DataObject $creditmemoGiftCard)
    {
        $this->_giftCardCreditMemoFactory->create()
            ->setGiftcardId($creditmemoGiftCard->getId())
            ->setCreditmemoId($creditmemo->getId())
            ->setOrderId($creditmemo->getOrderId())
            ->setBaseGiftcardAmount($creditmemoGiftCard->getBaseGiftcardAmount())
            ->setGiftcardAmount($creditmemoGiftCard->getGiftcardAmount())
            ->save()
        ;
        return true;
    }

    protected function _getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}