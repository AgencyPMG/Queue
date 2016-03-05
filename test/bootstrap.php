<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2014 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2014 PMG Worldwide
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('PMG\\Queue\\', __DIR__.'/unit');
