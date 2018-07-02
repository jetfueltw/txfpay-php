<?php

namespace Jetfuel\Ghfpay;

use Jetfuel\Ghfpay\Traits\ResultParser;
use Jetfuel\Ghfpay\Constants\Channel;

class DigitalPayment extends Payment
{
    use ResultParser;

    const PRODUCT_ID_SCAN = '0100';
    const GOODS_INFO = 'goods_info';

    /**
     * DigitalPayment constructor.
     *
     * @param string $orgId
     * @param string $merchantId
     * @param string $secretKey
     * @param null|string $baseApiUrl
     */
    public function __construct($orgId, $merchantId, $secretKey, $baseApiUrl = null)
    {
        parent::__construct($orgId, $merchantId, $secretKey, $baseApiUrl);
    }

    /**
     * Create digital payment order.
     *
     * @param string $tradeNo
     * @param string $channel
     * @param float $amount
     * @param string $notifyUrl
     * @param string $returnUrl
     * @return array
     */
    public function order($tradeNo, $channel, $amount, $notifyUrl, $returnUrl)
    {
        if (($channel === Channel::WECHAT) || ($channel === Channel::ALIPAY)) {
            $channel = Channel::JDPAY;
        }
        $businessData = [
            'merno'     => $this->merchantId,
            'bus_no'    => $channel,
            'amount'    => $this->convertYuanToFen($amount),
            'goods_info'=> self::GOODS_INFO,
            'order_id'  => $tradeNo,
            'return_url'=> $returnUrl,
            'notify_url'=> $notifyUrl,
        ];
        $payload = $this->signPayload([
            'businessData'        => json_encode($businessData),
            'requestId'           => $tradeNo,
            'productId'           => self::PRODUCT_ID_SCAN,
        ]);

        return $this->parseResponse($this->httpClient->post('trade/invoke', $payload));
    }
}
