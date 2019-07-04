<?php

namespace Angecode\IproSoftware\DTOs;

class ClientCredentials
{

    /** @var string */
    public $apiHost;

    /** @var string */
    public $tokenEndpoint;

    /** @var string */
    public $clientId;

    /** @var string */
    public $clientSecret;

    /**
     * ClientCredentials constructor.
     * @param string $apiHost
     * @param string $clientId
     * @param string $clientSecret
     * @param string $tokenEndpoint
     */
    public function __construct(
        string $apiHost,
        string $clientId,
        string $clientSecret,
        string $tokenEndpoint = '/oauth/2.0/token'
    )
    {
        $this->apiHost = $apiHost;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->tokenEndpoint = $tokenEndpoint;
    }

    public function valid() {
        return strlen($this->apiHost) && strlen($this->clientId) && strlen($this->clientSecret);
    }

}