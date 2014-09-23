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
 * An event that is aware of the consumer.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class ConsumerEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @since   0.1
     * @access  private
     * @var     PMG\Queue\ConsumerInterface
     */
    private $consumer;

    /**
     * Constructor, set the consumer.
     *
     * @since   0.1
     * @access  public
     * @param   PMG\Queue\ConsumerInterface
     * @return  void
     */
    public function __construct(\PMG\Queue\ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Get the consumer.
     *
     * @since   0.1
     * @access  public
     * @return  PMG\Queue\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
}
