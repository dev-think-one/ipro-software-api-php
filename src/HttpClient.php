<?php

namespace IproSoftwareApi;

use BadMethodCallException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use IproSoftwareApi\Contracts\AccessToken as AccessTokenInterface;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\DTOs\ClientCredentials;
use IproSoftwareApi\Exceptions\IproSoftwareApiAccessTokenException;
use IproSoftwareApi\Exceptions\IproSoftwareApiException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpClient.
 *
 * @method get($path = '', array $options = [])
 * @method post($path = '', array $options = [])
 * @method put($path = '', array $options = [])
 * @method delete($path = '', array $options = [])
 * @method head($path = '', array $options = [])
 * @method patch($path = '', array $options = [])
 */
class HttpClient implements Contracts\HttpClient
{
    /**
     * HTTP Methods.
     */
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_PATCH  = 'PATCH';

    const HTTP_METHODS = [
        self::HTTP_METHOD_GET,
        self::HTTP_METHOD_POST,
        self::HTTP_METHOD_PUT,
        self::HTTP_METHOD_DELETE,
        self::HTTP_METHOD_HEAD,
        self::HTTP_METHOD_PATCH,
    ];

    /**
     * @var string
     */
    protected $accessTokenClass;

    /**
     * @var AccessTokenCacher
     */
    protected $cacheManager;

    /**
     * @var null|callable
     */
    protected $responseFilter;

    /**
     * @var ClientCredentials
     */
    protected $clientCredentials;

    /**
     * @var ClientInterface
     */
    protected $http;

    /**
     * @var AccessTokenInterface
     */
    protected $accessToken;

    /**
     * HttpClient constructor.
     *
     * @param AccessTokenCacher $cacheManager
     * @param ClientCredentials $clientCredentials
     * @param array $httpConfiguration
     */
    public function __construct(ClientCredentials $clientCredentials, AccessTokenCacher $cacheManager, array $httpConfiguration = [])
    {
        $this->cacheManager      = $cacheManager;
        $this->clientCredentials = $clientCredentials;

        $this->accessTokenClass = $httpConfiguration['access_token_class']
            ?? \IproSoftwareApi\AccessToken\AccessToken::class;

        $configs = $httpConfiguration['client_conf'] ?? [];
        if (!isset($configs['base_uri'])) {
            $configs['base_uri'] = $this->clientCredentials->apiHost;
        }
        $this->http = new \GuzzleHttp\Client($configs);
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     *
     * @throws IproSoftwareApiAccessTokenException
     */
    public function __call($method, $arguments)
    {
        if (in_array(strtoupper($method), self::HTTP_METHODS)) {
            return $this->request(strtoupper($method), $arguments[0], $arguments[1] ?? []);
        }

        throw new BadMethodCallException('Method ' . $method . ' not found on ' . get_class() . '.', 500);
    }

    /**
     * @param AccessTokenCacher $cacheManager
     *
     * @return static
     */
    public function setCacheManager(AccessTokenCacher $cacheManager): static
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    /**
     * @param callable|null $responseFilter
     *
     * @return static
     */
    public function setResponseFilter(?callable $responseFilter): static
    {
        $this->responseFilter = $responseFilter;

        return $this;
    }

    /**
     * @param ClientInterface $http
     *
     * @return static
     */
    public function setHttp(ClientInterface $http): static
    {
        $this->http = $http;

        return $this;
    }

    /**
     * @param null $option
     *
     * @return mixed
     * @throws IproSoftwareApiException
     *
     */
    public function getConfig($option = null)
    {
        if (!is_null($this->http)) {
            return $this->http->getConfig($option);
        }

        throw new IproSoftwareApiException('Http client not specified');
    }

    /**
     * @param $method
     * @param string $path
     * @param array $options
     *
     * @return mixed|ResponseInterface
     * @throws IproSoftwareApiAccessTokenException
     *
     * @throws GuzzleException
     */
    public function request($method, $path = '', array $options = []): ResponseInterface
    {
        if (!$this->hasAccessToken()) {
            $this->generateAccessToken();
        }

        if (!isset($options['headers']['Authorization'])) {
            $options['headers']['Authorization'] = $this->accessToken->getAuthorizationHeader();
        }

        if (is_string($path) && !empty($path)) {
            if ($path[0] == '/') {
                $path = substr($path, 1);
            }
        }

        $response = $this->http->request($method, $path, $options);

        if (is_callable($this->responseFilter)) {
            $response = ($this->responseFilter)($response, $options, $path, $method);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        return $this->accessToken instanceof AccessTokenInterface && $this->accessToken->hasAccessToken();
    }

    /**
     * @return AccessTokenInterface
     * @throws IproSoftwareApiAccessTokenException
     *
     */
    public function generateAccessToken(): AccessTokenInterface
    {
        $this->accessToken = $this->cacheManager->get();

        // If empty access token or expired then make request for new token
        if (!$this->hasAccessToken()) {
            $this->receiveAccessToken();
        }

        return $this->accessToken;
    }

    /**
     * @throws IproSoftwareApiAccessTokenException
     */
    protected function receiveAccessToken()
    {
        $response = $this->http->post($this->clientCredentials->tokenEndpoint, [
            'auth' => [
                $this->clientCredentials->clientId,
                $this->clientCredentials->clientSecret,
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        if ($response->getStatusCode() != 200) {
            throw new IproSoftwareApiAccessTokenException($response, 'Get Access Token Error');
        }

        $this->accessToken = call_user_func(
            [$this->accessTokenClass, 'makeFromApiResponse'],
            $response
        );

        $this->cacheManager->put($this->accessToken);
    }
}
