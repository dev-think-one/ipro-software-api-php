<?php

namespace Feature\RealCall;

use IproSoftwareApi\Tests\Feature\RealCall\RealCallTestCase;

class GetSourcesListTest extends RealCallTestCase
{

    /** @test  */
    public function validate_api_response_structure()
    {
        $client = $this->getApiClient();

        $response = $client->getSourcesList();

        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('Id', $data[0]);
        $this->assertArrayHasKey('Name', $data[0]);
    }

}
