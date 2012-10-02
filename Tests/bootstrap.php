<?php

spl_autoload_register(function ($class) {
    if (0 === strpos(ltrim($class, '/'), 'NasAdvokat\Component\GeoCoder')) {
        if (file_exists($file = __DIR__.'/../'.substr(str_replace('\\', '/', $class), strlen('NasAdvokat\Component\GeoCoder')).'.php')) {
            require_once $file;
        }
    }
});

if (file_exists($loader = __DIR__.'/../vendor/autoload.php')) {
    require_once $loader;
}
