<?php

namespace Jetfuel\Ghfpay;
use Jetfuel\Ghfpay\Traits\ResultParser;
use Jetfuel\Ghfpay\Constants\Bank;

class BankPayment extends Payment
{
    use ResultParser;

    const PRODUCT_ID_BANK = '0500';
    const GOODS_INFO = 'goods_info';

    /**
     * BankPayment constructor.
     *
     * @param string $merchantId
     * @param string $secretKey
     * @param null|string $baseApiUrl
     */
    public function __construct($merchantId, $secretKey, $baseApiUrl = null)
    {
        parent::__construct($merchantId, $secretKey, $baseApiUrl);
    }

    /**
     * Create bank payment order.
     *
     * @param string $tradeNo
     * @param string $bank
     * @param float $amount
     * @param string $notifyUrl
     * @param string $returnUrl
     * @return string
     */
    public function order($tradeNo, $bank, $amount, $notifyUrl, $returnUrl)
    {
        $businessData = [
            'merno'     => $this->merchantId,
            'bus_no'    => '0499',
            'amount'    => $this->convertYuanToFen($amount),
            'goods_info'=> self::GOODS_INFO,
            'order_id'  => $tradeNo,
            'cardname' => $bank,
            'bank_code' => Bank::BANK_CODE[$bank],
            //'cardno'   => '1111111111',
            'return_url'=> $returnUrl,
            'notify_url'=> $notifyUrl,
            'card_type' => 1,
            'channelid' => 1,
        ];
        $payload = $this->signPayload([
            'businessData'        => json_encode($businessData),
            'requestId'           => $tradeNo,
            'productId'           => self::PRODUCT_ID_BANK,
        ]);
        
        $response = $this->parseResponse($this->httpClient->post('trade/invoke', $payload));
        if (isset($response['result'])) 
        {
           $html = json_decode($response['result'],true)['url'];
           return '<script> window.location = "'.$html.'"; </script>';
        }

        return null;
    }
}
