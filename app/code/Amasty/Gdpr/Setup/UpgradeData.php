<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup;

use Amasty\Gdpr\Model\Config;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    public function __construct(
        PageFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        WriterInterface $configWriter,
        TypeListInterface $typeList,
        ConfigInterface $resourceConfig
    ) {
        $this->configWriter = $configWriter;
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->typeList = $typeList;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion() || version_compare($context->getVersion(), '1.1.0', '<')) {
            /** @var \Magento\Cms\Api\Data\PageInterface $page */
            $page = $this->pageFactory->create();
            $page->setIsActive(1);
            $page->setTitle('Cookie Policy');
            if (!$page->checkIdentifier('cookie-policy', 0)) {
                $identifier = 'cookie-policy';
            } elseif (!$page->checkIdentifier('gdpr-cookie-policy', 0)) {
                $identifier = 'gdpr-cookie-policy';
            } else {
                $identifier = 'cookie-policy-' . mt_rand(1000, 9999);
            }
            $page->setIdentifier($identifier);
            $page->setContent($this->getCookiePolicyContent());
            $page->setPageLayout('1column');
            $page->setStoreId(["0"]);
            try {
                $this->pageRepository->save($page);
                $this->configWriter->save(
                    Config::PATH_PREFIX . '/' . Config::NOTIFY_BAR_TEXT,
                    $this->getNotifyCookiePolicyText($identifier)
                );
                $this->configWriter->save(
                    Config::PATH_PREFIX . '/' . Config::CONFIRMATION_BAR_TEXT,
                    $this->getConfirmationCookiePolicyText($identifier)
                );
                $this->typeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
            } catch (\Exception $e) {

            }
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.5', '<')) {
            $path = 'amasty_gdpr/deletion_notification/';
            $fields = ['sender', 'reply_to', 'template'];
            $connection = $this->resourceConfig->getConnection();

            foreach ($fields as $key => $fieldId) {
                $data = ['path' => $path . 'deny_' . $fieldId];
                $whereCondition = ['path = ?' => $path . $fieldId];
                $connection->update($this->resourceConfig->getMainTable(), $data, $whereCondition);
            }
        }
    }

    /**
     * @return string
     */
    private function getCookiePolicyContent()
    {
        return '<p>This site, like many others, uses small files called cookies to help us customize your experience.
                    Find out more about cookies and how you can control them.</p>
                    <p></p>
                    <p>What is a cookie?</p>
                    <p></p>
                    <p>A cookie is a small file that can be placed on your device that allows us
                    to recognise and remember you. It is sent to your browser and stored on your computer’s
                    hard drive or tablet or mobile device. When you visit our sites, we may collect information
                    from you automatically through cookies or similar technology.</p>
                    <p></p>
                    <p>How do we use cookies?</p>
                    <p></p>
                    <p>We use cookies in a range of ways to improve your experience on our site, including:</p>
                    <p></p>
                    <p>Keeping you signed in;</p>
                    <p>Understanding how you use our site;</p>
                    <p>Showing you content that is relevant to you;</p>
                    <p>Showing you products and services that are relevant to you.;</p>';
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getNotifyCookiePolicyText($identifier)
    {
        return preg_replace(
            '/%url%/is',
            $identifier,
            'The Website uses cookies. By continuing to use this website,'
                . ' your consent to our use of these cookies.'
                . ' <a href="{{base_url}}%url%" target="_blank">Find out more</a>'
        );
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getConfirmationCookiePolicyText($identifier)
    {
        return preg_replace(
            '/%url%/is',
            $identifier,
            'We use cookies to help improve our services, make personal offers, and enhance your experience.'
                . ' If you do not accept optional cookies below, your experience may be affected.'
                . ' If you want to know more, please read the '
                . ' <a href="{{base_url}}%url%" target="_blank">Cookie Policy</a>'
        );
    }
}
