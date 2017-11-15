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

namespace PMG\Queue;

use PMG\Queue\Lifecycle\NullLifecycle;

class_alias(NullLifecycle::class, __NAMESPACE__.'\\NullLifecycle');

@trigger_error(sprintf(
    'The %s\\NullLifecycle class is deprecated, use %s instead',
    __NAMESPACE__,
    NullLifecycle::class
), E_USER_DEPRECATED);
