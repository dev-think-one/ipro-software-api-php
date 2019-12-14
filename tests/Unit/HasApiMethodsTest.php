<?php

namespace Angecode\IproSoftware\Tests\Unit;

use Angecode\IproSoftware\Exceptions\IproServerException;
use Angecode\IproSoftware\Tests\TestCase;
use Angecode\IproSoftware\IproSoftwareClient;
use Angecode\IproSoftware\Contracts\HttpClient;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class HasApiMethodsTest extends TestCase
{
    public function testGetMethodDataReturnNullThenBadMethodException()
    {
        $client = new IproSoftwareClient();

        $this->expectException(\BadMethodCallException::class);

        $client->notExxxist();
    }

    public function testMergeMethodsAndRemoveMethod()
    {
        $client = new IproSoftwareClient();

        $this->assertFalse(in_array('newMethod', array_keys($client->getMethodsList())));
        $this->assertEquals($client, $client->mergeMethods(['newMethod' => []]));
        $this->assertTrue(in_array('newMethod', array_keys($client->getMethodsList())));
        $this->assertEquals($client, $client->removeMethod('newMethod'));
        $this->assertFalse(in_array('newMethod', array_keys($client->getMethodsList())));
    }

    public function testCallPredefinedRequest()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $http = \Mockery::mock(HttpClient::class);

        $client->setHttpClient($http);

        $methodName = $this->arrayKeyFirst($client->getMethodsList());
        $signature = $client->getMethodsList()[$methodName];

        $http->shouldReceive($signature[0])
            ->once()
            ->andReturn('RETURN');

        $return = $client->{$methodName}();

        $this->assertEquals('RETURN', $return);
    }

    public function testCallPredefinedRequestThrowException()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $http = \Mockery::mock(HttpClient::class);

        $client->setHttpClient($http);

        $methodName = $this->arrayKeyFirst($client->getMethodsList());
        $signature = $client->getMethodsList()[$methodName];

        $exception = new ServerException('TEST', new Request('get', '/'), new Response());

        $http->shouldReceive($signature[0])
            ->once()
            ->andThrows($exception);

        $this->expectException(IproServerException::class);

        $client->{$methodName}();
    }
}
