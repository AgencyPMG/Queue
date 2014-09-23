<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2014 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2014 PMG Worldwide
 * @license     http://opensource.org/licenses/MIT MIT
 */

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('PMG\\Queue\\', __DIR__.'/unit');
