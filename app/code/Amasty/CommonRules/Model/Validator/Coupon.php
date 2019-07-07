<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CommonRules
 */


namespace Amasty\CommonRules\Model\Validator;

class Coupon implements \Amasty\CommonRules\Model\Validator\ValidatorInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $salesRuleCoupon;

    /**
     * Coupon constructor.
     * @param \Magento\SalesRule\Model\CouponFactory $salesRuleCoupon
     */
    function __construct(\Magento\SalesRule\Model\CouponFactory $salesRuleCoupon)
    {
        $this->salesRuleCoupon = $salesRuleCoupon->create();
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     * @param $items
     * @param null $request
     * @param bool $isDisable
     * @return bool
     */
    public function validate($rule, $items, $request = null, $isDisable = false)
    {
        if (!$isDisable) {
            $code = $rule->getCoupon();
            $discountIds = $rule->getDiscountId();
        } else {
            $code = $rule->getCouponDisable();
            $discountIds = $rule->getDiscountIdDisable();
        }

        $actualCouponCode = trim(strtolower($code));
        $actualListDiscountId = array_filter($discountIds ? explode(',', $discountIds) : []);

        if (!$actualCouponCode && empty($actualListDiscountId)) {
            return !$isDisable;
        }

        $providedCouponCodes = $this->getCouponCodes($request);
        if ($actualCouponCode) {
            return in_array($actualCouponCode, $providedCouponCodes);
        }

        if (!empty($actualListDiscountId)) {
            foreach ($providedCouponCodes as $code) {
                $couponModel = $this->salesRuleCoupon->loadByCode($code);
                $providedDiscountId = $couponModel->getRuleId();

                if (in_array($providedDiscountId, $actualListDiscountId)) {
                    return true;
                }

                $couponModel = null;
            }

        }

        return false;
    }

    /**
     * @param $request
     * @return array
     */
    public function getCouponCodes($request)
    {
        if (!count($request->getAllItems())) {
            return [];
        }

        $firstItem = current($request->getAllItems());
        $codes = trim(strtolower($firstItem->getQuote()->getCouponCode()));

        if (!$codes) {
            return [];
        }

        $providedCouponCodes = explode(",", $codes);

        foreach ($providedCouponCodes as $key => $code) {
            $providedCouponCodes[$key] = trim($code);
        }

        return $providedCouponCodes;
    }
}

