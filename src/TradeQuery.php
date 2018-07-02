<?php

namespace Jetfuel\Ghfpay;

use Jetfuel\Ghfpay\Traits\ResultParser;

class TradeQuery extends Payment
{
    use ResultParser;

    const PRODUCT_ID_QUERY = '9701';

    /**
     * DigitalPayment constructor.
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
     * Find Order by trade number.
     *
     * @param string $tradeNo
     * @return array|null
     */
    public function find($tradeNo)
    {
        $businessData = [
            'order_id'  => $tradeNo,
        ];
        $payload = $this->signPayload([
            'businessData'        => json_encode($businessData),
            'requestId'           => $tradeNo,
            'productId'           => self::PRODUCT_ID_QUERY,
        ]);
        
        $order = $this->parseResponse($this->httpClient->post('query/invoke', $payload));
        
        if ($order['key'] !== '00' && $order['key'] !== '05') {
            return null;
        }

        $result = json_decode($order['result'], true);
        $result['amount'] = $this->convertFenToYuan( $result['amount']);
        $order['result'] = json_encode($result);
        
        return $order;
    }

    /**
     * Is order already paid.
     *
     * @param string $tradeNo
     * @return bool
     */
    public function isPaid($tradeNo)
    {
        $order = $this->find($tradeNo);

        if ($order === null || !isset($order['result']) || json_decode($order['result'], true)['payment_status'] !=='1'  ) {
            return false;
        }

        return true;
    }
}
