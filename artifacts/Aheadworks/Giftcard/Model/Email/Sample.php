<?php
namespace Aheadworks\Giftcard\Model\Email;

/**
 * Class Sample
 * @package Aheadworks\Giftcard\Model\Email
 */
class Sample extends \Magento\Framework\Config\Data
{
    /**
     * @param Sample\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\Email\Sample\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'aheadworks_giftcard_sample_email_templates_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
