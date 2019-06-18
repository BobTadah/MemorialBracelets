<?php
namespace MemorialBracelets\ConversionTags\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class ConversionTags extends Template
{
    const PATH_GOOGLE_CONVERSION_ID = 'conversion_tags/google/conversion_id';
    const PATH_GOOGLE_CONVERSION_LABEL = 'conversion_tags/google/conversion_label';
    const PATH_TWITTER_PIXEL_ID = 'conversion_tags/twitter/pixel_id';
    const PATH_PINTEREST_TAG_ID = 'conversion_tags/pinterest/tag_id';
    const PATH_LINKEDIN_DATA_PARTNER_ID = 'conversion_tags/linkedin/data_partner_id';
    const PATH_FACEBOOK_PIXEL_ID = 'conversion_tags/facebook/pixel_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Returns all the scripts that should be included in the header of every page of the site.
     * @return string
     */
    public function getHeadTagScripts()
    {
        $scripts  = "";
        $scripts .= $this->getPinterestScript();

        return $scripts;
    }

    /**
     * Returns all the scripts that should be included in the body of every page of the site.
     * @return string
     */
    public function getBodyTagScripts()
    {
        $scripts  = "";
        $scripts .= $this->getTwitterScript();
        $scripts .= $this->getLinkedinScript();

        return $scripts;
    }

    /**
     * Returns all the scripts that should be included in the order confirmation page.
     * @return string
     */
    public function getPurchaseCompletionPageScripts()
    {
        $scripts  = "";
        $scripts .= $this->getFacebookScript();
        $scripts .= $this->getGoogleScript();

        return $scripts;
    }

    /**
     * Returns Google conversion id.
     * @return string
     */
    public function getGoogleConversionId()
    {
        $conversionId = $this->scopeConfig->getValue(
            self::PATH_GOOGLE_CONVERSION_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $conversionId;
    }

    /**
     * Returns Google conversion label.
     * @return string
     */
    public function getGoogleConversionLabel()
    {
        $conversionLabel = $this->scopeConfig->getValue(
            self::PATH_GOOGLE_CONVERSION_LABEL,
            ScopeInterface::SCOPE_STORE
        );

        return $conversionLabel;
    }

    /**
     * Returns Google tracking tag. This tag must be in the html body.
     * @return string
     */
    public function getGoogleScript()
    {
        $id = $this->getGoogleConversionId();
        $label = $this->getGoogleConversionLabel();

        if (!empty($id) && !empty($label)) {
            $script = "
            <!-- Google Code for Magento MB Product Purchase 17 Conversion Page -->
            <script type=\"text/javascript\">
            /* <![CDATA[ */
            var google_conversion_id = " . $id . ";
            var google_conversion_language = \"en\";
            var google_conversion_format = \"3\";
            var google_conversion_color = \"ffffff\";
            var google_conversion_label = \"" . $label . "\";
            var google_remarketing_only = false;
            /* ]]> */
            </script>
            <script type=\"text/javascript\" src=\"//www.googleadservices.com/pagead/conversion.js\">
            </script>
            <noscript>
            <div style=\"display:inline;\">
            <img height=\"1\" width=\"1\" style=\"border-style:none;\" alt=\"\" 
            src=\"//www.googleadservices.com/pagead/conversion/".$id."/?label=".$label."&amp;guid=ON&amp;script=0\"/>
            </div>
            </noscript>
            <!-- End Google Code for Magento MB Product Purchase 17 Conversion Page -->
            ";
        }

        return (isset($script)) ? $script : '';
    }

    /**
     * Returns Facebook Pixel ID.
     * @return string
     */
    public function getFacebookPixelId()
    {
        $pixelId = $this->scopeConfig->getValue(
            self::PATH_FACEBOOK_PIXEL_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $pixelId;
    }

    /**
     * Returns Facebook tracking tag. This tag must be in the html body.
     * @return string
     */
    public function getFacebookScript()
    {
        $id = $this->getFacebookPixelId();

        if (!empty($id)) {
            $script = "
                <!-- Facebook Pixel Code -->
                <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
                // Insert Your Facebook Pixel ID below. 
                fbq('init', ".$id.");
                fbq('track', 'PageView');
                </script>
                <!-- Insert Your Facebook Pixel ID below. --> 
                <noscript><img height=\"1\" width=\"1\" style=\"display:none\"
                src=\"https://www.facebook.com/tr?id=".$id."&amp;ev=PageView&amp;noscript=1\"
                /></noscript>
                <!-- End Facebook Pixel Code -->
            ";
        }

        return (isset($script)) ? $script : '';
    }

    /**
     * Returns Twitter pixel ID.
     * @return string
     */
    public function getTwitterPixelId()
    {
        $pixelId = $this->scopeConfig->getValue(
            self::PATH_TWITTER_PIXEL_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $pixelId;
    }

    /**
     * Returns Twitter tracking tag. This tag must be in the html body.
     * @return string
     */
    public function getTwitterScript()
    {
        $id = $this->getTwitterPixelId();

        if (!empty($id)) {
            $script = "
            <!-- Twitter universal website tag code --> 
            <script> 
            !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments); 
            },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js', 
            a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script'); 
            // Insert Twitter Pixel ID and Standard Event data below 
            twq('init','".$id."'); 
            twq('track','PageView'); 
            </script> 
            <!-- End Twitter universal website tag code --> 
            ";
        }

        return (isset($script)) ? $script : '';
    }

    /**
     * Returns Pinterest Tag ID.
     * @return string
     */
    public function getPinterestTagId()
    {
        $tagId = $this->scopeConfig->getValue(
            self::PATH_PINTEREST_TAG_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $tagId;
    }

    /**
     * Returns Pinterest tracking tag. This tag must be in the html head.
     * @return string
     */
    public function getPinterestScript()
    {
        $id = $this->getPinterestTagId();

        if (!empty($id)) {
            $script = "
                <!-- Pinterest Pixel Base Code -->
                <script type=\"text/javascript\">
                !function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(
                Array.prototype.slice.call(arguments))};var
                n=window.pintrk;n.queue=[],n.version=\"3.0\";var
                t=document.createElement(\"script\");t.async=!0,t.src=e;var
                r=document.getElementsByTagName(\"script\")[0];r.parentNode.insertBefore(t,r)
                }}(\"https://s.pinimg.com/ct/core.js\");
                pintrk('load', ".$id.");
                pintrk('page');
                </script>
                <noscript>
                <img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" 
                src=\"https://ct.pinterest.com/v3/?tid=".$id."&noscript=1\" />
                </noscript>
                <!-- End Pinterest Pixel Base Code -->
            ";
        }

        return (isset($script)) ? $script : '';
    }

    /**
     * Returns Linkedin data partner ID.
     * @return string
     */
    public function getLinkedinDataPartnerId()
    {
        $partnerId = $this->scopeConfig->getValue(
            self::PATH_LINKEDIN_DATA_PARTNER_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $partnerId;
    }

    /**
     * Returns Linkedin tracking tag. This tag must be in the html body.
     * @return string
     */
    public function getLinkedinScript()
    {
        $id = $this->getLinkedinDataPartnerId();

        if (!empty($id)) {
            $script = "
                <!-- Linkedin Insight Tag Code -->
                <script type=\"text/javascript\">
                _linkedin_data_partner_id = \"".$id."\";
                </script><script type=\"text/javascript\"> 
                (function(){var s = document.getElementsByTagName(\"script\")[0];
                var b = document.createElement(\"script\");
                b.type = \"text/javascript\";b.async = true;
                b.src = \"https://snap.licdn.com/li.lms-analytics/insight.min.js\";
                s.parentNode.insertBefore(b, s);})();
                </script>
                <noscript>
                    <img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\" 
                    src=\"https://dc.ads.linkedin.com/collect/?pid=".$id."&fmt=gif\"/>      
                </noscript>
                <!-- End Linkedin Insight Tag Code -->
            ";
        }

        return (isset($script)) ? $script : '';
    }
}
