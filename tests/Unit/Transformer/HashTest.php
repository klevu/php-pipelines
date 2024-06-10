<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\Hash\Algorithms;
use Klevu\Pipelines\Transformer\Hash;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(Hash::class)]
class HashTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Hash::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Array(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [null, [], null],
                ['', [], 'a7ffc6f8bf1ed76651c14756a061d662f580ff4de43b49fa82d80a4b80f8434a'],
                ['foo', [], '76d3bc41c9f588f7fcd0d5bf4718f8f84b1c41b20882703100b9eb9413807c01'],
                ['foo ', [], '418e8c5e5824084537806f703af8e4239c0414c85894bf814359b11dec5b6468'],
                [str_repeat('"foo&bar;"', 10000), [], '899acbde944287e5b51df5ac1daf3e24c469440c32a04324efb6e85b803eda2a'],
                [42, [], '4e169ddf479c8cd9b4c45e0284181730cb42df3a8be892a7f379bd690db1eafa'],
                [3.14, [], '9a29161c038b081e3f874d3cd4801b4f68dfa0e5951acd85bdcd5a755e6a965b'],

                ['foo', [Algorithms::CRC32], 'a5c4fe49'],
                ['foo', [Algorithms::MD5], 'acbd18db4cc2f85cedef654fccc4a4d8'],
                ['foo', [Algorithms::SHA256], '2c26b46b68ffc68ff99b453c1d30413413422d706483bfa0f98a5e886266e7ae'],
                ['foo', [Algorithms::SHA512], 'f7fbba6e0636f890e56fbbf3283e524c6fa3204ae298382d624741d0dc6638326e282c41be5e4254d8820772c5518a2c5a8c0c7f7eda19594a7eb539453e1ed7'],
                ['foo', [Algorithms::SHA3_256], '76d3bc41c9f588f7fcd0d5bf4718f8f84b1c41b20882703100b9eb9413807c01'],
                ['foobar', [Algorithms::SHA3_256], '09234807e4af85f17c66b48ee3bca89dffd1f1233659f9f940a2b17b0b8c6bc5'],
                ['barfoo', [Algorithms::SHA3_256], 'eb5747a2de2d0ad62b10b6dd3741a052b492e6d4d8ac534fd7df74c75fc1c7ae'],
                ['foo', [Algorithms::SHA3_512], '4bca2b137edc580fe50a88983ef860ebaca36c857b1f492839d6d7392452a63c82cbebc68e3b70a2a1480b4bb5d437a7cba6ecf9d89f9ff3ccd14cd6146ea7e7'],

                ['foo', ['crc32'], 'a5c4fe49'],
                ['foo', ['md5'], 'acbd18db4cc2f85cedef654fccc4a4d8'],
                ['foo', ['sha256'], '2c26b46b68ffc68ff99b453c1d30413413422d706483bfa0f98a5e886266e7ae'],
                ['foo', ['sha512'], 'f7fbba6e0636f890e56fbbf3283e524c6fa3204ae298382d624741d0dc6638326e282c41be5e4254d8820772c5518a2c5a8c0c7f7eda19594a7eb539453e1ed7'],
                ['foo', ['sha3-256'], '76d3bc41c9f588f7fcd0d5bf4718f8f84b1c41b20882703100b9eb9413807c01'],
                ['foo', ['sha3-512'], '4bca2b137edc580fe50a88983ef860ebaca36c857b1f492839d6d7392452a63c82cbebc68e3b70a2a1480b4bb5d437a7cba6ecf9d89f9ff3ccd14cd6146ea7e7'],

                [null, [null, 'bar'], null],
                ['foo', [Algorithms::CRC32, 'bar'], 'ee332176'],
                ['foo', [Algorithms::MD5, 'bar'], '96948aad3fcae80c08a35c9b5958cd89'],
                ['foo', [Algorithms::SHA256, 'bar'], '88ecde925da3c6f8ec3d140683da9d2a422f26c1ae1d9212da1e5a53416dcc88'],
                ['foo', [Algorithms::SHA512, 'bar'], '39082c11afda7a927fe4e23c5ba81b64186c33f7e58adf492be5a5c64ddc9db459e0778d573000a5ebaeeae902b6c8641198406b58bf9c53ce48ecdef73a33d1'],
                ['foo', [Algorithms::SHA3_256, 'bar'], 'eb5747a2de2d0ad62b10b6dd3741a052b492e6d4d8ac534fd7df74c75fc1c7ae'],
                ['foo', [Algorithms::SHA3_512, 'bar'], 'b491a039c51aab2e18c4a7f38401981a078730408bd939df651668cecaf10c1145c55688b28b9f96fd9b966daf66a945131aa59c3fed7f321f3fdfc3c47c5b9c'],
            ],
        );
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [
                        null,
                        '',
                        'foo',
                        'foo ',
                        str_repeat('"foo&bar;"', 10000),
                        42,
                        3.14,
                    ],
                    [],
                    [
                        null,
                        'a7ffc6f8bf1ed76651c14756a061d662f580ff4de43b49fa82d80a4b80f8434a',
                        '76d3bc41c9f588f7fcd0d5bf4718f8f84b1c41b20882703100b9eb9413807c01',
                        '418e8c5e5824084537806f703af8e4239c0414c85894bf814359b11dec5b6468',
                        '899acbde944287e5b51df5ac1daf3e24c469440c32a04324efb6e85b803eda2a',
                        '4e169ddf479c8cd9b4c45e0284181730cb42df3a8be892a7f379bd690db1eafa',
                        '9a29161c038b081e3f874d3cd4801b4f68dfa0e5951acd85bdcd5a755e6a965b',
                    ],
                ],
                [
                    [
                        null,
                        '',
                        'foo',
                        'foo ',
                        str_repeat('"foo&bar;"', 10000),
                        42,
                        3.14,
                    ],
                    ['md5'],
                    [
                        null,
                        'd41d8cd98f00b204e9800998ecf8427e',
                        'acbd18db4cc2f85cedef654fccc4a4d8',
                        'd83d7e46b5a299f6dfd0b80b406c9d4e',
                        '3c72e51d1e2968faef74bb5ba5a9349b',
                        'a1d0c6e83f027327d8461063f4ac58a6',
                        '4beed3b9c4a886067de0e3a094246f78',
                    ],
                ],
                [
                    [
                        null,
                        '',
                        'foo',
                        'foo ',
                        str_repeat('"foo&bar;"', 10000),
                        42,
                        3.14,
                    ],
                    ['md5', 'bar'],
                    [
                        null,
                        '37b51d194a7513e45b56f6524f2d51f2',
                        '96948aad3fcae80c08a35c9b5958cd89',
                        '7f81f085d7ab099fc600e392e8bf48f8',
                        '6e32bf5535b0c03817359b42742b5923',
                        '48fb6c4d6d155a30c8ce3203246c9bae',
                        '0951ee248958e14a6e4afe17fd692a2d',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidInputData(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return [
            [(object)['foo']],
            [$fileHandle],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return self::convertFixtures(
            fixtures: array_merge(
                array_map(
                    callback: static fn ($algorithmArgumentValue): array => [
                        'foo',
                        [$algorithmArgumentValue, null],
                        '',
                    ],
                    array: [
                        42,
                        3.14,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($saltArgumentValue): array => [
                        'foo',
                        [null, $saltArgumentValue],
                        '',
                    ],
                    array: [
                        ['foo'],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
            ),
        );
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[2] ?? null,
                is_array($data[1] ?? null)
                    ? $argumentIteratorFactory->create([
                    Hash::ARGUMENT_INDEX_ALGORITHM => $data[1][0] ?? null,
                    Hash::ARGUMENT_INDEX_SALT => $data[1][1] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
