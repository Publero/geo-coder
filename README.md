GeoCoder Component
==================

GeoCoder provides interface to Google's GeoCoder API.

Usage
-----

Api initialization example:

``` php
<?php
$browser = new Buzz\Browser();
$browser->setClient(new Buzz\Client\Curl());
$browser->setMessageFactory(new Buzz\Message\Factory\Factory());
$api = new Publero\Component\GeoCoder\GeoCoder($browser);
```

Coordinates request:

``` php
<?php
$coordinates = $api->getAddressCoordinates('1 Example Street, Example City, Example Country');
if (coordinates === null) {
    // not found, do someting ...
} else {
    $latitude = $coordinates['lat'];
    $longitude = $coordinates['lng'];
}
```

Resources
---------

You can run the unit tests with the following command:

``` bash
$ phpunit
```
