# IProSoftware API client (https://www.ipro-software.com/)

[![Packagist License](https://img.shields.io/packagist/l/think.studio/ipro-software-api-php?color=%234dc71f)](https://github.com/think.studio/ipro-software-api-php/blob/main/LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/ipro-software-api-php)](https://packagist.org/packages/think.studio/ipro-software-api-php)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/ipro-software-api-php)](https://packagist.org/packages/think.studio/ipro-software-api-php)
[![Build Status](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/badges/build.png?b=main)](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/think.studio/ipro-software-api-php/?branch=main)

Unofficial [IproSoftware](https://github.com/iprosoftware/api-csharp-client/wiki) api implementation.

## Installation

You can install the package via composer:

```bash
    composer require think.studio/ipro-software-api-php
```

### Simple example

```php
    $iproSoftwareClient = new \IproSoftwareApi\IproSoftwareClient([
        'api_host' => 'my-iprosoftware-api-host',
        'client_id' => 'my-iprosoftware-client-id',
        'client_secret' => 'my-iprosoftware-client-secret',
    ]);
    
    $response = $iproSoftwareClient->getBookingRulesList();

    $responseBody = json_decode($response->getBody());
```

**Note**: All predefined api requests name you can
find [here](https://github.com/dev-think-one/ipro-software-api-php/blob/main/src/Traits/HasApiMethods.php)

## Configure the client

The Trello client needs a few configuration settings to operate successfully.

| Setting                | Description                                                                                                                                                                                                                                                                                  |
|------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `api_host`             | An api domain associated with your application.                                                                                                                                                                                                                                              |
| `client_id`            | The application `client ID` associated with your application.                                                                                                                                                                                                                                |
| `client_secret`        | The application `client secret` associated with your application.                                                                                                                                                                                                                            |
| `oauth_endpoint`       | You can specify you own oauth endpoint (By default used *`/oauth/2.0/token`*).                                                                                                                                                                                                               |
| `requests_path_prefix` | You can specify you own endpoint prefix for all **predefined endpoints** (By default used *`/apis`*).                                                                                                                                                                                        |
| `client_conf`          | Set of predefined configurations applied to `http client` (By default package use [Guzzle](http://docs.guzzlephp.org/en/), all available options for Guzzle you can find [here](http://docs.guzzlephp.org/en/latest/request-options.html)).                                                  |
| `cache_manager`        | By default package not cache the *access token* and request new one every request. If you want to cache access token to some storage (file, DB, ...) than you should create you own class from interface `IproSoftwareApi\Contracts\AccessTokenCacher` and pass object to this setting |
| `access_token_class`   | Also you can specify you own access token class implements interface `IproSoftwareApi\Contracts\AccessToken`                                                                                                                                                                           |

#### Set configuration when creating client

```php
$iproSoftwareClient = new \IproSoftwareApi\IproSoftwareClient([
    'api_host' => 'my-iprosoftware-api-host',
    'client_id' => 'my-iprosoftware-client-id',
    'client_secret' => 'my-iprosoftware-client-secret',
    'cache_manager' => new MyDatabaseAccessTokenCacheManager(),
    'client_conf' => [
        'timeout' => 15,
        'http_errors' => false,
        'headers' => [
            'Accept' => 'application/json',
        ]
    ]
]);
```

#### Set access token cache manager after initialisation

```php
    $iproSoftwareClient = new \IproSoftwareApi\IproSoftwareClient([
        'api_host' => 'my-iprosoftware-api-host',
        'client_id' => 'my-iprosoftware-client-id',
        'client_secret' => 'my-iprosoftware-client-secret'
    ]);
    
    $iproSoftwareClient->setAccessTokenCacheManager(new MyDatabaseAccessTokenCacheManager())
```

#### Set your own http client if you want full control over requests

```php
    $iproSoftwareClient = new \IproSoftwareApi\IproSoftwareClient();
    
    $iproSoftwareClient->setHttpClient(new MyOwnHttpClient($credentials))
```

### Handling exceptions

If you try to query with the wrong configuration, you will get an
exception `IproSoftwareApi\Exceptions\IproSoftwareApiException`. If the server fails to get the access token, you
will receive `IproSoftwareApi\Exceptions\IproSoftwareApiAccessTokenException`

```php
    try {
        $response = $iproSoftwareClient->getBookingRulesList();
    } catch (IproSoftwareApi\Exceptions\IproSoftwareApiAccessTokenException $e) {
        $code = $e->getCode(); // Http status code from response
        $reason = $e->getMessage(); // Http status reason phrase
        $httpResponse = $e->getResponse(); // Psr\Http\Message\ResponseInterface from http client
    }
```

## Testing

```shell
  composer test
```

## Credits

- [![Think Studio](https://think.studio.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
