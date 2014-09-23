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
 * Event fired when the Consumer exits.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class QuitEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $exit_code = 0;

    public function __construct($exit_code=0)
    {
        $this->setExitCode($exit_code);
    }

    public function setExitCode($exit_code)
    {
        $this->exit_code = $exit_code;
        return $this;
    }

    public function getExitCode()
    {
        return $this->exit_code;
    }
}
