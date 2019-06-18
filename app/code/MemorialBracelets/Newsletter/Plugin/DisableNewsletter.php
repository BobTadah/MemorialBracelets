<?php

namespace MemorialBracelets\Newsletter\Plugin;

/**
 * Plugin to disable the newsletter
 * @see \Magento\Customer\Block\Account\Dashboard\Info
 * @see \Magento\Customer\Block\Form\Register
 */
class DisableNewsletter
{
    /**
     * A couple Magento\Customer blocks have a custom isNewsletterEnabled function that checks whether or not the
     * output of the newsletter plugin is enabled to determine whether or not it should output newsletter stuff.
     *
     * Let's just straight up tell it no.
     *
     * @see \Magento\Customer\Block\Account\Dashboard\Info::isNewsletterEnabled()
     * @see \Magento\Customer\Block\Form\Register::isNewsletterEnabled()
     * @return false
     */
    public function aroundIsNewsletterEnabled()
    {
        return false;
    }
}
