<?php


namespace Angecode\IproSoftware\AccessToken;


use Carbon\Carbon;
use Angecode\IproSoftware\Contracts\AccessToken as AccessTokenInterface;

class AccessToken implements AccessTokenInterface
{
    protected $accessToken;
    protected $tokenType;
    protected $expiresIn;
    protected $expiresAt;

    /**
     * AccessToken constructor.
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
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
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
            return new self(
                $data['access_token'] ?? null,
                $data['token_type'] ?? null,
                $data['expires_in'] ?? null,
                $data['expires_at'] ?? null
            );
        }

        return null;
    }

    public function hasAccessToken(): bool
    {
        return (!!$this->accessToken && !$this->isTokenExpired());
    }

    public function isTokenExpired(): bool
    {
        return (Carbon::parse($this->expiresAt)
            ->lessThanOrEqualTo(Carbon::now()));
    }

    public function getAuthorizationHeader(): string
    {
        return ucfirst($this->tokenType) . ' ' . $this->accessToken;
    }
}