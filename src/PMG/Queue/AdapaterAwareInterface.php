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

/**
 * Defines an "awareness" (set, get) of Adapaters (server queue backends).
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
interface AdapaterAwareInterface
{
    /**
     * Set the adapater.
     *
     * @since   0.1
     * @access  public
     * @param   PMG\Queue\Adapater\AdapaterInterface $adpt
     * @return  $this
     */
    public function setAdapater(\PMG\Queue\Adapater\AdapaterInterface $adpt);

    /**
     * Get the adapater.
     *
     * @since   0.1
     * @access  public
     * @return  PMG\Queue\Adapater\AdapaterInterface
     */
    public function getAdapater();
}
