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

namespace PMG\Queue;

class Producer implements ProducerInterface, AdapterAwareInterface
{
    /**
     * Container for the Adapater (server backend)
     *
     * @since   0.1
     * @access  private
     * @var     PMG\Queue\Adapater\AdapaterInterface
     */
    private $adapater;

    /**
     * Constructor. Set the Adapater.
     *
     * @since   0.1
     * @access  public
     * @param   PMG\Queue\Adapater\AdapterInterface $adpt
     * @return  void
     */
    public function __construct(\PMG\Queue\Adapter\AdapterInterface $adpt)
    {
        $this->adapater = $adpt;
    }

    /**
     * From ProducerInterface
     *
     * {@inheritdoc}
     */
    public function addJob($name, array $args=array(), $ttr=null)
    {
        return $this->getAdapter()->put($name, $args, $ttr);
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function setAdapter(\PMG\Queue\Adapter\AdapterInterface $adpt)
    {
        $this->adapater = $adpt;
    }

    /**
     * From AdapaterAwareInterface
     *
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        return $this->adapater;
    }
}
