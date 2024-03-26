<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation\ChangeCase;

enum Cases: string
{
    case UPPERCASE = 'uppercase';
    case LOWERCASE = 'lowercase';
    case TITLECASE = 'titlecase';
}
