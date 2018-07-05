<?php

namespace Jetfuel\Txfpay\Traits;

use Jetfuel\Txfpay\Signature;

trait NotifyWebhook
{
    use ConvertMoney;
    /**
     * Verify notify request's signature.
     *
     * @param array $payload
     * @param $secretKey
     * @return bool
     */
    public function verifyNotifyPayload(array $payload, $secretKey)
    {
        if (!isset($payload['sign_data'])) {
            return false;
        }

        $signature = $payload['sign_data'];

        unset($payload['sign_data']);
        
        return Signature::validate($payload, $secretKey, $signature);
    }

    /**
     * Verify notify request's signature and parse payload.
     *
     * @param array $payload
     * @param string $secretKey
     * @return array|null
     */
    public function parseNotifyPayload(array $payload, $secretKey)
    {
        if (!$this->verifyNotifyPayload($payload, $secretKey)) {
            return null;
        }
        $payload['amount'] = $this->convertFenToYuan($payload['amount']);
        return $payload;
    }

    /**
     * Response content for successful notify.
     *
     * @return string
     */
    public function successNotifyResponse()
    {
        return '0000';
    }
}
