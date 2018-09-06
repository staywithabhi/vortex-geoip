<?php
namespace Vortex\GeoIP\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vortex\GeoIP\Model\RetrcictionType;
class Restrict implements ObserverInterface
{
    protected $helper;
    protected $geoIpCountryHelper;
    protected $request;
    protected $isDenied = false;
    const XML_PATH_VORTEX_GEOIP_STATUS  = 'vortex_geoip/geoip_config/activate_geoip';
    const XML_VORTEX_GEOIP_RANGE_ENABLED = 'vortex_geoip/geo_restrict_range/active_range';
    const XML_PATH_VORTEX_RESTRICTION_RULE  = 'vortex_geoip/geoip_config/rule_type';
    const XML_PATH_VORTEX_GEOIP_COUNTRIES  = 'vortex_geoip/geoip_config/countries';
    public function __construct(
        \Vortex\GeoIP\Helper\Data $helper,
         \Vortex\GeoIP\Helper\Countrylib $countryhelper,
        \Magento\Framework\App\RequestInterface $request,
         \Psr\Log\LoggerInterface $logger,
         \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag
    ) {
        $this->helper = $helper;
        $this->countryhelper=$countryhelper;
        $this->request = $request;
         $this->_logger = $logger;
          $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    $_ismoduleEnabled=$this->helper->getStoreSpecificValue(self::XML_PATH_VORTEX_GEOIP_STATUS);
    $_iprangeEnabled=$this->helper->getStoreSpecificValue(self::XML_VORTEX_GEOIP_RANGE_ENABLED);
    $_restrictionType=$this->helper->getStoreSpecificValue(self::XML_PATH_VORTEX_RESTRICTION_RULE);
    $_countriesSelected=$this->helper->getStoreSpecificValue(self::XML_PATH_VORTEX_GEOIP_COUNTRIES);
    $geoplugin = $this->countryhelper;
    $country_code=$geoplugin->countryCode;
        if (!$_ismoduleEnabled) {
            return $this;
            }

        if ($this->request->isAjax()) {
            return $this;
            }

      $_countriesSelected=explode(",",$_countriesSelected);
        if (!$_countriesSelected || empty($_countriesSelected)) {
            return $this->isDenied;
        }
        else
        {
        $geoplugin = $this->countryhelper;
        $geoplugin->locate();
        $country_name=$geoplugin->countryName;
        $country_code=$geoplugin->countryCode;
        switch  ($_restrictionType) {
            case RetrcictionType::ALLOW:
                if (!in_array($country_code, $_countriesSelected)) {
                    $this->isDenied = true;
                }
                break;
            case RetrcictionType::DENY:
                if (in_array($country_code, $_countriesSelected)) {
                    $this->isDenied = true;
                }
                break;
            default:
                return $this->isDenied;
        }

    }
            if( $_iprangeEnabled){
            $this->checkIpList($geoplugin->ip);
                }
            if ($this->isDenied === true) {
                $this->denyAccess($observer);
            }
            return $this;

    }

    protected function checkIpList($currentLocation)
    {
        $customerIp = $currentLocation;
        $ipBlackList = $this->helper->getIpBlackList();
        if ($ipBlackList) {
            foreach ($ipBlackList as $ipaddress) {
                $ipaddress = str_replace(['*', '.'], ['\d+', '\.'], $ipaddress);
                if (preg_match("/^{$ipaddress}$/", $customerIp)) {
                    $this->isDenied = true;
                    break;
                }
            }
        }

        $ipWhiteList = $this->helper->getIpWhiteList();
        if ($ipWhiteList) {
            foreach ($ipWhiteList as $ipaddress) {
                $ipaddress = str_replace(['*', '.'], ['\d+', '\.'], $ipaddress);
                if (preg_match("/^{$ipaddress}$/", $customerIp)) {
                    $this->isDenied = false;
                    break;
                }
            }
        }

        return $this->isDenied;
    }

        protected function denyAccess($observer)
    {
        $response = $observer->getResponse();
        $response->clearBody()->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_403);
        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
    }
    }