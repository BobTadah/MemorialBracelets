<?php
namespace Aheadworks\Giftcard\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;
use Aheadworks\Giftcard\Model\Source\Giftcard\EmailTemplate;
use Aheadworks\Giftcard\Model\Source\Giftcard\Status;

/**
 * Class Sender
 * @package Aheadworks\Giftcard\Model\Email
 */
class Sender
{
    /**
     * Location of the "Gift Card Notification Email Sender" config param
     */
    const XML_PATH_SENDER_IDENTITY = 'aw_giftcard/email/sender';

    /**
     * ID of default Gift Card email template
     */
    const DEFAULT_EMAIL_TEMPLATE_ID = 'aw_giftcard_email_template';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreFactory $storeFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param CurrencyInterface $localeCurrency
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param FactoryInterface $templateFactory
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreFactory $storeFactory,
        OrderRepositoryInterface $orderRepository,
        CurrencyInterface $localeCurrency,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        FactoryInterface $templateFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeFactory = $storeFactory;

        $this->orderRepository = $orderRepository;

        $this->localeCurrency = $localeCurrency;
        $this->assetRepo = $assetRepo;
        $this->templateFactory = $templateFactory;
        $this->appEmulation = $appEmulation;
        $this->timezone = $timezone;
    }

    /**
     * Send email using given gift card code model
     *
     * @param \Aheadworks\Giftcard\Model\Giftcard $giftCardCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendGiftCardCode($giftCardCode)
    {
        if ($giftCardCode->getEmailTemplate() != EmailTemplate::DO_NOT_SEND_VALUE) {
            $storeCollection = $this->storeFactory->create()
                ->getCollection()
                ->addWebsiteFilter($giftCardCode->getWebsiteId())
            ;
            /** @var $store \Magento\Store\Model\Store */
            $store = $storeCollection->getFirstItem();
            if (!$store->getId()) {
                throw new LocalizedException(__('Unable to send email'));
            }
            if ($giftCardCode->getState() != Status::AVAILABLE_VALUE) {
                throw new LocalizedException(__('Unable to send email: gift card code is not active'));
            }
            $currencyCode = $store->getCurrentCurrencyCode();
            if ($giftCardCode->getOrderId()) {
                try {
                    $currencyCode = $this->orderRepository->get($giftCardCode->getOrderId())->getOrderCurrencyCode();
                } catch (\Exception $e) {
                }
            }
            $this->send(
                $giftCardCode->getEmailTemplate(),
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $store->getId()
                ],
                $this->prepareTemplateVars(
                    [
                        'store' => $store,
                        TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME => $giftCardCode->getRecipientName(),
                        TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL => $giftCardCode->getRecipientEmail(),
                        TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME => $giftCardCode->getSenderName(),
                        TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL => $giftCardCode->getSenderEmail(),
                        'giftcard_codes' => [$giftCardCode->getCode()],
                        'balance' => $giftCardCode->getBalance(),
                        'currency_code' => $currencyCode,
                        'expired_at' => $giftCardCode->getExpireAt()
                    ]
                ),
                $this->scopeConfig->getValue(
                    self::XML_PATH_SENDER_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                ),
                [$giftCardCode->getRecipientName() => $giftCardCode->getRecipientEmail()]
            );
        }
    }

    /**
     * Prepare email data and send
     *
     * @param array $data
     */
    public function prepareAndSend(array $data)
    {
        $this->send(
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE],
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $data['store_id']
            ],
            $this->prepareTemplateVars($data),
            $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $data['store_id']
            ),
            [$data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME] => $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL]]
        );
    }

    public function send($templateId, array $templateOptions, array $templateVars, $from, array $to)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
        ;
        $this->transportBuilder->getTransport()->sendMessage();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareTemplateVars($data)
    {
        $templateVars = [];
        /** @var $store \Magento\Store\Model\Store */
        $store = $data['store'];
        $templateVars['store'] = $store;
        $templateVars['store_name'] = $store->getName();

        $this->appEmulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);
        $templateVars['card_image_base_url'] = $this->assetRepo->getUrl('Aheadworks_Giftcard::images/email/cards') . '/';
        $this->appEmulation->stopEnvironmentEmulation();

        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME])) {
            $templateVars['recipient_name'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME];
        }
        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL])) {
            $templateVars['recipient_email'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL];
        }
        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME])) {
            $templateVars['sender_name'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME];
        }
        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL])) {
            $templateVars['sender_email'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL];
        }
        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_HEADLINE])) {
            $templateVars['headline'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_HEADLINE];
        }
        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE])) {
            $templateVars['message'] = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE];
        }
        if (isset($data['giftcard_codes'])) {
            $templateVars['giftcards'] = $data['giftcard_codes'];
            $templateVars['is_multiple_codes'] = count($data['giftcard_codes']) > 1;
        }
        if (isset($data['balance'])) {
            $templateVars['balance'] = $this->convertToCurrency(
                $data['currency_code'],
                $data['balance'] * $store->getBaseCurrency()->getRate($data['currency_code'])
            );
        }
        if (isset($data['expired_at']) && !empty($data['expired_at'])) {
            $templateVars['expired_at'] = $this->timezone->formatDate(
                $this->timezone->scopeDate(
                    $store,
                    $data['expired_at'],
                    true
                ),
                \IntlDateFormatter::LONG,
                false
            );
        }
        return $templateVars;
    }

    /**
     * Convert amount to given currency and return string representation
     *
     * @param string $currencyCode
     * @param float $amount
     * @return string
     */
    protected function convertToCurrency($currencyCode, $amount)
    {
        return $this->localeCurrency->getCurrency($currencyCode)->toCurrency(sprintf("%f", $amount));
    }
}
