<?php
namespace Aheadworks\Giftcard\Helper\Catalog\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class Options extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Mail\Template\FactoryInterface
     */
    protected $templateFactory;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Mail\Template\FactoryInterface $templateFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->templateFactory = $templateFactory;
        $this->emailConfig = $emailConfig;
        $this->mediaConfig = $mediaConfig;
        $this->customerSession = $customerSession;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getProductOptions($productId)
    {
        return [
            'amounts' => $this->getAmountOptions($productId),
            'maxCustomAmount' => $this->getMaxCustomAmount($productId),
            'minCustomAmount' => $this->getMinCustomAmount($productId),
            'templates' => $this->getTemplateOptions($productId),
            'isLoggedIn' => $this->getCustomerLoggedId()
        ];
    }

    /**
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @param int $productId
     * @return array
     */
    protected function getAmountOptions($productId)
    {
        $result = [];
        $amountOptions = $this->getProduct($productId)->getTypeInstance()->getAmountOptions(
            $this->getProduct($productId)
        );
        foreach ($amountOptions as $option) {
            $result[] = [
                'value' => $option,
                'label' => $this->priceCurrency->convertAndFormat($option, false)
            ];
        }
        return $result;
    }

    /**
     * @param int $productId
     * @return array
     */
    protected function getTemplateOptions($productId)
    {
        $result = [];
        $templateOptions =  $this->getProduct($productId)->getTypeInstance()->getTemplateOptions(
            $this->getProduct($productId)
        );
        foreach ($templateOptions as $option) {
            $result[] = [
                'value' => $option['template'],
                'name' => $this->getTemplateName($option['template']),
                'imageUrl' => $option['image'] ? $this->mediaConfig->getTmpMediaUrl($option['image']) : ''
            ];
        }
        return $result;
    }

    /**
     * @param int|string $templateId
     * @return string
     */
    protected function getTemplateName($templateId)
    {
        /** @var \Magento\Email\Model\Template $template */
        $template = $this->templateFactory->get($templateId);
        if (is_numeric($templateId)) {
            return $template->load($templateId)->getTemplateCode();
        } else {
            return $this->emailConfig->getTemplateLabel($templateId);
        }
    }

    /**
     * @param int $productId
     * @return array|bool
     */
    protected function getMinCustomAmount($productId)
    {
        if ($this->isAllowOpenAmount($productId)) {
            $value = $this->getProduct($productId)->getData(TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MIN);
            return [
                'value' => $this->priceCurrency->convertAndRound($value),
                'label' => $this->priceCurrency->convertAndRound($value)
            ];
        }
        return false;
    }

    /**
     * @param int $productId
     * @return array|bool
     */
    protected function getMaxCustomAmount($productId)
    {
        if ($this->isAllowOpenAmount($productId)) {
            $value = $this->getProduct($productId)->getData(TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MAX);
            return [
                'value' => $this->priceCurrency->convertAndRound($value),
                'label' => $this->priceCurrency->convertAndRound($value)
            ];
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function getCustomerLoggedId()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @param int $productId
     * @return bool
     */
    protected function isAllowOpenAmount($productId)
    {
        return (bool)$this->getProduct($productId)->getData(TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT);
    }
}