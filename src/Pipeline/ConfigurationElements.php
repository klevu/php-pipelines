<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

enum ConfigurationElements: string
{
    case PIPELINE = 'pipeline';
    case ARGS = 'args';
    case STAGES = 'stages';
    case IMPORT = 'import';
    case ADD_STAGES = 'addStages';
    case REMOVE_STAGES = 'removeStages';
}
