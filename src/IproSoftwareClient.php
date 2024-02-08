<?php

namespace IproSoftwareApi;

use IproSoftwareApi\AccessToken\NoneCacher;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\DTOs\ClientCredentials;
use IproSoftwareApi\Exceptions\IproSoftwareApiException;
use IproSoftwareApi\Traits\HasApiMethods;

class IproSoftwareClient
{
    use HasApiMethods;

    /** @var \IproSoftwareApi\Contracts\HttpClient */
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
     * @return static
     */
    public function setHttpClient(Contracts\HttpClient $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param AccessTokenCacher $cacheManager
     *
     * @throws IproSoftwareApiException
     *
     * @return static
     */
    public function setAccessTokenCacheManager(AccessTokenCacher $cacheManager): static
    {
        if (!($this->httpClient instanceof Contracts\HttpClient)) {
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
            $configurations['api_host']      ?? '',
            $configurations['client_id']     ?? '',
            $configurations['client_secret'] ?? ''
        );

        if (isset($configurations['oauth_endpoint'])) {
            $clientCredentials->tokenEndpoint = $configurations['oauth_endpoint'];
        }

        if ($clientCredentials->valid()) {
            $this->httpClient = new HttpClient($clientCredentials, $configurations['cache_manager'] ?? new NoneCacher(), $configurations);
        }
    }

    public function httpClient(): ?\IproSoftwareApi\Contracts\HttpClient
    {
        return $this->httpClient;
    }
}
