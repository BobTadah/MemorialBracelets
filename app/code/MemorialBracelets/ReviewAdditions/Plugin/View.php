<?php

namespace MemorialBracelets\ReviewAdditions\Plugin;

class View
{
    /**
     * @param \Magento\Review\Controller\Customer\Index $index
     * @param callable $proceed
     * @return array|void
     */
    public function aroundExecute(\Magento\Review\Controller\Customer\View $view, callable $proceed)
    {
        $result = $proceed();
        $result->getConfig()->getTitle()->set(__('Reviews and Reflections Details'));
        return $result;
    }
}
