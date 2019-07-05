<?php

namespace Angecode\IproSoftware;

use Angecode\IproSoftware\Traits\HasApiMethods;
use Angecode\IproSoftware\AccessToken\NoneCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiException;

class IproSoftwareClient
{
    use HasApiMethods;

    /** @var \Angecode\IproSoftware\Contracts\HttpClient */
    protected $httpClient;

    /**
     * Client constructor.
     *
     * @param array $configurations
     *
     * @throws IproSoftwareApiException
     */
    public function __construct($configurations = [])
    {
        if (isset($configurations['requests_path_prefix']) && $configurations['requests_path_prefix']) {
            $this->setPathPrefix($configurations['requests_path_prefix']);
        }

        $this->tryCreateDefaultHttpClient($configurations);
    }

    /**
     * @param Contracts\HttpClient $httpClient
     *
     * @return IproSoftwareClient
     */
    public function setHttpClient(Contracts\HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param AccessTokenCacher $cacheManager
     *
     * @throws IproSoftwareApiException
     *
     * @return IproSoftwareClient
     */
    public function setAccessTokenCacheManager(AccessTokenCacher $cacheManager): self
    {
        if (! ($this->httpClient instanceof Contracts\HttpClient)) {
            throw new IproSoftwareApiException('A HttpClient must be set at the beginning.', 500);
        }
        $this->httpClient->setCacheManager($cacheManager);

        return $this;
    }

    /**
     * @param array $configurations
     *
     * @throws IproSoftwareApiException
     */
    protected function tryCreateDefaultHttpClient(array $configurations = [])
    {
        $clientCredentials = new ClientCredentials(
            $configurations['api_host'] ?? '',
            $configurations['client_id'] ?? '',
            $configurations['client_secret'] ?? ''
        );

        if (isset($configurations['oauth_endpoint'])) {
            $clientCredentials->tokenEndpoint = $configurations['oauth_endpoint'];
        }

        if ($clientCredentials->valid()) {
            $this->httpClient = new HttpClient($clientCredentials, $configurations['cache_manager'] ?? new NoneCacher(), $configurations);
        }
    }

    public function httpClient(): ?\Angecode\IproSoftware\Contracts\HttpClient
    {
        return $this->httpClient;
    }
}
