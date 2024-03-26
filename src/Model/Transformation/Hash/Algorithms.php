<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation\Hash;

enum Algorithms: string
{
    case CRC32 = 'crc32';
    case MD5 = 'md5';
    case SHA256 = 'sha256';
    case SHA512 = 'sha512';
    case SHA3_256 = 'sha3-256';
    case SHA3_512 = 'sha3-512';
}
