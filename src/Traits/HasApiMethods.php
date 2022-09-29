<?php

namespace Angecode\IproSoftware\Traits;

use Angecode\IproSoftware\Exceptions\IproServerException;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiException;
use Angecode\IproSoftware\HttpClient;
use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait HasApiMethods.
 *
 * @method ResponseInterface getSourcesList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Sources
 * @method ResponseInterface getBookingRulesList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Booking-Rules
 * @method ResponseInterface getBookingTagsList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Booking-Tags
 * @method ResponseInterface getLocationsList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Locations
 * @method ResponseInterface getAttributesList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Attributes
 * @method ResponseInterface getContactTypesList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Contact-Types
 * @method ResponseInterface searchContacts($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Contacts
 * @method ResponseInterface getContact($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Get-Contact
 * @method ResponseInterface getExternalContact($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/External-Contact
 * @method ResponseInterface createOrUpdateContact($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Create-Update-Contact
 * @method ResponseInterface getPropertiesList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Properties
 * @method ResponseInterface searchProperties($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Search
 * @method ResponseInterface getPropertiesReferenceLookupList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Reference-Lookup
 * @method ResponseInterface getPropertyDetails($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Detail
 * @method ResponseInterface getPropertyImages($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Images
 * @method ResponseInterface getPropertyExtras($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Extras
 * @method ResponseInterface getPropertyRates($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Rates
 * @method ResponseInterface getPropertyCustomRates($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Custom-Rates
 * @method ResponseInterface getPropertyAvailability($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Availabilities
 * @method ResponseInterface getPropertyDayAvailability($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Day-Availabilities
 * @method ResponseInterface getPropertyRooms($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Rooms
 * @method ResponseInterface getPropertyDistances($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Distances
 * @method ResponseInterface getPropertyAll($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-All
 * @method ResponseInterface getPropertyEnquiries($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Enquiries
 * @method ResponseInterface getPropertyWelcomepack($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Property-Welcome-Pack
 * @method ResponseInterface createOrUpdateProperty($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Push-Property
 * @method ResponseInterface createEnquiry($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Create-Enquiry
 * @method ResponseInterface searchBookings($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Bookings
 * @method ResponseInterface calculateBooking($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Calculate-Booking
 * @method ResponseInterface createBooking($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Create-Booking
 * @method ResponseInterface updateBooking($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Update-Booking
 * @method ResponseInterface getStatementsByOwner($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Statements
 * @method ResponseInterface getReviewsList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Reviews
 * @method ResponseInterface createReview($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Create-Review
 * @method ResponseInterface createPayment($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Create-Payment
 * @method ResponseInterface getLateDealsList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Late-Deals
 * @method ResponseInterface getSpecialOffersList($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Special-Offers-&-Last-Minute-Deals
 * @method ResponseInterface getVouchers($options = []) https://github.com/iprosoftware/api-csharp-client/wiki/Voucher-API---Query-vouchers-to-bring-through-validation-and-price
 */
trait HasApiMethods
{
    /** @var string Path prefix */
    protected $pathPrefix = 'apis/';

    /**
     * Api methods list.
     *
     * @var array
     */
    protected $methods = [
        /* Settings */
        'getSourcesList'      => ['get', 'sources'],
        'getBookingRulesList' => ['get', 'bookingrules'],
        'getBookingTagsList'  => ['get', 'bookingtags'],
        'getLocationsList'    => ['get', 'locations'],
        'getAttributesList'   => ['get', 'amenities'],
        'getContactTypesList' => ['get', 'contacttypes'],
        /* Contacts */
        'searchContacts'        => ['get', 'contacts'],
        'getContact'            => ['get', 'contact/%s'],
        'getExternalContact'    => ['get', 'externalcontactID'],
        'createOrUpdateContact' => ['post', 'contacts'],
        /* Properties */
        'getPropertiesList'                => ['get', 'properties'],
        'searchProperties'                 => ['get', 'propertysearch'],
        'searchLiteProperties'             => ['get', 'propertysearchlite'],
        'getPropertiesReferenceLookupList' => ['get', 'properties/reflookup'],
        'getPropertyDetails'               => ['get', 'property/%s'],
        'getPropertyImages'                => ['get', 'property/%s/images'],
        'getPropertyExtras'                => ['get', 'property/%s/extras'],
        'getPropertyRates'                 => ['get', 'property/%s/rates'],
        'getPropertyCustomRates'           => ['get', 'property/%s/customrates'],
        'getPropertyAvailability'          => ['get', 'property/%s/availability'],
        'getPropertyDayAvailability'       => ['get', 'property/%s/dayavailability'],
        'getPropertyRooms'                 => ['get', 'property/%s/rooms'],
        'getPropertyDistances'             => ['get', 'property/%s/distances'],
        'getPropertyAll'                   => ['get', 'property/%s/all'],
        'getPropertyEnquiries'             => ['get', 'property/%s/enquiries'],
        'getPropertyWelcomepack'           => ['get', 'property/%s/welcomepack'],
        'createOrUpdateProperty'           => ['post', 'property'],
        /* Enquires */
        'createEnquiry' => ['post', 'enquiry'],
        /* Bookings */
        'searchBookings'       => ['get', 'bookings'],
        'calculateBooking'     => ['post', 'booking/calc'],
        'createBooking'        => ['post', 'booking'],
        'updateBooking'        => ['post', 'booking/update'],
        'getStatementsByOwner' => ['get', 'statements'],
        /* Reviews */
        'getReviewsList' => ['get', 'reviews'],
        'createReview'   => ['post', 'reviews'],
        /* Payments */
        'createPayment' => ['post', 'payments'],
        /* Offers & Deals */
        'getLateDealsList'     => ['get', 'latedeals'],
        'getSpecialOffersList' => ['get', 'specialoffers'],
        'getVouchers'          => ['get', 'vouchers'],
    ];

    abstract public function httpClient(): ?\Angecode\IproSoftware\Contracts\HttpClient;

    /**
     * Attempts to handle api method call.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return object
     * @throws IproSoftwareApiException
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if ($signature = $this->getMethodData($method)) {
            if (is_null($this->httpClient())) {
                throw new IproSoftwareApiException('Please specify HttpClient or pass credentials to client constructor', 500);
            }
            $pathTemplate = $this->pathPrefix . $signature[1];
            preg_match_all('/\%/', $pathTemplate, $replacements);
            $replacementCount  = isset($replacements[0]) ? count($replacements[0]) : 0;
            $replacementParams = array_splice($parameters, 0, $replacementCount);
            array_unshift($replacementParams, $pathTemplate);
            $path = call_user_func_array('sprintf', $replacementParams);
            array_unshift($parameters, $path);

            try {
                $response = call_user_func_array([$this->httpClient(), $signature[0]], $parameters);
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                throw new IproServerException($e);
            }

            return $response;
        }

        throw new BadMethodCallException('Method ' . $method . ' not found on ' . get_class() . '.', 500);
    }

    /**
     * @param string $pathPrefix
     *
     * @return self
     */
    public function setPathPrefix(string $pathPrefix): self
    {
        $this->pathPrefix = $pathPrefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathPrefix(): string
    {
        return $this->pathPrefix;
    }

    /**
     * @return array
     */
    public function getMethodsList(): array
    {
        return $this->methods;
    }

    /**
     * @param string $method
     *
     * @return self
     */
    public function removeMethod(string $method): self
    {
        if (isset($this->methods[$method])) {
            unset($this->methods[$method]);
        }

        return $this;
    }

    /**
     * @param array $methods
     *
     * @return self
     */
    public function mergeMethods(array $methods): self
    {
        $this->methods = array_merge($this->methods, $methods);

        return $this;
    }

    /**
     * Get method form methods list.
     *
     * @param $method
     *
     * @return array|null
     */
    protected function getMethodData($method): ?array
    {
        $validMethod = isset($this->methods[$method])
            && is_array($this->methods[$method])
            && count($this->methods[$method]) >= 2
            && in_array(strtoupper($this->methods[$method][0]), HttpClient::HTTP_METHODS);

        if ($validMethod) {
            return $this->methods[$method];
        }

        return null;
    }
}
