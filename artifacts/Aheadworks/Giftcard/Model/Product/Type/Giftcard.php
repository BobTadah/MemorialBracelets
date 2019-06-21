<?php
namespace Aheadworks\Giftcard\Model\Product\Type;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType;
use Aheadworks\Giftcard\Model\Email\Sender;

/**
 * Giftcard product type implementation
 */
class Giftcard extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Gift Card product type code
     */
    const TYPE_CODE = 'aw_giftcard';

    /**
     * Gift Card product attributes codes
     */
    const ATTRIBUTE_CODE_TYPE = 'aw_gc_type';
    const ATTRIBUTE_CODE_DESCRIPTION = 'aw_gc_description';
    const ATTRIBUTE_CODE_EXPIRE = 'aw_gc_expire';
    const ATTRIBUTE_CODE_ALLOW_MESSAGE = 'aw_gc_allow_message';
    const ATTRIBUTE_CODE_EMAIL_TEMPLATES = 'aw_gc_email_templates';
    const ATTRIBUTE_CODE_AMOUNTS = 'aw_gc_amounts';
    const ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT = 'aw_gc_allow_open_amount';
    const ATTRIBUTE_CODE_OPEN_AMOUNT_MIN = 'aw_gc_open_amount_min';
    const ATTRIBUTE_CODE_OPEN_AMOUNT_MAX = 'aw_gc_open_amount_max';

    /**
     * Buy request attributes codes
     */
    const BUY_REQUEST_ATTR_CODE_AMOUNT = 'aw_gc_amount';
    const BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT = 'aw_gc_custom_amount';
    const BUY_REQUEST_ATTR_CODE_SENDER_NAME = 'aw_gc_sender_name';
    const BUY_REQUEST_ATTR_CODE_SENDER_EMAIL = 'aw_gc_sender_email';
    const BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME = 'aw_gc_recipient_name';
    const BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL = 'aw_gc_recipient_email';
    const BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE = 'aw_gc_template';
    const BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME = 'aw_gc_template_name';
    const BUY_REQUEST_ATTR_CODE_HEADLINE = 'aw_gc_headline';
    const BUY_REQUEST_ATTR_CODE_MESSAGE = 'aw_gc_message';

    /**
     * Gift Card Product option codes to convert into order options
     *
     * @var array
     */
    protected $orderedOptionCodes = [
        self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE,
        self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME,
        self::BUY_REQUEST_ATTR_CODE_SENDER_NAME,
        self::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL,
        self::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME,
        self::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL,
        self::BUY_REQUEST_ATTR_CODE_MESSAGE,
        self::BUY_REQUEST_ATTR_CODE_HEADLINE,
        self::ATTRIBUTE_CODE_TYPE
    ];

    /**
     * Gift Card Product option codes to process buy request
     *
     * @var array
     */
    protected $buyRequestOptionCodes = [
        self::BUY_REQUEST_ATTR_CODE_AMOUNT,
        self::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT,
        self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE,
        self::BUY_REQUEST_ATTR_CODE_SENDER_NAME,
        self::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL,
        self::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME,
        self::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL,
        self::BUY_REQUEST_ATTR_CODE_HEADLINE,
        self::BUY_REQUEST_ATTR_CODE_MESSAGE
    ];

    /**
     * @var \Aheadworks\Giftcard\Model\Statistics
     */
    protected $statistics;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Mail\Template\FactoryInterface
     */
    protected $emailTemplateFactory;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $emailTemplateConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * If product can be configured
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Aheadworks\Giftcard\Model\Statistics $statistics
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Mail\Template\FactoryInterface $emailTemplateFactory
     * @param \Magento\Email\Model\Template\Config $emailTemplateConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Aheadworks\Giftcard\Model\Statistics $statistics,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Mail\Template\FactoryInterface $emailTemplateFactory,
        \Magento\Email\Model\Template\Config $emailTemplateConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
        $this->statistics = $statistics;
        $this->customerSession = $customerSession;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isTypeVirtual(\Magento\Catalog\Model\Product $product)
    {
        return $this->getAttribute($product, self::ATTRIBUTE_CODE_TYPE) == GiftcardType::VALUE_VIRTUAL;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isTypePhysical(\Magento\Catalog\Model\Product $product)
    {
        return $this->getAttribute($product, self::ATTRIBUTE_CODE_TYPE) == GiftcardType::VALUE_PHYSICAL;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isTypeCombined(\Magento\Catalog\Model\Product $product)
    {
        return $this->getAttribute($product, self::ATTRIBUTE_CODE_TYPE) == GiftcardType::VALUE_COMBINED;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        return $this->isTypeVirtual($product);
    }

    /**
     * Save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function save($product)
    {
        $this->statistics->createStatistics($product);
        return parent::save($product);
    }

    /**
     * Retrieves amounts of given Gift Card product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAmounts(\Magento\Catalog\Model\Product $product)
    {
        $amounts = [];
        $websiteId = $product->getStore()->getWebsiteId();
        $amountsData = $this->getAttribute($product, self::ATTRIBUTE_CODE_AMOUNTS);
        if (is_null($amountsData)) {
            $this->reloadAttributesData($product);
            $amountsData = $this->getAttribute($product, self::ATTRIBUTE_CODE_AMOUNTS);
        }
        foreach ($amountsData as $data) {
            if (in_array($data['website_id'], [$websiteId, 0])) {
                $amounts[] = $data['price'];
            }
        }
        return $amounts;
    }

    /**
     * Reloads attributes data for given product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function reloadAttributesData(\Magento\Catalog\Model\Product $product)
    {
        $setAttributes = $product->getResource()
            ->loadAllAttributes($product)
            ->getSortedAttributes($product->getAttributeSetId());
        $product->setData($this->_cacheProductSetAttributes, $setAttributes);
    }

    /**
     * Get amount options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAmountOptions(\Magento\Catalog\Model\Product $product)
    {
        $amountOptions = $this->getAmounts($product);
        sort($amountOptions);
        return $amountOptions;
    }

    /**
     * Get email templates options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTemplateOptions(\Magento\Catalog\Model\Product $product)
    {
        $templateOptions = [];
        $storeId = $product->getStoreId();
        foreach ($product->getData(self::ATTRIBUTE_CODE_EMAIL_TEMPLATES) as $data) {
            if (in_array($data['store_id'], [$storeId, 0])) {
                $templateOptions[] = [
                    'template' => $data['template'],
                    'image' => $data['image']
                ];
            }
        }
        return $templateOptions;
    }

    public function getGiftCardProductOptions(\Magento\Catalog\Model\Product $product, $storeId)
    {
        $options = [];
        $keys = [
            self::ATTRIBUTE_CODE_TYPE,
            self::ATTRIBUTE_CODE_EXPIRE
        ];
        $product
            ->setStoreId($storeId)
            ->load($product->getId());
        foreach ($keys as $key) {
            if ($product->hasData($key)) {
                $options[$key] = $product->getData($key);
            }
        }
        return $options;
    }

    /**
     * Delete data specific for this product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        // TODO: Implement deleteTypeSpecificData() method.
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|\Magento\Framework\Phrase|string
     */
    protected function _prepareProduct(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode
    ) {
        $result = [];
        if (method_exists(get_parent_class($this), 'prepareForCartAdvanced')) {
            $result = parent::_prepareProduct($buyRequest, $product, $processMode);
            if (is_string($result)) {
                return $result;
            }
        }
        try {
            $this->validateBuyRequest($buyRequest, $product, $processMode);
            $amount = $this->getAmount($buyRequest, $product, $processMode);
            $this->validateAmount($buyRequest, $product, $processMode, $amount);
        } catch (LocalizedException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return __('An error has occurred while adding product to cart.');
        }
        if (isset($amount)) {
            $product->addCustomOption(self::ATTRIBUTE_CODE_AMOUNTS, $amount, $product);
        }

        $senderName = $this->customerSession->isLoggedIn() ?
            $this->customerSession->getCustomer()->getName() :
            $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_SENDER_NAME);
        $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_SENDER_NAME, $senderName, $product);

        $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME, $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME), $product);
        if (!$this->isTypePhysical($product)) {

            $senderEmail = $this->customerSession->isLoggedIn() ?
                $this->customerSession->getCustomer()->getEmail() :
                $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL);
            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL, $senderEmail, $product);

            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL, $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL), $product);

            $emailTemplateId = $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE);
            /** @var \Magento\Email\Model\Template $emailTemplate */
            $emailTemplate = $this->emailTemplateFactory->get($emailTemplateId);
            if (is_numeric($emailTemplateId)) {
                $emailTemplate->load($emailTemplateId);
                if (!$emailTemplate->getId()) {
                    $emailTemplateId = Sender::DEFAULT_EMAIL_TEMPLATE_ID;
                }
            }
            if (!is_numeric($emailTemplateId)) {
                $emailTemplate->loadDefault($emailTemplateId);
            }
            $emailTemplateName = is_numeric($emailTemplateId) ?
                $emailTemplate->getTemplateCode() :
                $this->emailTemplateConfig->getTemplateLabel($emailTemplateId)->getText();
            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE, $emailTemplateId, $product);
            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME, $emailTemplateName, $product);
        }
        if ($product->getData(self::ATTRIBUTE_CODE_ALLOW_MESSAGE)) {
            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_HEADLINE, $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_HEADLINE), $product);
            $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_MESSAGE, $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_MESSAGE), $product);
        }
        $product->addCustomOption(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE, $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE), $product);
        return $result;
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param $processMode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateBuyRequest(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode
    ) {
        if ($this->isCustomAmount($buyRequest, $product)) {
            if ($buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT) <= 0 && $this->_isStrictProcessMode($processMode)) {
                throw new LocalizedException(__('Please specify Gift Card amount.'));
            }
        }
        if (
            !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME) &&
            $this->_isStrictProcessMode($processMode)
        ) {
            throw new LocalizedException(__('Please specify recipient name.'));
        }
        if (
            !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_SENDER_NAME) &&
            $this->_isStrictProcessMode($processMode) &&
            !$this->customerSession->isLoggedIn()
        ) {
            throw new LocalizedException(__('Please specify sender name.'));
        }
        if (!$this->isTypePhysical($product)) {
            if (
                !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL) &&
                $this->_isStrictProcessMode($processMode)
            ) {
                throw new LocalizedException(__('Please specify recipient email.'));
            }
            if (
                !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL) &&
                $this->_isStrictProcessMode($processMode) &&
                !$this->customerSession->isLoggedIn()
            ) {
                throw new LocalizedException(__('Please specify sender email.'));
            }
            if (
                !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE) &&
                $this->_isStrictProcessMode($processMode)
            ) {
                throw new LocalizedException(__('Please specify a design.'));
            }
        }
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param $processMode
     * @return int|mixed|null|string
     */
    protected function getAmount(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode
    ) {
        $amountOptions = $this->getAmountOptions($product);
        $selectedAmountOption = $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_AMOUNT);
        $customAmount = $buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_CUSTOM_AMOUNT);

        $amount = null;
        if ($this->isCustomAmount($buyRequest, $product)) {
            /** @var \Magento\Directory\Model\Currency $currency */
            $currency = $this->priceCurrency->getCurrency($product->getStoreId());
            $baseCurrency = $this->storeManager->getStore($product->getStoreId())->getBaseCurrency();
            $amount = $currency->convert($customAmount, $baseCurrency);
        }
        if (is_numeric($selectedAmountOption) && in_array($selectedAmountOption, $amountOptions)) {
            $amount = $selectedAmountOption;
        }
        if (is_null($amount) && count($amountOptions) == 1) {
            $amount = array_shift($allowedAmounts);
        }
        if (is_null($amount) && $product->getCustomOption('aw_gc_amounts')) {
            $amount = $product->getCustomOption('aw_gc_amounts')->getValue();
        }
        return $amount;
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param $processMode
     * @param $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateAmount(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode,
        $amount
    ) {
        $minOpenAmount = $buyRequest->getData('aw_gc_open_amount_min');
        $maxOpenAmount = $buyRequest->getData('aw_gc_open_amount_max');
        if (is_null($amount) && $this->_isStrictProcessMode($processMode)) {
            throw new LocalizedException(__('Please specify Gift Card amount.'));
        }
        if ($this->isCustomAmount($buyRequest, $product)) {
            if ($maxOpenAmount && $amount > $maxOpenAmount && $this->_isStrictProcessMode($processMode)) {
                // todo: add formatted amount
                throw new LocalizedException(__('Maximum allowed Gift Card amount is %1'));
            }

            if ($minOpenAmount && $amount < $minOpenAmount && $this->_isStrictProcessMode($processMode)) {
                // todo: add formatted amount
                throw new LocalizedException(__('Minimum allowed Gift Card amount is %1'));
            }
        }
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function isCustomAmount(\Magento\Framework\DataObject $buyRequest, $product)
    {
        return
            ($buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_AMOUNT) == 'custom' || !$buyRequest->getData(self::BUY_REQUEST_ATTR_CODE_AMOUNT)) &&
            $product->getData(self::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = [];
        foreach ($this->orderedOptionCodes as $code) {
            if ($customOption = $product->getCustomOption($code)) {
                $options[$code] = $customOption->getValue();
            } else if ($product->hasData($code)) {
                $options[$code] = $product->getData($code);
            }
        }
        return array_merge(
            $options,
            parent::getOrderOptions($product)
        );
    }

    /**
     * @param string $processMode
     * @return bool
     */
    protected function _isStrictProcessMode($processMode)
    {
        $isStrictProcessMode = true;
        if (method_exists(get_parent_class($this), '_isStrictProcessMode')) {
            $isStrictProcessMode = parent::_isStrictProcessMode($processMode);
        }
        return $isStrictProcessMode;
    }

    protected function getAttribute(\Magento\Catalog\Model\Product $product, $code)
    {
        if (!$product->hasData($code)) {
            $product->getResource()->load($product, $product->getId());
        }
        return $product->getData($code);
    }

    /**
     * Prepare selected options for product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = [];
        foreach ($this->buyRequestOptionCodes as $code) {
            if ($buyRequest->hasData($code)) {
                $options[$code] = $buyRequest->getData($code);
            }
        }
        return $options;
    }
}
