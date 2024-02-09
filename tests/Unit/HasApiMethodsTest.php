<?php

namespace IproSoftwareApi\Tests\Unit;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use IproSoftwareApi\Contracts\HttpClient;
use IproSoftwareApi\Exceptions\IproServerException;
use IproSoftwareApi\IproSoftwareClient;
use IproSoftwareApi\Tests\TestCase;

class HasApiMethodsTest extends TestCase
{
    /** @test */
    public function get_wrong__method_data_return_bad_method_exception()
    {
        $client = new IproSoftwareClient();

        $this->expectException(\BadMethodCallException::class);

        $client->notExxxist();
    }

    /** @test */
    public function merge_methods_and_remove_method()
    {
        $client = new IproSoftwareClient();

        $this->assertFalse(in_array('newMethod', array_keys(IproSoftwareClient::getMethodsList())));
        IproSoftwareClient::mergeMethods(['newMethod' => []]);
        $this->assertTrue(in_array('newMethod', array_keys(IproSoftwareClient::getMethodsList())));
        IproSoftwareClient::removeMethod('newMethod');
        $this->assertFalse(in_array('newMethod', array_keys(IproSoftwareClient::getMethodsList())));
    }

    /** @test */
    public function call_predefined_request()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $http = \Mockery::mock(HttpClient::class);

        $client->setHttpClient($http);

        $methodName = $this->arrayKeyFirst(IproSoftwareClient::getMethodsList());
        $signature  = IproSoftwareClient::getMethodsList()[$methodName];

        $http->shouldReceive($signature[0])
            ->once()
            ->andReturn('RETURN');

        $return = $client->{$methodName}();

        $this->assertEquals('RETURN', $return);
    }

    /** @test */
    public function incorrect_call_predefined_request_throw_exception()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $http = \Mockery::mock(HttpClient::class);

        $client->setHttpClient($http);

        $methodName = $this->arrayKeyFirst(IproSoftwareClient::getMethodsList());
        $signature  = IproSoftwareClient::getMethodsList()[$methodName];

        $exception = new ServerException('TEST', new Request('get', '/'), new Response());

        $http->shouldReceive($signature[0])
            ->once()
            ->andThrows($exception);

        $this->expectException(IproServerException::class);

        $client->{$methodName}();
    }
}
