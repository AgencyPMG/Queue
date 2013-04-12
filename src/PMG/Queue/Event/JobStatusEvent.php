<?php
/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) 2013 PMG Worldwide
 *
 * @package     PMGQueue
 * @copyright   2013 PMG Worldwide
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace PMG\Queue\Event;

/**
 * Fired on job completion for failure or success
 *
 * @since   0.1
 */
class JobStatusEvent extends \Symfony\Component\EventDispatcher\Event
{
    public function __construct($name)
    {
        $this->setName($name);
    }
}
