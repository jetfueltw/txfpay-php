<?php

namespace Jetfuel\Txfpay\Traits;

trait ResultParser
{
    /**
     * Parse JSON format response to array.
     *
     * @param string $response
     * @return array
     */
    public function parseResponse($response)
    {
        return json_decode($response, true);
    }

    /**
     * Parse XML format response to array.
     *
     * @param string $response
     * @return array
     */
    public function parseXMLResponse($response)
    {
        $result = new \SimpleXMLElement($response);
        return json_decode(json_encode($result), true);
    }
}
