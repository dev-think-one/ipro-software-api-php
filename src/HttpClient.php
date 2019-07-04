<?php


namespace Angecode\IproSoftware;


use Angecode\IproSoftware\Contracts\AccessToken as AccessTokenInterface;
use Angecode\IproSoftware\Contracts\AccessToken;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException;
use BadMethodCallException;
use Carbon\Carbon;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpClient
 * @package Angecode\IproSoftware
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
     * HTTP Methods
     */
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD = 'HEAD';
    const HTTP_METHOD_PATCH = 'PATCH';

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
     * @param AccessTokenCacher $cacheManager
     * @param ClientCredentials $clientCredentials
     * @param array $httpConfiguration
     */
    public function __construct(ClientCredentials $clientCredentials, AccessTokenCacher $cacheManager, array $httpConfiguration = [])
    {
        $this->cacheManager = $cacheManager;
        $this->clientCredentials = $clientCredentials;

        $this->accessTokenClass = $httpConfiguration['access_token_class']
            ?? \Angecode\IproSoftware\AccessToken\AccessToken::class;

        $configs = $httpConfiguration['client_conf'] ?? [];
        if (!isset($configs['base_uri'])) {
            $configs['base_uri'] = $this->clientCredentials->apiHost;
        }
        $this->http = new \GuzzleHttp\Client($configs);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed|ResponseInterface
     * @throws IproSoftwareApiAccessTokenException
     * @throws GuzzleException
     */
    public function __call($method, $arguments)
    {
        if (in_array(strtoupper($method), self::HTTP_METHODS)) {
            return $this->request(self::HTTP_METHOD_GET, $arguments[0], $arguments[1] ?? []);
        }

        throw new BadMethodCallException("Method " . $method . " not found on " . get_class() . ".", 500);
    }

    /**
     * @param AccessTokenCacher $cacheManager
     * @return self
     */
    public function setCacheManager(AccessTokenCacher $cacheManager): Contracts\HttpClient
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    /**
     * @param ClientInterface $http
     */
    public function setHttp(ClientInterface $http): void
    {
        $this->http = $http;
    }

    /**
     * @param $method
     * @param string $path
     * @param array $options
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     * @throws IproSoftwareApiAccessTokenException
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

        return $this->http->request($method, $path, $options);
    }

    /**
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        return ($this->accessToken instanceof AccessTokenInterface && $this->accessToken->hasAccessToken());
    }

    /**
     * @return AccessTokenInterface
     * @throws IproSoftwareApiAccessTokenException
     */
    public function generateAccessToken(): AccessTokenInterface
    {
        $this->accessToken = $this->cacheManager->get();

        // If empty access token or expired then make request for new token
        if (!$this->hasAccessToken()) {
            $response = $this->http->post($this->clientCredentials->tokenEndpoint, [
                'auth' => [
                    $this->clientCredentials->clientId,
                    $this->clientCredentials->clientSecret
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            if ($response->getStatusCode() != 200) {
                throw new IproSoftwareApiAccessTokenException($response, 'Get Access Token Error');
            }

            $responseBody = json_decode($response->getBody(), true);

            if (!isset($responseBody['access_token'])
                || !isset($responseBody['token_type'])
                || !isset($responseBody['expires_in'])
                || $responseBody['expires_in'] < 60 * 5
            ) {
                throw new IproSoftwareApiAccessTokenException($response, 'Get Access Token: Not Valid Response');
            }

            $expiresAt = Carbon::now()->addSeconds($responseBody['expires_in']);
            $responseBody['expires_at'] = $expiresAt->toString();

            $this->accessToken = call_user_func(
                [$this->accessTokenClass, 'makeFromJson'],
                json_encode($responseBody)
            );

            if (!($this->accessToken instanceof AccessToken)) {
                throw new IproSoftwareApiAccessTokenException(
                    $response,
                    'Get Access Token: Error while initialising'
                );
            }

            $this->cacheManager->put($this->accessToken);
        }

        return $this->accessToken;
    }
}