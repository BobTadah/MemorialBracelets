<?php

namespace MemorialBracelets\ReviewAdditions\Plugin;

class Index
{
    /**
     * @param \Magento\Review\Controller\Customer\Index $index
     * @param callable $proceed
     * @return array|void
     */
    public function aroundExecute(\Magento\Review\Controller\Customer\Index $index, callable $proceed)
    {
        $result = $proceed();
        $result->getConfig()->getTitle()->set(__('Reviews and Reflections'));
        return $result;
    }
}
