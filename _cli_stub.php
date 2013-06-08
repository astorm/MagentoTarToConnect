#!/usr/bin/env php
<?php

Phar::mapPhar('magento-tar-to-connect.phar');

$application = require_once 'phar://magento-tar-to-connect.phar/magento-tar-to-connect.php';
$application->setPharMode(true);
$application->run();

__HALT_COMPILER();