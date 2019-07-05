<?php

namespace Angecode\IproSoftware\AccessToken;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Angecode\IproSoftware\Contracts\AccessToken as AccessTokenInterface;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException;

class AccessToken implements AccessTokenInterface
{
    protected $accessToken;
    protected $tokenType;
    protected $expiresIn;
    protected $expiresAt;

    /**
     * AccessToken constructor.
     *
     * @param string|null $accessToken
     * @param string|null $tokenType
     * @param string|null $expiresIn
     * @param string|null $expiresAt
     */
    public function __construct(string $accessToken = null, string $tokenType = null, string $expiresIn = null, string $expiresAt = null)
    {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'expires_at' => $this->expiresAt,
        ];
    }

    public static function makeFromJson(string $json): ?AccessTokenInterface
    {
        $data = json_decode($json, true);
        if (is_array($data)) {
            $accessToken = new self(
                $data['access_token'] ?? null,
                $data['token_type'] ?? null,
                $data['expires_in'] ?? null,
                $data['expires_at'] ?? null
            );
        }

        return (isset($accessToken) && $accessToken->hasAccessToken()) ? $accessToken : null;
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws IproSoftwareApiAccessTokenException
     *
     * @return AccessTokenInterface|null
     */
    public static function makeFromApiResponse(ResponseInterface $response): ?AccessTokenInterface
    {
        $responseBody = json_decode($response->getBody(), true);

        if (! isset($responseBody['access_token'])
            || ! isset($responseBody['token_type'])
            || ! isset($responseBody['expires_in'])
            || $responseBody['expires_in'] < 60 * 5
        ) {
            throw new IproSoftwareApiAccessTokenException($response, 'Get Access Token: Not Valid Response');
        }

        $expiresAt = Carbon::now()->addSeconds($responseBody['expires_in']);
        $responseBody['expires_at'] = $expiresAt->toString();

        $accessToken = call_user_func(
            [self::class, 'makeFromJson'],
            json_encode($responseBody)
        );

        if (! ($accessToken instanceof self)) {
            throw new IproSoftwareApiAccessTokenException(
                $response,
                'Get Access Token: Error while initialising'
            );
        }

        return $accessToken;
    }

    public function hasAccessToken(): bool
    {
        return (bool) $this->accessToken && ! $this->isTokenExpired();
    }

    public function isTokenExpired(): bool
    {
        return Carbon::parse($this->expiresAt)
            ->lessThanOrEqualTo(Carbon::now());
    }

    public function getAuthorizationHeader(): string
    {
        return ucfirst($this->tokenType) . ' ' . $this->accessToken;
    }
}
