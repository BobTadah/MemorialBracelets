<?php
namespace Aheadworks\Giftcard\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\FactoryInterface;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Previewer
 * @package Aheadworks\Giftcard\Model\Email
 */
class Previewer extends Sender
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

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
     * @param \Magento\Customer\Model\Session $customerSession
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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct(
            $transportBuilder,
            $scopeConfig,
            $storeFactory,
            $orderRepository,
            $localeCurrency,
            $assetRepo,
            $templateFactory,
            $appEmulation,
            $timezone
        );
        $this->customerSession = $customerSession;
    }

    /**
     * @param $data
     * @param $storeId
     * @return string
     */
    public function getPreview($data, $storeId)
    {
        $data = $this->prepare($data, $storeId);
        $template = $this->getTemplate(
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE],
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ],
            $this->prepareTemplateVars($data)
        );
        return $template->processTemplate();
    }

    /**
     * @param $templateId
     * @param array $templateOptions
     * @param array $templateVars
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    protected function getTemplate($templateId, array $templateOptions, array $templateVars)
    {
        return $this->templateFactory->get($templateId)
                ->setVars($templateVars)
                ->setOptions($templateOptions)
            ;
    }

    /**
     * Prepare data for preview
     *
     * @param array $data
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepare($data, $storeId)
    {
        $this->validate($data);
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->storeFactory->create()->load($storeId);
        if (!$store->getId()) {
            throw new LocalizedException(__('Invalid Store Id'));
        }
        $data['store'] = $store;
        $data['currency_code'] = $store->getCurrentCurrencyCode();
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME] = $customer->getName();
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL] = $customer->getEmail();
        }
        $data['giftcard_codes'] = ['XXXXXXXXXXXX'];
        $data['balance'] = (float)($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT] == 'custom' ?
            $data['aw_gc_custom_amount'] :
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT]);
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareTemplateVars($data)
    {
        $templateVars = parent::prepareTemplateVars($data);
        if ($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT] == 'custom') {
            $templateVars['balance'] = $this->localeCurrency->getCurrency($data['currency_code'])
                ->toCurrency(sprintf("%f", $data['balance']));
            $templateVars['balance'] = $this->convertToCurrency($data['currency_code'], $data['balance']);
        }
        return $templateVars;
    }

    /**
     * Validate data for preview
     *
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validate($data)
    {
        if (
            empty($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT]) ||
            $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_AMOUNT] == 'custom' && empty($data['aw_gc_custom_amount'])
        ) {
            throw new LocalizedException(__('Please specify Gift Card amount.'));
        }
        if (empty($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE])) {
            throw new LocalizedException(__('Please specify design.'));
        }
    }
}
