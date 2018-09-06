<?php

namespace Vortex\Geoip\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_VORTEX_GEOIP_RANGE_ENABLED = 'vortex_geoip/geo_restrict_range/active_range';

    const XML_VORTEX_GEOIP_RANGE_BLACKLIST = 'vortex_geoip/geo_restrict_range/blacklisted';

    const XML_VORTEX_GEOIP_RANGE_WHITELIST = 'vortex_geoip/geo_restrict_range/whitelisted';

    const IP_VORTEX_GEOIP_RANGE_REGEXP_DELIMITER = '/[\r?\n]+|[,?]+/';
    
    
     public function getStoreSpecificValue($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIpBlackList($storeId = null)
    {
        $rawIpList = $this->scopeConfig->getValue(
            self::XML_VORTEX_GEOIP_RANGE_BLACKLIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $ipList = array_filter((array) preg_split(self::IP_VORTEX_GEOIP_RANGE_REGEXP_DELIMITER, $rawIpList));

        return $ipList;
    }

    public function getIpWhiteList($storeId = null)
    {
        $rawIpList = $this->scopeConfig->getValue(
            self::XML_VORTEX_GEOIP_RANGE_WHITELIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $ipList = array_filter((array) preg_split(self::IP_VORTEX_GEOIP_RANGE_REGEXP_DELIMITER, $rawIpList));

        return $ipList;
    }
}
