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

namespace PMG\Queue\Adapter\Exception;

/**
 * Thrown when the adapater has to cause the entire operation to exit. getCode
 * should return the exit code.
 *
 * @since   0.1
 * @author  Christopher Davis <chris@pmg.co>
 */
class MustQuitException implements AdapaterException
{
    // empty
}
