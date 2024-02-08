<?php

namespace IproSoftwareApi\AccessToken;

use IproSoftwareApi\Contracts\AccessToken;
use IproSoftwareApi\Contracts\AccessTokenCacher;

class FileCacher implements AccessTokenCacher
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * FileCacher constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Store an item in the cache.
     *
     * @param mixed $accessToken
     * @param int   $ttl         - time in seconds
     *
     * @return bool
     * @static
     */
    public function put(AccessToken $accessToken, int $ttl = 0)
    {
        return (bool) file_put_contents($this->filePath, serialize($accessToken));
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @return mixed
     * @static
     */
    public function get(): ?AccessToken
    {
        $token = unserialize(file_get_contents($this->filePath));

        return ($token instanceof AccessToken) ? $token : null;
    }
}
