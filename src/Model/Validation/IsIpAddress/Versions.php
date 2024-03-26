<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Validation\IsIpAddress;

enum Versions: string
{
    case IPv4 = 'IPv4';
    case IPv6 = 'IPv6';
}
