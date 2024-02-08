<?php

namespace IproSoftwareApi\Tests\Feature\RealCall;

class GetBookingRulesListTest extends RealCallTestCase
{

    /** @test  */
    public function validate_api_response_structure()
    {
        $client = $this->getApiClient();

        $response = $client->getBookingRulesList();

        $data = json_decode($response->getBody(), true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertIsString(array_keys($data)[0]);
    }

}
