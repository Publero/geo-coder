<?php
namespace Publero\Component\GeoCoder\Tests;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Factory\Factory;
use Publero\Component\GeoCoder\ApiException;
use Publero\Component\GeoCoder\GeoCoderApi;

class GeoCoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const TEST_ADDRESS = 'Hlavní 1, Brno, Česká republika';

    /**
     * @var float
     */
    const TEST_LATITUDE = 49.2163308;

    /**
     * @var float
     */
    const TEST_LONGITUDE = 16.563519;

    /**
     * @var GeoCoderApi
     */
    private static $api;

    public static function setUpBeforeClass()
    {
        $browser = new Browser();
        $browser->setClient(new Curl());
        $browser->setMessageFactory(new Factory());
        static::$api = new GeoCoderApi($browser);
    }

    public static function tearDownAfterClass()
    {
        static::$api = null;
    }

    public function testGetAddressData()
    {
        $result = self::$api->getAddressData(self::TEST_ADDRESS);

        $this->assertTrue(is_array($result));
    }

    public function testGetAddressCoordinates()
    {
        $coordinates = self::$api->getAddressCoordinates(self::TEST_ADDRESS);

        $this->assertEquals(self::TEST_LATITUDE, $coordinates['lat']);
        $this->assertEquals(self::TEST_LONGITUDE, $coordinates['lng']);
    }

    public function testGetCoordinatesData()
    {
        $coordinates = array('lat' => self::TEST_LATITUDE, 'lng' => self::TEST_LONGITUDE);
        $components = array();
        $results = self::$api->getCoordinatesData($coordinates, $components, 'cs');

        $this->assertTrue(is_array($results));

        $relevantAddressComponents = array('street_number', 'route', 'locality', 'country');
        $addressComponents = array();
        foreach ($results[0]->address_components as $component) {
            $componentTypes = array_intersect($component->types, $relevantAddressComponents);
            if (count($componentTypes) > 0) {
                $addressComponents[$componentTypes[0]] = $component->long_name;
            }
        }
        $foundAddress = sprintf(
            '%s %s, %s, %s',
            $addressComponents['route'],
            $addressComponents['street_number'],
            $addressComponents['locality'],
            $addressComponents['country']
        );

        $this->assertEquals(self::TEST_ADDRESS, $foundAddress);
    }
}
