<?php

namespace Jetfuel\Txfpay;

use Jetfuel\Txfpay\HttpClient\GuzzleHttpClient;
use Jetfuel\Txfpay\Traits\ConvertMoney;

class Payment
{
    use ConvertMoney;
    //const BASE_API_URL = 'http://118.31.38.147:18888/open-gateway/';
   //const BASE_API_URL = 'http://47.75.180.21:18888/open-gateway/';
   const BASE_API_URL = 'https://www.txpays.com/open-gateway/';
    const TIME_ZONE      = 'Asia/Shanghai';
    const TIME_FORMAT    = 'YmdHis';

      /**
     * @var string
     */
    protected $orgId;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var string
     */
    protected $baseApiUrl;

    /**
     * @var \Jetfuel\Txfpay\HttpClient\HttpClientInterface
     */
    protected $httpClient;

    /**
     * Payment constructor.
     *
     * @param string $orgId
     * @param string $merchantId
     * @param string $secretKey
     * @param null|string $baseApiUrl
     */
    protected function __construct($orgId, $merchantId, $secretKey, $baseApiUrl = null)
    {
        $this->orgId = $orgId;
        $this->merchantId = $merchantId;
        $this->secretKey = $secretKey;
        $this->baseApiUrl = $baseApiUrl === null ? self::BASE_API_URL : $baseApiUrl;

        $this->httpClient = new GuzzleHttpClient($this->baseApiUrl);
    }

    /**
     * Sign request payload.
     *
     * @param array $payload
     * @return array
     */
    protected function signPayload(array $payload)
    {
        $payload['orgId'] = $this->orgId;
        $payload['timestamp'] = $this->getCurrentTime();
        $payload['signData'] = Signature::generate($payload, $this->secretKey);

        return $payload;
    }

    /**
     * Get current time.
     *
     * @return string
     */
    protected function getCurrentTime()
    {
        return (new \DateTime('now', new \DateTimeZone(self::TIME_ZONE)))->format(self::TIME_FORMAT);
    }

}
