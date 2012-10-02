<?php
namespace Publero\Component\GeoCoder;

use Buzz\Browser;

/**
 * @see https://developers.google.com/maps/documentation/geocoding/
 */
class GeoCoder
{
    /**
     * @var string
     */
    const BASE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @var string
     */
    const OK_STATUS = 'OK';

    /**
     * @var string
     */
    const EMPTY_STATUS = 'ZERO_RESULTS';

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var bool
     */
    private $sensor;

    /**
     * @param Browser $browser
     * @param bool $sensor
     */
    public function __construct(Browser $browser, $sensor = false)
    {
        $this->browser = $browser;
        $this->sensor = (bool) $sensor;
    }

    /**
     * @param array $parameters
     * @param array $components
     * @param string $language
     * @param array $bounds
     * @return array
     * @throws ApiException
     *
     * @see https://developers.google.com/maps/documentation/geocoding/#JSON
     */
    protected function call(array $parameters, array $components = array(), $language = null, array $bounds = array())
    {
        $parameters['sensor'] = $this->sensor ? 'true' : 'false';

        if (!empty($components)) {
            $parameters['components'] = $components;
        }

        if (!empty($language)) {
            $parameters['language'] = $language;
        }

        if (!empty($bounds)) {
            $parameters['bounds'] = $this->boundsToString($bounds);
        }

        $url = self::BASE_URL . '?' . http_build_query($parameters);
        $response = $this->browser->get($url);
        $data = json_decode($response->getContent());

        if ($data->status === self::EMPTY_STATUS) {
            return array();
        }

        if ($data->status !== self::OK_STATUS) {
            throw new ApiException('status: ' . $data->status);
        }

        return $data->results;
    }

    /**
     * @param array $bounds
     * @return string
     */
    private function boundsToString(array $bounds)
    {
        return sprintf(
            '%s,%s|%s,%s',
            $bounds['from']['lat'],
            $bounds['from']['lng'],
            $bounds['to']['lat'],
            $bounds['to']['lng']
        );
    }

    /**
     * @param string $address
     * @param array $components
     * @param string $language
     * @param array $bounds
     * @return array
     * @throws ApiException
     *
     * @see https://developers.google.com/maps/documentation/geocoding/#JSON
     */
    public function getAddressData($address, array $components = array(), $language = null, array $bounds = array())
    {
        $parameters = array('address' => $address);

        return $this->call($parameters, $components, $language, $bounds);
    }

    /**
     * @param string $address
     * @param array $components
     * @param string $language
     * @param array $bounds
     * @return array['lat' => $lat, 'lng' => $lng]|null
     * @throws ApiException
     */
    public function getAddressCoordinates($address, array $components = array(), $language = null, array $bounds = array())
    {
        $result = $this->getAddressData($address, $components, $language, $bounds);

        if (empty($result)) {
            return null;
        } elseif (count($result) !== 1) {
            throw new \InvalidArgumentException('too general address');
        }
        $result = $result[0];

        return (array) $result->geometry->location;
    }

    /**
     * @param string $coordinates
     * @param array $components
     * @param string $language
     * @param array $bounds
     * @return array
     * @throws ApiException
     *
     * @see https://developers.google.com/maps/documentation/geocoding/#ReverseGeocoding
     */
    public function getCoordinatesData(array $coordinates, array $components = array(), $language = null, array $bounds = array())
    {
        $parameters = array('latlng' => sprintf('%s,%s', $coordinates['lat'], $coordinates['lng']));

        return $this->call($parameters, $components, $language, $bounds);
    }
}
