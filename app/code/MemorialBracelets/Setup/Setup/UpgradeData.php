<?php

namespace MemorialBracelets\Setup\Setup;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;

/**
 * Class UpgradeData
 *
 * @package MemorialBracelets\Setup\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var  ModuleDataSetupInterface */
    private $setup;

    /** @var  ModuleContextInterface */
    private $context;

    /** @var  Config */
    private $config;

    /** @var \Magento\Cms\Model\BlockFactory */
    protected $blockFactory;

    /** @var PageFactory */
    protected $pageFactory;

    /** @var IndexerRegistry */
    protected $indexerRegistry;

    /** @var ReinitableConfigInterface */
    protected $reinitableConfig;

    /** @var BlockRepositoryInterface $blockRepo */
    protected $blockRepo;

    /** @var PageRepositoryInterface $pageRepo */
    protected $pageRepo;

    /**
     * UpgradeData constructor.
     * @param PageFactory                     $pageFactory
     * @param Config                          $scopeConfig
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param IndexerRegistry                 $indexerRegistry
     * @param ReinitableConfigInterface       $reinitableConfig
     * @param BlockRepositoryInterface        $blockRepo
     * @param PageRepositoryInterface         $pageRepo
     */
    public function __construct(
        PageFactory $pageFactory,
        Config $scopeConfig,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        IndexerRegistry $indexerRegistry,
        ReinitableConfigInterface $reinitableConfig,
        BlockRepositoryInterface $blockRepo,
        PageRepositoryInterface $pageRepo
    ) {
        $this->pageFactory      = $pageFactory;
        $this->config           = $scopeConfig;
        $this->blockFactory     = $blockFactory;
        $this->indexerRegistry  = $indexerRegistry;
        $this->reinitableConfig = $reinitableConfig;
        $this->blockRepo        = $blockRepo;
        $this->pageRepo         = $pageRepo;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup   = $setup;
        $this->context = $context;

        $setup->startSetup();

        if (version_compare($context->getVersion(), '100.0.1') < 0) {
            $this->saveCmsBlock($this->createLoginBlock());
        }

        if (version_compare($context->getVersion(), '100.0.2') < 0) {

            /** array of all out new static blocks. */
            $cmsBlocks = [
                $this->createFooterAccountBlock(),
                $this->createFooterCompanyBlock(),
                $this->createFooterCopyrightBlock(),
                $this->createFooterPaymentBlock(),
                $this->createHeaderLinksBlock(),
                $this->createFaqLinks(),
                $this->createFooterWrapper()
            ];

            /** Cycle static blocks and attempt to save/update them. */
            foreach ($cmsBlocks as $data) {
                $this->saveCmsBlock($data);
            }

            /** array of all out new cms pages. */
            $cmsPages = [
                $this->createDonationPage(),
                $this->createAboutUsPage(),
                $this->createWhyWearPage(),
                $this->createFaqPage(),
                $this->createSatisfactionPage(),
                $this->createTermsAndConditionsPage()
            ];

            /** Cycle static pages and attempt to save them. */
            foreach ($cmsPages as $data) {
                $this->savePage($data);
            }
        }

        if (version_compare($context->getVersion(), '100.0.3') < 0) {
            $this->config->saveConfig('design/theme/theme_id', 4, 'default', 0);
            $this->reinitableConfig->reinit();
            $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
        }

        if (version_compare($context->getVersion(), '100.0.3') < 0) {
            $this->config->saveConfig('design/theme/theme_id', 4, 'default', 0);
            $this->reinitableConfig->reinit();
            $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
        }

        if (version_compare($context->getVersion(), '100.0.4') < 0) {
            $this->saveCmsBlock($this->createTextBanner());
        }

        if (version_compare($context->getVersion(), '100.0.5') < 0) {
            // here we remove the script tag from the slider (its added externally now due to wysiwyg issues).
            $this->saveCmsBlock($this->createTextBanner());
        }

        if (version_compare($context->getVersion(), '100.0.6') < 0) {
            /** array of all out new static blocks. */
            $cmsBlocks = [
                $this->createCheckPaymentBlock(),
                $this->createOtherPaymentBlock()
            ];

            /** Cycle static blocks and attempt to save/update them. */
            foreach ($cmsBlocks as $data) {
                $this->saveCmsBlock($data);
            }
        }

        if (version_compare($context->getVersion(), '100.0.7') < 0) {
            $this->saveCmsBlock($this->createSizeBlock());
        }

        if (version_compare($context->getVersion(), '100.0.8') < 0) {
            $this->savePage($this->createReflectionsPage());
        }

        if (version_compare($context->getVersion(), '100.0.9') < 0) {
            $this->saveCmsBlock($this->createCheckoutWarning());
        }

        if (version_compare($context->getVersion(), '100.1.0') < 0) {
            $this->savePage($this->createQuantityDiscount());
        }

        if (version_compare($context->getVersion(), '100.1.1') < 0) {
            $this->saveCmsBlock($this->createTextBanner());
        }

        if (version_compare($context->getVersion(), '100.1.2') < 0) {
            $this->saveHomePage($this->updateHomepageWithNewNewsletter());
        }

        if (version_compare($context->getVersion(), '100.1.3') < 0) {
            // removal of unused cms page
            $this->deleteCmsPage('quantity-discount');
            // addition of new block that will replace removed page
            $this->saveCmsBlock($this->createQtyDiscountBlock());
        }

        if (version_compare($context->getVersion(), '100.1.5') < 0) {
            $this->savePage($this->createReviewsPage());
        }

        if (version_compare($context->getVersion(), '100.1.6') < 0) {
            $this->config->saveConfig('web/cookie/cookie_lifetime', 600, 'default', 0);
        }
    }

    /**
     * This function will attempt to save/update the passed in block data.
     *
     * @param array $data
     */
    protected function saveCmsBlock($data)
    {
        /**
         * @var $cmsBlock \Magento\Cms\Model\Block
         */
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);

        if (!$cmsBlock->getData()) {//block does not exist: set data.
            $cmsBlock->setData($data);
        } else {//block exists: update data.
            $cmsBlock->addData($data);
        }

        $cmsBlock->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);

        $cmsBlock->save();
    }

    /**
     * This function will update the CMS HomePage with new data.
     *
     * @param $data array
     */
    public function saveHomePage($data)
    {
        /**
         * @var $cmsPage \Magento\Cms\Model\Page
         */
        $page = $this->pageFactory->create()->load(2);
        if (isset($data['content'])) {
            $page->setContent($data['content']);
        }
        if (isset($data['page_layout'])) {
            $page->setPageLayout($data['page_layout']);
        }
        if (isset($data['layout_update_xml'])) {
            $page->setLayoutUpdateXml($data['layout_update_xml']);
        }
        $page->save();
    }

    /**
     * @param $data
     */
    public function savePage($data)
    {
        $page = $this->pageFactory->create();
        $page->setTitle($data['title'])->setIdentifier($data['identifier'])->setIsActive(true)->setPageLayout($data['page_layout'])->setContent($data['content'])->setContentHeading($data['content_heading'])->setLayoutUpdateXml($data['layout_update_xml'])->setStores([0])->save();
    }

    /**
     * this will attempt to update a cms page from the passed in data argument.
     * @param $data
     */
    protected function updatePage($data)
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->pageFactory->create();
        try {
            $page->load($data['identifier']);
            $page->setPageLayout($data['page_layout'])->setContent($data['content'])->setLayoutUpdateXml($data['layout_update_xml'])->setTitle($data['title'])->setContentHeading($data['content_heading'])->save();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * this will attempt to delete a static block.
     * @param $identifier
     */
    protected function deleteCmsBlock($identifier)
    {
        try {
            /** @var \Magento\Cms\Api\Data\BlockInterface $cmsBlock */
            $cmsBlock = $this->blockRepo->getById($identifier);
            if ($cmsBlock->getId()) { // if block exists
                $this->blockRepo->delete($cmsBlock);
            }
        } catch (LocalizedException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * this will attempt to delete a cms page.
     * @param $identifier
     */
    protected function deleteCmsPage($identifier)
    {
        try {
            /** @var \Magento\Cms\Api\Data\BlockInterface $cmsBlock */
            $cmsPage = $this->pageRepo->getById($identifier);
            if ($cmsPage->getId()) { // if block exists
                $this->pageRepo->delete($cmsPage);
            }
        } catch (LocalizedException $e) {
            echo $e->getMessage();
        }
    }

    /*****************************************************
     *                 CMS: BLOCKS                       *
     *****************************************************/

    /**
     * @return array
     */
    public function createLoginBlock()
    {
        $html = <<<HTML
<div id="create-account-content">
   <p class="text">Sign up with Memorial Bracelets</p>
   <div class="list-container">
       <p class="text">- Lorem ipsum dolor sit amet</p>
       <p class="text">- Lorem ipsum dolor sit amet</p>
       <p class="text">- Lorem ipsum dolor sit amet</p>
       <p class="text">- Lorem ipsum dolor sit amet</p>
   </div>
</div>
HTML;

        $data = [
            'title'      => 'Login Create Content',
            'identifier' => 'login-create-content',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFooterAccountBlock()
    {
        $html = <<<HTML
<div id="account-footer" class="footer-column">
    <div class="title-container">
        <h3 class="title">Account</h3>
    </div>
    <div class="links">
        <a class="link" href="/customer/account/">My Account</a>
        <a class="link" href="/wishlist/">Wishlist</a>
        <a class="link" href="/sales/order/history/">Orders</a>
    </div>
</div>
HTML;

        $data = [
            'title'      => 'Footer Account',
            'identifier' => 'footer-account',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFooterCompanyBlock()
    {
        $html = <<<HTML
<div id="company-footer" class="footer-column">
    <div class="title-container">
        <h3 class="title">Memorial Bracelets</h3>
    </div>
    <div class="links">
        <a class="link" href="/privacy-policy-cookie-restriction-mode/">Privacy Statement</a>
        <a class="link" href="/customer-satisfaction/">Customer Satisfaction Policy</a>
        <a class="link" href="/terms-conditions/">Terms and Conditions</a>
        <a class="link" href="/contact/">Contact Us</a>
        <a class="link" href="/faq/">FAQS</a>
    </div>
</div>
HTML;

        $data = [
            'title'      => 'Footer Company',
            'identifier' => 'footer-company',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFooterCopyrightBlock()
    {
        $html = <<<HTML
<div id="copyright-footer" class="footer-column">
    <div class="title-container">
        <h3 class="title">Copyright</h3>
    </div>
    <div class="links">
        <p class="text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam gravida ut nulla ac dapibus.
         Sed sagittis nisl vel tincidunt dignissim. Praesent sit amet nunc a odio eleifend mollis.</p>
    </div>
</div>
HTML;

        $data = [
            'title'      => 'Footer Copyright',
            'identifier' => 'footer-copyright',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFooterPaymentBlock()
    {
        $html = <<<HTML
<div id="payment-footer" class="footer-column">
    <div class="title-container">
        <h3 class="title">Payment Methods</h3>
    </div>
    <div class="image-wrapper">
        <img src="{{media url=memorial/placeholder.png}}" alt="Payment One" title="Payment One"/>
        <img src="{{media url=memorial/placeholder.png}}" alt="Payment Two" title="Payment Two"/>
        <img src="{{media url=memorial/placeholder.png}}" alt="Payment Three" title="Payment Three"/>
        <img src="{{media url=memorial/placeholder.png}}" alt="Payment Four" title="Payment Four"/>
        <img src="{{media url=memorial/placeholder.png}}" alt="Payment Five" title="Payment Five"/>
    </div>
</div>
HTML;

        $data = [
            'title'      => 'Footer Payment',
            'identifier' => 'footer-payment',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createHeaderLinksBlock()
    {
        $html = <<<HTML
<div class="header-top-links">
    <a class="link" href="/donations/">Donations</a>
    <a class="link" href="/about-us/">About</a>
    <a class="link" href="/why-wear">Why Wear?</a>
    <a class="link" href="/faq/">FAQS</a>
    <a class="link" href="/contact/">Contact</a>
</div>
HTML;

        $data = [
            'title'      => 'Header Top Links',
            'identifier' => 'header-top-links',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFaqLinks()
    {
        $html = <<<HTML
<div class="faq-links">
    <h2>Memorial Bracelets Quick Links</h2>
    <ul>
        <li class="link"><a href="/about-us/">About Us</a></li>
        <li class="link"><a href="/contact/">Contact Us</a></li>
        <li class="link"><a href="/customer/account/">Account</a></li>
    </ul>
</div>
HTML;

        $data = [
            'title'      => 'Faq Links',
            'identifier' => 'faq-links',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createFooterWrapper()
    {
        $html = <<<HTML
<div class="footer-inner-wrapper">
   {{block class="Magento\\Cms\\Block\\Block" block_id="footer-account"}}
   {{block class="Magento\\Cms\\Block\\Block" block_id="footer-company"}}
   {{block class="Magento\\Cms\\Block\\Block" block_id="footer-copyright"}}
   {{block class="Magento\\Cms\\Block\\Block" block_id="footer-payment"}}
</div>
<div class="footer-bottom">
       {{block class="Magento\\Theme\\Block\\Html\\Footer" name="copyright-bottom" template="html/copyright.phtml"}}
</div>
HTML;

        $data = [
            'title'      => 'Footer Wrapper Container',
            'identifier' => 'footer-wrapper',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * This function will return the homepage text banner data inside an array.
     *
     * @return array
     */
    private function createTextBanner()
    {
        $html = <<<HTML
<div id="home-text-banner">
    <div id="home-hero-slider">
        <div title="Bracelets" class="item-container">
            <div class="image-container">
            <div class="image-wrapper">
                <img title="POW MIA" src="{{view url="images/homepage/pow.jpg"}}" alt="POW MIA" width="172" height="220"/>
                <img title="Heroes and Victims of Terrorism" src="{{view url="images/homepage/vot.jpg"}}" alt="Heroes and Victims of Terrorism" width="172" height="220"/>
            </div>
         
            </div>
            <div class="text-wrapper">
                <p class="text">In the 1970's, we wore POW/MIA bracelets bearing the name of a captured or lost soldier to create awareness. Today we wear Memorial Bracelets to keep loved ones, and heroes &amp; victims of terrorism alive in our hearts and minds.</p>
            </div>
        </div>
        <div title="Charity" class="item-container">
            <div class="image-container">
            <div class="image-wrapper">
                <img title="Charity" src="{{view url="images/homepage/charity.jpg"}}" alt="Charity" width="293" height="220" />
            </div>
             
            </div>
            <div class="text-wrapper">
                <p class="text">$2.00 from the sale of each product is donated to charities that support the families of the heroes and victims of terrorism. Because of the support of our loyal customers, we have donated over $125,000 since October 23, 2001.</p>
            </div>
        </div>
        <div  title="Special Request" class="item-container">
            <div class="image-container">
            <div class="image-wrapper">
                <img title="Special Request" src="{{view url="images/homepage/request.jpg"}}" alt="Special Request" width="287" height="110" />
            </div>
            </div>
            <div class="text-wrapper">
                <p class="text">If you would like to create your own Memorial Bracelet and Dog Tag for others to wear in honor of a lost friend or family member, you can make a Special Request in the Find A Name section to have their name added to the website.</p>
            </div>
        </div>
    </div>
</div>
HTML;

        $data = [
            'title'      => 'Home Text Banner',
            'identifier' => 'home-text-banner',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    public function createCheckPaymentBlock()
    {
        $html = <<<HTML
<div class="check-content-container">
   <p>This is the check block text placeholder</p>
</div>
HTML;

        $data = [
            'title'      => 'Check Payment Email Block',
            'identifier' => 'check-email-block',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    public function createOtherPaymentBlock()
    {
        $html = <<<HTML
<div class="payment-content-container">
   <p>This is the other payment type block text placeholder</p>
</div>
HTML;

        $data = [
            'title'      => 'Other Payment Email Block',
            'identifier' => 'other-email-block',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    public function createSizeBlock()
    {
        $html = <<<HTML
<div class="size-block-container">
    <table class="size-table">
        <thead>
            <tr>
                <th class="placeholder"></th>
                <th class="size-head">XS</th>
                <th class="size-head">S</th>
                <th class="size-head">M</th>
                <th class="size-head">L</th>
                <th class="size-head">XL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="name">Size</td>
                <td class="size">3</td>
                <td class="size">5</td>
                <td class="size">7</td>
                <td class="size">8</td>
                <td class="size">10</td>
            </tr>
            <tr>
                <td class="name">Width</td>
                <td class="size">4</td>
                <td class="size">6</td>
                <td class="size">7</td>
                <td class="size">9</td>
                <td class="size">11</td>
            </tr>
            <tr>
                <td class="name">Length</td>
                <td class="size">12</td>
                <td class="size">13</td>
                <td class="size">14</td>
                <td class="size">15</td>
                <td class="size">16</td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
        $data = [
            'title'      => 'View Page Size Block',
            'identifier' => 'attribute-size-block',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    public function createCheckoutWarning()
    {
        $html = <<<HTML
<div class="checkout-warning-container">
    <p><b>WARNING:</b> Orders take about 30-35 days for processing and engraving. Shipping One or Two Day Air does not shorten the product processing and engraving time.</p>
</div>
HTML;
        $data = [
            'title'      => 'Checkout Warning',
            'identifier' => 'checkout-warning',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function createQtyDiscountBlock()
    {
        $html = <<<HTML
<table border="0" cellspacing="1" cellpadding="5">
    <tbody>
    <tr>
        <td class="style1" colspan="2">If you are interested in ordering 25 or more of the same product with the exact same engraving, you are eligible to receive the discounts listed below. The discounts will display automatically when you place your order.</td>
    </tr>
    </tbody>
</table>
<table border="1" cellspacing="1" cellpadding="5 align=">
    <tbody>
    <tr bgcolor="#999999">
        <td class="style2" width="50%">Quantity</td>
        <td class="style2" width="50%">Discount</td>
    </tr>
    <tr bgcolor="#efefef">
        <td class="style1">25 - 49</td>
        <td class="style1">5%</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td class="style1">50 - 74</td>
        <td class="style1">10%</td>
    </tr>
    <tr bgcolor="#efefef">
        <td class="style1">75 - 99</td>
        <td class="style1">15%</td>
    </tr>
    <tr bgcolor="#FFFFFF">
        <td class="style1">100 - 499</td>
        <td class="style1">20%</td>
    </tr>
    <tr bgcolor="#efefef">
        <td class="style1">500+</td>
        <td class="style1">25%</td>
    </tr>
    </tbody>
</table>
HTML;

        $data = [
            'title'      => 'Quantity Discount',
            'identifier' => 'quantity-discount',
            'content'    => $html,
            'is_active'  => true
        ];

        return $data;
    }


    /*****************************************************
     *                 CMS: Pages                        *
     *****************************************************/

    /**
     * This function will return the Donation Page data inside an array.
     *
     * @return array
     */
    private function createDonationPage()
    {
        $html   = <<<HTML
<p class="text">Donations Page coming soon...</p>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'Donations Page',
            'page_layout'       => '1column',
            'identifier'        => 'donations',
            'content_heading'   => 'Donations Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the About Us Page data inside an array.
     *
     * @return array
     */
    private function createAboutUsPage()
    {
        $html   = <<<HTML
<p class="text">About Us Page coming soon...</p>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'About Us Page',
            'page_layout'       => '1column',
            'identifier'        => 'about-us',
            'content_heading'   => 'About Us Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the About Us Page data inside an array.
     *
     * @return array
     */
    private function createWhyWearPage()
    {
        $html   = <<<HTML
<p class="text">Why Wear Page coming soon...</p>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'Why Wear Page',
            'page_layout'       => '1column',
            'identifier'        => 'why-wear',
            'content_heading'   => 'Why Wear Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the About Us Page data inside an array.
     *
     * @return array
     */
    private function createFaqPage()
    {
        $html   = <<<HTML
<div class="faq-container">
    <div class="title">
        <h1>Frequently Asked Questions</h1>
    </div>
    <p class="instructions">*Click on a question to expand and display the answer.</p>
    <div class="content">
        <div class="row closed" title="Click to expand">
            <p class="question">This is a sample question?</p>
            <p class="answer">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eu velit ac turpis tempor
             varius ut quis ipsum. Aliquam fermentum, est a pellentesque ultricies, ipsum ligula venenatis tortor,
              ac porttitor est nulla cursus enim. Proin vitae aliquam massa, sed bibendum metus. </p>
        </div>
        <div class="row closed" title="Click to expand">
            <p class="question">This is another sample question?</p>
            <p class="answer">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eu velit ac turpis tempor
             varius ut quis ipsum. Aliquam fermentum, est a pellentesque ultricies, ipsum ligula venenatis tortor,
              ac porttitor est nulla cursus enim. Proin vitae aliquam massa, sed bibendum metus. </p>
        </div>
        <div class="row closed" title="Click to expand">
            <p class="question">This is yet another sample question?</p>
            <p class="answer">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eu velit ac turpis tempor
             varius ut quis ipsum. Aliquam fermentum, est a pellentesque ultricies, ipsum ligula venenatis tortor,
              ac porttitor est nulla cursus enim. Proin vitae aliquam massa, sed bibendum metus. </p>
        </div>
        <div class="row closed" title="Click to expand">
            <p class="question">This is the last sample question?</p>
            <p class="answer">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eu velit ac turpis tempor
             varius ut quis ipsum. Aliquam fermentum, est a pellentesque ultricies, ipsum ligula venenatis tortor,
              ac porttitor est nulla cursus enim. Proin vitae aliquam massa, sed bibendum metus. </p>
        </div>
    </div>
</div>
<script type="text/javascript" xml="space">// <![CDATA[
require(['jquery', 'jquery/ui'], function($) {
    $(function() {
        $('.row').click(function() {
            if($(this).hasClass('closed')) {
                $(this).removeClass('closed').addClass('open').find('.answer').slideDown('fast');
            }
            else {
                $(this).removeClass('open').addClass('closed').find('.answer').slideUp('fast');
            }
        })
    });
});
// ]]>
</script>
HTML;
        $layout = <<<XML
<referenceContainer name="sidebar.additional">
   <block class="Magento\Cms\Block\Block" name="faq-links-container">
       <arguments>
            <argument name="block_id" xsi:type="string">faq-links</argument>
       </arguments>
   </block>
   <referenceBlock name="catalog.compare.sidebar" remove="true" />
   <referenceBlock name="wishlist_sidebar" remove="true" />
</referenceContainer>
XML;

        $data = [
            'title'             => 'FAQ Page',
            'page_layout'       => '2columns-left',
            'identifier'        => 'faq',
            'content_heading'   => 'FAQ Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the Customer Satisfaction Page data inside an array.
     *
     * @return array
     */
    private function createSatisfactionPage()
    {
        $html   = <<<HTML
<p class="text">Customer Satisfaction Page coming soon...</p>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'Customer Satisfaction Page',
            'page_layout'       => '1column',
            'identifier'        => 'customer-satisfaction',
            'content_heading'   => 'Customer Satisfaction Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the Customer Satisfaction Page data inside an array.
     *
     * @return array
     */
    private function createTermsAndConditionsPage()
    {
        $html   = <<<HTML
<p class="text">Terms and Conditions Page coming soon...</p>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'Terms and Conditions Page',
            'page_layout'       => '1column',
            'identifier'        => 'terms-conditions',
            'content_heading'   => 'Terms and Conditions Page',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the Customer Satisfaction Page data inside an array.
     *
     * @return array
     */
    private function createReflectionsPage()
    {
        $html   = <<<HTML

HTML;
        $layout = <<<XML
<referenceContainer name="content">
   <block class="MemorialBracelets\ReviewAdditions\Block\Reflections" name="reflections.block" 
          template="MemorialBracelets_ReviewAdditions::reflections/list.phtml"/>
</referenceContainer>
XML;

        $data = [
            'title'             => 'Reflections',
            'page_layout'       => '1column',
            'identifier'        => 'reflections',
            'content_heading'   => '',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * @return array
     *
     */
    private function createQuantityDiscount()
    {
        $html   = <<<HTML
<div>
    <h3>Coming Soon...</h3>
</div>
HTML;
        $layout = <<<XML
XML;

        $data = [
            'title'             => 'Quantity Discount',
            'page_layout'       => '1column',
            'identifier'        => 'quantity-discount',
            'content_heading'   => '',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * @return string[]
     */
    private function updateHomepageWithNewNewsletter()
    {
        $html = <<<HTML
<div id="espot-newsletter-container">
    {{block class="Magento\\Cms\\Block\\Block" block_id="home-espot"}}
    <div class="item newsletter-block">
        {{block class="Magento\\Newsletter\\Block\\Subscribe"  name="form.subscribe.home" as="subscribe-home"  
        template="MemorialBracelets_Newsletter::subscribe.phtml"}}
    </div>
</div>
HTML;

        return ['content' => $html];
    }

    /**
     * This function will return the Customer Review Page data inside an array.
     *
     * @return array
     */
    private function createReviewsPage()
    {
        $html   = <<<HTML

HTML;
        $layout = <<<XML
<referenceContainer name="content">
   <block class="MemorialBracelets\ReviewAdditions\Block\Reviews" name="reviews.block" 
          template="MemorialBracelets_ReviewAdditions::reviews/list.phtml"/>
</referenceContainer>
XML;

        $data = [
            'title'             => 'Reviews',
            'page_layout'       => '1column',
            'identifier'        => 'reviews',
            'content_heading'   => '',
            'content'           => $html,
            'is_active'         => 1,
            'stores'            => [0],
            'sort_order'        => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }
}
