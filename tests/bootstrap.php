<?php

/**
 * Test bootstrap file.
 *
 * Loads Composer autoloader and registers stubs for host-application classes
 * that are not available in this standalone module's test environment.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/stubs.php';
