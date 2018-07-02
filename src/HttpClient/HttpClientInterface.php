<?php

namespace Jetfuel\Ghfpay\HttpClient;

interface HttpClientInterface
{
    /**
     * HttpClientInterface constructor.
     *
     * @param string $baseUrl
     */
    public function __construct($baseUrl);

    /**
     * GET request.
     *
     * @param string $uri
     * @param array $data
     * @return string
     */
    public function get($uri, array $data);

    /**
     * POST request.
     *
     * @param string $uri
     * @param array $data
     * @return string
     */
    public function post($uri, array $data);
}
