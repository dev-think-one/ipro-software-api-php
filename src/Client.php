<?php


namespace Angecode\IproSoftware;


use Angecode\IproSoftware\AccessToken\NoneCacher;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiException;
use Angecode\IproSoftware\Traits\HasApiMethods;

class Client
{

    use HasApiMethods;

    /** @var \Angecode\IproSoftware\Contracts\HttpClient */
    protected $httpClient;

    /**
     * Client constructor.
     * @param array $configurations
     * @throws IproSoftwareApiException
     */
    public function __construct($configurations = [])
    {

        if (isset($configurations['http_client'])) {
            $this->setHttpClient($configurations['http_client']);
        }
        $this->tryCreateDefaultHttpClient($configurations);
    }

    /**
     * @param Contracts\HttpClient $httpClient
     * @return Client
     */
    public function setHttpClient(Contracts\HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param AccessTokenCacher $cacheManager
     * @return Client
     */
    public function setAccessTokenCacheManager(AccessTokenCacher $cacheManager): self
    {
        $this->httpClient->setCacheManager($cacheManager);

        return $this;
    }

    /**
     * @param array $configurations
     * @throws IproSoftwareApiException
     */
    protected function tryCreateDefaultHttpClient(array $configurations)
    {
        $clientCredentials = new ClientCredentials($configurations['api_host'], $configurations['client_id'], $configurations['client_secret']);

        if (!$clientCredentials->valid()) {
            throw new IproSoftwareApiException('Fields api_host, client_id, client_secret are required');
        }

        $this->httpClient = new HttpClient($clientCredentials, $configurations['cache_manager'] ?? new NoneCacher(), $configurations);
    }

    public function httpClient(): \Angecode\IproSoftware\Contracts\HttpClient
    {
        return $this->httpClient;
    }

}