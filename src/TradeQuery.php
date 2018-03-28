<?php

namespace Jetfuel\Txfpay;

use Jetfuel\Txfpay\Traits\ResultParser;

class TradeQuery extends Payment
{
    use ResultParser;

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
        $payload = $this->signPayload([
            'orderNo' => $tradeNo,
        ]);

        $order = $this->parseResponse($this->httpClient->get($this->merchantId.'-'.$tradeNo, $payload));

        if ($order['respCode'] !== 'S0001') {
            return null;
        }

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

        if ($order === null || !isset($order['status']) || $order['status'] !== 'completed') {
            return false;
        }

        return true;
    }
}
