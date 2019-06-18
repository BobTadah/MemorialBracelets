<?php

namespace MemorialBracelets\Social\Block;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Product as ProductHelper;

/**
 * Class Social
 *
 * @package MemorialBracelets\Social\Block
 */
class Social extends Template
{
    /** @var Registry */
    protected $coreRegistry;

    /**@var \Magento\Catalog\Helper\Product */
    protected $productHelper;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Social constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param ProductHelper $productHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        ProductHelper $productHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepository $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->productHelper = $productHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
    }

    /**
     * This will return the admin input value for:
     * Stores > Configuration > Catalog > Catalog > Home Page Favorite Options:
     * [Enabled]
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue('catalog/home_favorite/enabled', 'store');
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    /**
     * This will create an array of social links with an associated
     * array of all their details.
     *
     * @return array
     */
    public function getSocialIcons()
    {
        $product            = $this->getProduct();
        $productName        = $product->getName();
        $productImageUrl    = $this->productHelper->getImageUrl($product);
        $productUrl         = $this->productHelper->getProductUrl($product);
        $productDescription = $product->getShortDescription();
        $productType        = $product->getTypeId();

        $dataArray = [
            'twitter' => [
                'title' => 'Share on Twitter',
                'url' => $this->generateTwitterLink(
                    $productType,
                    $productName,
                    $productUrl,
                    array(),
                    null,
                    array(),
                    null,
                    '&amp;'
                ),
                'icon-class' => 'icon-twitter-circle',
                'target' => '_blank'
            ],
            'facebook' => [
                'title' => 'Share on Facebook',
                'url' => $this->generateFacebookLink(
                    $productUrl,
                    $productImageUrl,
                    $productName,
                    $productDescription,
                    '&amp;'
                ),
                'icon-class' => 'icon-facebook-circle',
                'target' => '_blank'
            ],
            'pinterest' => [
                'title' => 'Share on Pinterest',
                'url' => $this->generatePinterestLink(
                    $productImageUrl,
                    $productUrl,
                    $productName,
                    "\n\n" . $productDescription . '&amp;'
                ),
                'icon-class' => 'icon-pinterest-circle',
                'target' => '_blank'
            ],
            'googleplus' => [
                'title' => 'Share on Google Plus',
                'url' => $this->generateGooglePlusLink(
                    $productUrl,
                    '&amp;'
                ),
                'icon-class' => 'icon-google-plus-circle',
                'target' => '_blank'
            ],
            'email' => [
                'title' => 'Email to a Friend',
                'url' => $this->productHelper->getEmailToFriendUrl($product),
                'icon-class' => 'icon-email-circle',
                'target' => '_self'
            ],
            'print' => [
                'title' => 'Print',
                'url' => 'javascript:window.print();',
                'icon-class' => 'icon-print-circle',
                'target' => '_self'
            ],
        ];

        return $dataArray;
    }

    /**
     * @param $productType
     * @param string|null $text Text content of post
     * @param string|null $url Full URL to link to
     * @param array $hashtags Hashtags (without the # character)
     * @param string $via Appends "via @{$via}"
     * @param array $relatedAccounts Related accounts to suggest the user follow
     * @param string|null $inReplyTo ID of tweet this should be a reply to
     * @param string $querySeparator & by default.  May be set to &amp; when going to be output in HTML
     * @return string URL to target sharer
     */
    public function generateTwitterLink(
        $productType,
        $text = null,
        $url = null,
        array $hashtags = array(),
        $via = null,
        array $relatedAccounts = array(),
        $inReplyTo = null,
        $querySeparator = '&'
    ) {
        $intentUrl = 'https://twitter.com/intent/tweet';

        $params = array();
        if (!is_null($text)) {
            if ($productType == 'name') {
                $params['text'] = 'At Memorial Bracelets website remembering ' . $text . '. To order your engraved bracelet or dog tag visit: ';
            } else {
                $params['text'] = 'Memorial Bracelets website offers ' . $text . '. To create your own bracelet or dog tag visit: ';
            }
        }
        if (!is_null($url)) {
            $params['url'] = $url;
        }
        if (!empty($hashtags)) {
            $params['hashtags'] = implode(',', $hashtags);
        }
        if (!is_null($via)) {
            $params['via'] = $via;
        }
        if (!empty($relatedAccounts)) {
            $params['related'] = implode(',', $relatedAccounts);
        }
        if (!is_null($inReplyTo)) {
            $params['in-reply-to'] = $inReplyTo;
        }

        return $intentUrl . '?' . http_build_query($params, null, $querySeparator);
    }

    /**
     * @param string $url
     * @param string|null $imageUrl
     * @param string|null $title
     * @param string|null $summary
     * @param string $querySeparator & by default.  May be set to &amp; when going to be output in HTML
     *
     * @return string
     */
    public function generateFacebookLink(
        $url,
        $imageUrl = null,
        $title = null,
        $summary = null,
        $querySeparator = '&'
    ) {
        $linkPrefix = 'https://www.facebook.com/sharer.php';

        $params = array('s' => 100);
        $params['p[url]'] = $url;

        if (!is_null($imageUrl)) {
            $params['p[images][0]'] = $imageUrl;
        }
        if (!is_null($title)) {
            $params['p[title]'] = $title;
        }
        if (!is_null($summary)) {
            $params['p[summary]'] = $summary;
        }

        return $linkPrefix . '?' . http_build_query($params, null, $querySeparator);
    }

    /**
     * @param $image
     * @param $url
     * @param string|null $description
     * @param string $querySeparator & by default.  May be set to &amp; when going to be output in HTML
     *
     * @return string
     */
    public function generatePinterestLink(
        $image,
        $url,
        $description = null,
        $querySeparator = '&'
    ) {
        $linkPrefix = 'https://pinterest.com/pin/create/button/';

        $params = array(
            'media' => $image,
            'url' => $url,
        );

        if (!is_null($description)) {
            $params['description'] = $description;
        }

        return $linkPrefix . '?' . http_build_query($params, null, $querySeparator);
    }

    /**
     * @param $url
     * @param string $querySeparator & by default.  May be set to &amp; when going to be output in HTML
     *
     * @return string
     */
    public function generateGooglePlusLink($url, $querySeparator = '&')
    {
        $linkPrefix = 'https://plus.google.com/share';

        $params = array(
            'url' => $url
        );

        return $linkPrefix . '?' . http_build_query($params, null, $querySeparator);
    }

    /**
     * @param $productName
     * @param $productUrl
     * @param $senderName
     * @return string
     */
    public function getEmailWording1($productName, $productUrl, $senderName)
    {
        $text = '';
        $pType = $this->getProductType($productName);

        if ($pType == 'name') {
            $text .= $senderName . ' is remembering: <a href="' . $productUrl . '">' . $productName . '</a>';
        } else {
            $text .= $senderName . ' wants to share this product with you: <a href="' . $productUrl . '">' . $productName . '</a>';
        }

        return $text;
    }

    /**
     * @param $productName
     * @return string
     */
    public function getEmailWording2($productName)
    {
        $text = '';
        $pType = $this->getProductType($productName);

        if ($pType == 'name') {
            $text .= '<p>Memorial Bracelets helps keep the memories of victims and heroes of terrorism and loved ones alive with engraved bracelets and dog tags.  Click on the name of the person being remembered to order your own custom engraved bracelet or dog tag, or to write a <a href="https://production.mb.dev.briteskies.com/reflections/">Reflection</a> of your favorite memory.</p>
                      <br />
                      <p>Thank you again for your support.</p>
                      <p>-Rob</p>';
        } else {
            $text .= '<p>Memorial Bracelets helps keep the memories of victims and heroes of terrorism and loved ones alive with engraved bracelets and dog tags.  Click on the product description above to order your own custom engraved bracelet or dog tag.</p>
                      <p>To share the story of the person you are remembering, please post it on our <a href="https://www.facebook.com/MemorialBracelets/">Facebook page</a>  or write a  <a href="https://production.mb.dev.briteskies.com/reflections/">Reflection</a> if the person is listed on the Memorial Bracelets website.</p>
                      <br />
                      <p>Thank you again for your support.</p>
                      <p>-Rob</p>';
        }

        return $text;
    }

    /**
     * this function will load a product from the repo by name and
     * return its product type.
     * @param $productName
     * @return null|string
     */
    public function getProductType($productName)
    {
        $productType = '';

        $search = $this->searchCriteriaBuilder->addFilter(
            'name',
            $productName,
            'eq'
        )->create();

        $product = $this->productRepository->getList($search)->getItems();
        foreach ($product as $p) {
            $productType = $p->getTypeId();
            break;
        }

        return $productType;
    }
}
