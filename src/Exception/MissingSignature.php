<?php declare(strict_types=1);

/**
 * This file is part of PMG\Queue
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\Queue\Exception;

/**
 * Thrown when a serializer is asked to unserialize a message that does not have
 * a signature to authenticate it.
 *
 * @since 4.0
 */
final class MissingSignature extends SerializationError
{
    // noop
}
