<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Model;

use Amasty\Shiprestriction\Model\RegistryConstants;
use Amasty\Shiprestriction\Model\ResourceModel\Rule\Collection;
use Amasty\CommonRules\Model\Rule as CommonRule;

class ShippingRestrictionRule
{
    const SECTION = "amshiprestriction";

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $allRules;

    /**
     * @var \Amasty\Shiprestriction\Model\ResourceModel\Rule\Collection
     */
    protected $shippingRestrictionRulesCollection;

    /**
     * @var \Amasty\Shiprestriction\Model\ProductRegistry
     */
    protected $productRegistry;

    /**
     * @var Message\MessageBuilder
     */
    private $messageBuilder;

    /**
     * @var \Amasty\CommonRules\Model\Validator\Coupon
     */
    private $couponValidator;

    /**
     * Rule constructor.
     *
     * @param \Amasty\Shiprestriction\Model\ResourceModel\Rule\Collection $shippingRestrictionRulesCollection ,
     * @param \Amasty\Shiprestriction\Model\ProductRegistry $productRegistry,
     * @param \Amasty\Shiprestriction\Model\Message\MessageBuilder $messageBuilder
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Shiprestriction\Model\ResourceModel\Rule\Collection $shippingRestrictionRulesCollection,
        \Amasty\Shiprestriction\Model\ProductRegistry $productRegistry,
        \Amasty\Shiprestriction\Model\Message\MessageBuilder $messageBuilder,
        \Amasty\CommonRules\Model\Validator\Coupon $couponValidator
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->shippingRestrictionRulesCollection = $shippingRestrictionRulesCollection;
        $this->productRegistry = $productRegistry;
        $this->messageBuilder = $messageBuilder;
        $this->couponValidator = $couponValidator;
    }

    public function getRestrictionRules($request)
    {
        $all = $request->getAllItems();
        if (!$all) {
            return array();
        }
        $firstItem = current($all);
        /**
         * @var $address \Magento\Quote\Model\Quote\Address
         */
        $address = $firstItem->getAddress();
        $address->setItemsToValidateRestrictions($request->getAllItems());

        //multishipping optimization
        if (is_null($this->allRules)) {
            $this->allRules = $this->shippingRestrictionRulesCollection->addAddressFilter($address);
            if ($this->isAdmin()) {
                $this->allRules->addFieldToFilter('for_admin', 1);
            }

            $this->allRules->load();
            foreach ($this->allRules as $rule) {
                $rule->afterLoad();
            }
        }

        /**
         * Fix for admin checkout
         */
        if ($this->isAdmin() && $address->getSubtotal() == 0
            && $address->getOrigData('subtotal') != $address->getSubtotal()
        ) {
            $address->addData($address->getOrigData());
        }

        // remember old
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();

        $validRules = [];
        foreach ($this->allRules as $rule) {
            $this->productRegistry->clearProducts();

            if ($rule->validate($address, $request->getAllItems())
                && $this->couponValidator->validate($rule, $all, $request)
                && !$this->couponValidator->validate($rule, $all, $request, true)
            ) {
                // remember used products
                $newMessage = $this->messageBuilder->parseMessage(
                    $rule->getMessage(),
                    $this->productRegistry->getProducts()
                );

                $rule->setMessage($newMessage);

                $validRules[] = $rule;
            }
        }

        // restore
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

        return $validRules;
    }

    /**
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->context->getAppState()->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }
}

