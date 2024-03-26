<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation\EscapeHtml;

enum Quotes: string
{
    // Single and double quotes
    case QUOTES = 'quotes';

    // Double quotes only
    case COMPAT = 'compat';

    // No quotes
    case NOQUOTES = 'noquotes';

    /**
     * @return int
     */
    public function htmlentitiesFlag(): int
    {
        return match ($this) {
            self::QUOTES => ENT_QUOTES,
            self::COMPAT => ENT_COMPAT,
            self::NOQUOTES => ENT_NOQUOTES,
        };
    }
}
