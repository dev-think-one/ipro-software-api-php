<?php

namespace Angecode\IproSoftware\AccessToken;

use Angecode\IproSoftware\Contracts\AccessToken;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;

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
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl   - time in seconds
     *
     * @return bool
     * @static
     */
    public function puast(string $key, $value, int $ttl = 0)
    {
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     * @static
     */
    public function gaset(string $key, $default = null)
    {
        $data = file_get_contents($this->filePath);

        return ($data === false) ? $default : $data;
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
