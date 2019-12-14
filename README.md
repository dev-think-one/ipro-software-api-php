# IProSoftware API client (https://www.ipro-software.com/)

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/yaroslawww/ipro-software-api-php.svg?branch=master)](https://travis-ci.org/yaroslawww/ipro-software-api-php) 
[![StyleCI](https://github.styleci.io/repos/195302588/shield?branch=master&style=flat-square)](https://github.styleci.io/repos/195302588)
[![Quality Score](https://img.shields.io/scrutinizer/g/yaroslawww/ipro-software-api-php.svg?b=master)](https://scrutinizer-ci.com/g/yaroslawww/ipro-software-api-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/ipro-software-api-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yaroslawww/ipro-software-api-php/?branch=master)
[![PHP Version](https://img.shields.io/travis/php-v/yaroslawww/ipro-software-api-php.svg?style=flat-square)](https://packagist.org/packages/yaroslawww/ipro-software-api-php)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/ipro-software-api-php.svg)](https://packagist.org/packages/yaroslawww/ipro-software-api-php)

## Installation

You can install the package via composer:

```bash
    composer require yaroslawww/ipro-software-api-php
```

### Simple example

```php
    $iproSoftwareClient = new \Angecode\IproSoftware\IproSoftwareClient([
        'api_host' => 'my-iprosoftware-api-host',
        'client_id' => 'my-iprosoftware-client-id',
        'client_secret' => 'my-iprosoftware-client-secret',
    ]);
    
    $response = $iproSoftwareClient->getBookingRulesList();

    $responseBody = json_decode($response->getBody());
```

**Note**: All predefined api requests name you can find [here](https://github.com/yaroslawww/ipro-software-api-php/blob/master/src/Traits/HasApiMethods.php)

## Configure the client

The Trello client needs a few configuration settings to operate successfully.

Setting | Description
--- | ---
`api_host` | An api domain associated with your application.
`client_id` | The application `client ID` associated with your application.
`client_secret` | The application `client secret` associated with your application.
`oauth_endpoint` | You can specify you own oauth endpoint (By default used *`/oauth/2.0/token`*).
`requests_path_prefix` | You can specify you own endpoint prefix for all **predefined endpoints** (By default used *`/apis`*).
`client_conf` | Set of predefined configurations applied to `http client` (By default package use [Guzzle](http://docs.guzzlephp.org/en/), all available options for Guzzle you can find [here](http://docs.guzzlephp.org/en/latest/request-options.html)).
`cache_manager` | By default package not cache the *access token* and request new one every request. If you want to cache access token to some storage (file, DB, ...) than you should create you own class from interface `Angecode\IproSoftware\Contracts\AccessTokenCacher` and pass object to this setting
`access_token_class` | Also you can specify you own access token class implements interface `Angecode\IproSoftware\Contracts\AccessToken`

#### Set configuration when creating client

```php
    $iproSoftwareClient = new \Angecode\IproSoftware\IproSoftwareClient([
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
    $iproSoftwareClient = new \Angecode\IproSoftware\IproSoftwareClient([
        'api_host' => 'my-iprosoftware-api-host',
        'client_id' => 'my-iprosoftware-client-id',
        'client_secret' => 'my-iprosoftware-client-secret'
    ]);
    
    $iproSoftwareClient->setAccessTokenCacheManager(new MyDatabaseAccessTokenCacheManager())
```

#### Set your own http client if you want full control over requests

```php
    $iproSoftwareClient = new \Angecode\IproSoftware\IproSoftwareClient();
    
    $iproSoftwareClient->setHttpClient(new MyOwnHttpClient($credentials))
```

### Handling exceptions

If you try to query with the wrong configuration, you will get an exception `Angecode\IproSoftware\Exceptions\IproSoftwareApiException`. If the server fails to get the access token, you will receive `Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException`

```php
    try {
        $response = $iproSoftwareClient->getBookingRulesList();
    } catch (Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException $e) {
        $code = $e->getCode(); // Http status code from response
        $reason = $e->getMessage(); // Http status reason phrase
        $httpResponse = $e->getResponse(); // Psr\Http\Message\ResponseInterface from http client
    }
```

### Api documentation
IproSoftware has only on documentation [there](https://github.com/iprosoftware/api-csharp-client/wiki)

## Testing

``` bash
    ./vendor/bin/phpunit
    # or
    composer test
```

## Security
If you discover any security related issues, please email yaroslav.georgitsa@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
