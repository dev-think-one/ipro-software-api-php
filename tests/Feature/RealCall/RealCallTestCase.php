<?php

namespace IproSoftwareApi\Tests\Feature\RealCall;

use IproSoftwareApi\Exceptions\IproSoftwareApiException;
use IproSoftwareApi\IproSoftwareClient;
use IproSoftwareApi\Tests\TestCase;

class RealCallTestCase extends TestCase
{
    /**
     * @return IproSoftwareClient
     * @throws IproSoftwareApiException
     */
    protected function getApiClient(): IproSoftwareClient
    {
        $clientId     = $_ENV['IPROSOFTWARE_CLIENT_ID'];
        $clientSecret = $_ENV['IPROSOFTWARE_CLIENT_SECRET'];
        $apiHost      = $_ENV['IPROSOFTWARE_API_HOST'];

        if ($apiHost && $clientId && $clientSecret) {

            return new \IproSoftwareApi\IproSoftwareClient([
                'api_host'      => $apiHost,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ]);
        }

        /** @psalm-suppress UndefinedClass */
        $this->markTestSkipped('Real client params not set. Please create .env file.');
    }
}
