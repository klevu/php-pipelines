<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation;

enum StringPositions: string
{
    case START = 'start';
    case END = 'end';
    case BOTH = 'both';
}
