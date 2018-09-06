<?php
namespace Vortex\Geoip\Model;
use Magento\Framework\Option\ArrayInterface;
class RetrcictionType implements ArrayInterface
{
    const ALLOW = 1;
    const DENY = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::ALLOW, 'label' => __('Allow')],
            ['value' => self::DENY, 'label' => __('Deny')],
        ];
    }
}
