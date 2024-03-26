<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation\EscapeHtml;

enum TranslationTables: string
{
    case HTML401 = 'html401';
    case XML1 = 'xml1';
    case XHTML = 'xhtml';
    case HTML5 = 'html5';

    /**
     * @return int
     */
    public function htmlentitiesFlag(): int
    {
        return match ($this) {
            self::HTML401 => ENT_HTML401,
            self::XML1 => ENT_XML1,
            self::XHTML => ENT_XHTML,
            self::HTML5 => ENT_HTML5,
        };
    }
}
