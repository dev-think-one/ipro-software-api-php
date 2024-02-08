<?php

namespace IproSoftwareApi\AccessToken;

use IproSoftwareApi\Contracts\AccessToken;
use IproSoftwareApi\Contracts\AccessTokenCacher;

class NoneCacher implements AccessTokenCacher
{
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
        return true;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @return mixed
     * @static
     */
    public function get(): ?AccessToken
    {
        return null;
    }
}
