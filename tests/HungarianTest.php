<?php
/*
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2022 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace ElGigi\HungarianAlgorithm\Tests;

use ElGigi\HungarianAlgorithm\Hungarian;
use PHPUnit\Framework\TestCase;

class HungarianTest extends TestCase
{
    public const DEFAULT_MATRIX = [
        'matrix' => [
            [1, 2, 3, 0, 1],
            [0, 2, 3, 12, 1],
            [3, 0, 1, 13, 1],
            [3, 1, 1, 12, 0],
            [3, 1, 1, 12, 0],
        ],
        'expected' => [
            0 => 3,
            1 => 0,
            2 => 1,
            4 => 2,
            3 => 4,
        ],
    ];

    public function solveProvider(): array
    {
        return [
            static::DEFAULT_MATRIX,
            [
                'matrix' => [
                    [0, 2, 0, 0, 1],
                    [0, 3, 12, 1, 1],
                    [3, 1, 1, 13, 1],
                    [3, 1, 1, 12, 0],
                    [3, 1, 1, 12, 0],
                ],
                'expected' => [
                    0 => 3,
                    1 => 0,
                    2 => 2,
                    4 => 1,
                    3 => 4,
                ],
            ],
            [
                'matrix' => [
                    [-3, -3, -3, -3, -2, -2, -2, -2, -99, -99],
                    [-3, -3, -3, -3, -5, -5, -5, -5, -2, -99],
                    [-2, -2, -2, -2, -5, -5, -5, -5, -3, -99],
                    [-2, -2, -2, -2, -5, -5, -5, -5, -99, -3],
                    [-3, -3, -3, -3, -2, -2, -2, -2, -99, -5],
                    [-4, -4, -4, -4, -3, -3, -3, -3, -1, -99],
                    [-4, -4, -4, -4, -3, -3, -3, -3, -99, -1],
                    [-4, -4, -4, -4, -1, -1, -1, -1, -99, -99],
                    [-1, -1, -1, -1, -3, -3, -3, -3, -6, -99],
                    [-3, -3, -3, -3, -1, -1, -1, -1, -99, -6],
                ],
                'expected' => [
                    7 => 1,
                    6 => 2,
                    5 => 3,
                    9 => 0,
                    4 => 8,
                    0 => 9,
                    8 => 4,
                    3 => 5,
                    2 => 6,
                    1 => 7,
                ],
            ],
            [
                'matrix' => [
                    [-2, -2, -2, -2, -5, -5, -5, -5, -3, -99],
                    [-2, -2, -2, -2, -5, -5, -5, -5, -99, -3],
                    [-2, -2, -2, -2, -3, -3, -3, -3, -99, -99],
                    [-3, -3, -3, -3, -5, -5, -5, -5, -8, -2],
                    [-2, -2, -2, -2, -3, -3, -3, -3, -99, -8],
                    [-3, -3, -3, -3, -1, -1, -1, -1, -99, -4],
                    [-1, -1, -1, -1, -3, -3, -3, -3, -99, -99],
                    [-3, -3, -3, -3, -1, -1, -1, -1, -6, -99],
                    [-3, -3, -3, -3, -1, -1, -1, -1, -99, -6],
                    [-1, -1, -1, -1, -3, -3, -3, -3, -7, -99],
                ],
                'expected' => [
                    7 => 2,
                    6 => 8,
                    5 => 3,
                    9 => 4,
                    4 => 0,
                    0 => 7,
                    8 => 1,
                    3 => 5,
                    2 => 9,
                    1 => 6,
                ],
            ],
            [
                'matrix' => [
                    [-5, -5, -5, -5, -3, -3, -3, -3, -6, -2],
                    [-2, -2, -2, -2, -3, -3, -3, -3, -99, -6],
                    [-3, -3, -3, -3, -2, -2, -2, -2, -99, -99],
                    [-2, -2, -2, -2, -3, -3, -3, -3, -11, -5],
                    [-3, -3, -3, -3, -2, -2, -2, -2, -99, -11],
                    [-3, -3, -3, -3, -4, -4, -4, -4, -1, -7],
                    [-4, -4, -4, -4, -1, -1, -1, -1, -3, -99],
                    [-3, -3, -3, -3, -4, -4, -4, -4, -9, -1],
                    [-1, -1, -1, -1, -4, -4, -4, -4, -99, -9],
                    [-4, -4, -4, -4, -1, -1, -1, -1, -10, -3],
                ],
                'expected' => [
                    1 => 8,
                    7 => 5,
                    5 => 6,
                    8 => 4,
                    3 => 7,
                    2 => 9,
                    9 => 1,
                    4 => 0,
                    6 => 2,
                    0 => 3,
                ],
            ],
            [
                'matrix' => [
                    [0],
                    [11],
                ],
                'expected' => [
                    0 => 0,
                ],
            ],
            [
                'matrix' => [
                    [0, 1],
                ],
                'expected' => [
                    0 => 0,
                ],
            ],
            [
                'matrix' => [
                    [10],
                ],
                'expected' => [
                    0 => 0,
                ],
            ],
//            [
//                'matrix' => [
//                    [10, 2, INF, 15],
//                    [15, INF, INF, 0],
//                    [0, INF, INF, 0],
//                    [0, INF, INF, 0],
//                ],
//                'expected' => [
//                    0 => 0,
//                ],
//            ],
        ];
    }

    /**
     * @dataProvider solveProvider
     */
    public function testSolve(array $matrix, array $expected)
    {
        $algo = new Hungarian($matrix);

        $this->assertEquals($expected, $algo->solve());
    }

    public function testDebug()
    {
        $algo = new Hungarian(static::DEFAULT_MATRIX['matrix']);

        ob_start();
        $algo->solve();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEmpty($output);

        ob_start();
        $algo->debug();
        $algo->solve();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertNotEmpty($output);
    }
}
