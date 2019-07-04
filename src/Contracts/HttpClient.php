<?php

namespace Angecode\IproSoftware\Contracts;

use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    public function setCacheManager(AccessTokenCacher $cacheManager): self;

    public function request($method, $path = '', array $options = []): ResponseInterface;
}
