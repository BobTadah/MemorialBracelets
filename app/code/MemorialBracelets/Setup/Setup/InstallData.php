<?php

namespace MemorialBracelets\Setup\Setup;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * InstallData constructor.
     * @param PageFactory $pageFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->pageFactory  = $pageFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** array of all out new static blocks. */
        $cmsBlocks = [
            $this->getTextBanner(),
            $this->getEspotBlock(),
            $this->getBottomContentBlock()
        ];

        /** Cycle static blocks and attempt to save/update them. */
        foreach ($cmsBlocks as $data) {
            $this->saveCmsBlock($data);
        }

        /** Update the base homepage with new data. */
        $this->saveHomePage($this->getHomePageData());

        $setup->endSetup();
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
        $this->pageFactory->create()
            ->load(2)
            ->setContent($data['content'])
            ->setPageLayout($data['page_layout'])
            ->setLayoutUpdateXml($data['layout_update_xml'])
            ->save();
    }

    /**
     * This function will return the homepage data inside an array.
     *
     * @return array
     */
    private function getHomePageData()
    {
        $html = <<<HTML
<div id="espot-newsletter-container">
    {{block class="Magento\\Cms\\Block\\Block" block_id="home-espot"}}
    <div class="item newsletter-block">
        {{block class="Magento\\Newsletter\\Block\\Subscribe"  name="form.subscribe.home" as="subscribe-home"  
        template="subscribe.phtml"}}
    </div>
</div>
HTML;
        $layout = <<<XML
<referenceContainer name="content">
    <block class="Magento\Cms\Block\Block" name="home-text-banner" before="-">
        <arguments>
            <argument name="block_id" xsi:type="string">home-text-banner</argument>
        </arguments>
    </block>
    <block class="Magento\Cms\Block\Block" name="home-bottom-content" after="-">
        <arguments>
            <argument name="block_id" xsi:type="string">home-bottom-content</argument>
        </arguments>
    </block>
</referenceContainer>
XML;

        $data =  [
            'title' => 'Home Page',
            'page_layout' => '1column',
            'meta_keywords' => 'Page keywords',
            'meta_description' => 'Page description',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => $html,
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0,
            'layout_update_xml' => $layout
        ];

        return $data;
    }

    /**
     * This function will return the homepage text banner data inside an array.
     *
     * @return array
     */
    private function getTextBanner()
    {
        $html = <<<HTML
<div id="home-text-banner">
    <div class="text-container">
        <p class="text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus mollis elementum odio eu
         sagittis. In tempus sapien in ex convallis auctor. Vestibulum vehicula blandit diam ut consequat.</p>
    </div>
</div>
HTML;

        $data = [
            'title' => 'Home Text Banner',
            'identifier' => 'home-text-banner',
            'content' => $html,
            'is_active' => true
        ];

        return $data;
    }

    /**
     * This function will return the homepage espot block data inside an array.
     *
     * @return array
     */
    private function getEspotBlock()
    {
        $html = <<<HTML
<div class="item espot">
    <a title="Espot 1" href="#"><img alt="Espot 1" src="{{media url=memorial/placeholder.png}}"/></a>
</div>
<div class="item espot">
    <a title="Espot 2" href="#"><img alt="Espot 2" src="{{media url=memorial/placeholder.png}}"/></a>
</div>
HTML;

        $data = [
            'title' => 'Home Dual Espot',
            'identifier' => 'home-espot',
            'content' => $html,
            'is_active' => true
        ];

        return $data;
    }

    /**
     * This function will return the homepage bottom content data inside an array.
     *
     * @return array
     */
    private function getBottomContentBlock()
    {
        $html = <<<HTML
<div id="home-bottom-content">
    <div class="content-container">
        <div id="box-first" class="box">
            <div class="ribbon-wrapper"><h3 class="title">Special Announcements</h3></div>
            <div class="content">
                <p class="paragraph">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur id augue
                 porttitor, efficitur turpis quis, viverra ligula. Phasellus fringilla ante eget tortor accumsan
                  dictum et vitae tellus. Nullam ullamcorper purus sit amet eleifend convallis.</p>
                <p class="paragraph">Cras pulvinar venenatis malesuada. Aenean libero urna, porttitor non velit quis,
                 venenatis porta tortor. Mauris cursus eleifend</p>
            </div>
        </div>
        <div class="multi-box-container">
            <div id="box-second" class="box">
                <div class="ribbon-wrapper"><h3 class="title">Reviews</h3></div>
                <div class="content">
                    <div class="left">
                        <img title="image title" alt="image alt" src="{{media url=memorial/placeholder.png}}"/>
                    </div>
                    <div class="right">
                        <h4 class="small-title">FirstName | Date</h4>
                        <p class="paragraph">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur id augu
                        e porttitor, efficitur turpis quis, viverra ligula. Phasellus fringilla ante eget tortor.</p>
                    </div>
                </div>
            </div>
            <div id="box-third" class="box">
                <div class="ribbon-wrapper"><h3 class="title">Reflections</h3></div>
                <div class="content">
                    <div class="main">
                        <h4 class="small-title">FirstName | Date</h4>
                        <p class="paragraph">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur id augue
                         porttitor, efficitur turpis quis, viverra ligula. Phasellus fringilla ante eget tortor accumsan
                          dictum et vitae tellus.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

        $data = [
            'title' => 'Home Bottom Content',
            'identifier' => 'home-bottom-content',
            'content' => $html,
            'is_active' => true
        ];

        return $data;
    }
}
