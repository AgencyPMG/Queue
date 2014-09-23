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
 * Fired when an exception get caught anywhere in the consumer.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class ExceptionEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $e;

    public function __construct(\Exception $e)
    {
        $this->setException($e);
    }

    public function setException(\Exception $e)
    {
        $this->e = $e;
    }

    public function getException()
    {
        return $this->e;
    }
}
