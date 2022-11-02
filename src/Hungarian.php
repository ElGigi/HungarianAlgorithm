<?php
/*
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2022 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace ElGigi\HungarianAlgorithm;

use LogicException;

class Hungarian
{
    /** The assignment cost matrix to be minimised */
    public array $matrix = [];

    /** The reduced cost matrix */
    protected array $reduced = [];

    /** The primed zeros of the matrix */
    protected array $primed = [];

    /** The starred zeros of the matrix */
    protected array $starred = [];

    /** The covered lines of the matrix */
    protected array $covered = [
        'column' => [],
        'row' => [],
    ];

    public function __construct(array $matrix)
    {
        $this->assertValid($matrix);
        $this->matrix = $matrix;
        $this->reduced = $this->forceSquare($matrix);
    }

    public function assertValid(array $matrix): void
    {
        if (0 === count($matrix)) {
            throw new LogicException('Number of rows in matrix returns false.');
        }

        foreach ($matrix as $key => $row) {
            if (count($row) !== count(array_intersect_key($row, ...$matrix))) {
                throw new LogicException(
                    sprintf(
                        'Column keys of row %u do not correspond to the column keys found in the rest of the matrix.',
                        $key
                    )
                );
            }
        }
    }

    /**
     * Get matrix size.
     *
     * @param array $matrix
     *
     * @return array
     */
    public function getMatrixSize(array $matrix): array
    {
        return [
            max(0, ...array_map(fn($cols) => count($cols), $matrix)),
            count($matrix),
        ];
    }

    /**
     * Force square.
     *
     * @param array $matrix
     *
     * @return array
     */
    public function forceSquare(array $matrix): array
    {
        $max = max($this->getMatrixSize($matrix));

        // Defaults 0
        $matrix = array_pad($matrix, $max, []);
        $matrix = array_map(fn($row) => array_pad($row, $max, 0), $matrix);

        return $matrix;
    }

    /**
     * Reduce matrix to initial size.
     *
     * @param array $matrix
     *
     * @return array
     */
    public function reduceToInitialSize(array $matrix): array
    {
        list($x, $y) = $this->getMatrixSize($matrix);

        $matrix = array_slice($matrix, 0, $y);
        $matrix = array_map(fn($row) => array_slice($row, 0, $x), $matrix);

        return $matrix;
    }

    /*
     * Reduces the cost matrix
     */
    protected function reduce(): void
    {
        /*
         * Reduces all rows of the matrix
         */
        foreach ($this->reduced as $row => $cells) {
            if (($min = min($cells)) !== INF) {
                foreach ($cells as $column => $cell) {
                    $this->reduced[$row][$column] -= $min;
                }
            }
        }

        $transposed = array_map(null, ...$this->reduced);
        $tmp = $this->reduced;

        /*
         * Reduces all columns of the matrix
         */
        foreach ($transposed as $column => $cells) {
            if (($min = min((array)$cells)) !== INF) {
                foreach ((array)$cells as $row => $cell) {
                    $this->reduced[$row][$column] -= $min;
                }
            }
        }
    }

    public function addPrime($row, $column): static
    {
        $this->primed[$row] = $column;

        return $this;
    }

    public function addStar($row, $column): static
    {
        $this->starred[$row] = $column;

        return $this;
    }

    public function getPrimed(): array
    {
        return $this->primed;
    }

    public function hasPrimeInColumn($column): bool
    {
        return (bool)array_search($column, $this->primed, true);
    }

    public function getPrimeFromColumn($column): bool|int|string
    {
        return array_search($column, $this->primed, true);
    }

    public function hasPrimeInRow($row): bool
    {
        return array_key_exists($row, $this->primed);
    }

    public function getPrimeFromRow($row)
    {
        if (!key_exists($row, $this->primed)) {
            return false;
        }

        return $this->primed[$row];
    }

    public function hasStarInColumn($column): bool
    {
        return in_array($column, $this->starred, true);
    }

    public function getStarFromColumn($column): bool|int|string
    {
        return array_search($column, $this->starred, true);
    }

    public function hasStarInRow($row): bool
    {
        return array_key_exists($row, $this->starred);
    }

    public function getStarFromRow($row)
    {
        if (!key_exists($row, $this->starred)) {
            return false;
        }

        return $this->starred[$row];
    }

    public function getZeroMatrix(): array
    {
        $zeros = [];
        foreach ($this->reduced as $row => $cells) {
            $zeros[$row] = array_keys($cells, 0, true);
        }
        return $zeros;
    }

    public function getCoveredZeroMatrix($zero_matrix): array
    {
        $covered_zero_matrix = [];
        foreach ($zero_matrix as $row => $cells) {
            foreach ($cells as $column) {
                if (in_array($row, $this->covered['row'], true) || in_array($column, $this->covered['column'], true)) {
                    $covered_zero_matrix[$row][] = $column;
                }
            }
        }
        return $covered_zero_matrix;
    }

    public function getNonCoveredZeroMatrix($zero_matrix): array
    {
        $non_covered_zero_matrix = [];
        foreach ($zero_matrix as $row => $cells) {
            foreach ($cells as $column) {
                if (!in_array($row, $this->covered['row'], true) && !in_array(
                        $column,
                        $this->covered['column'],
                        true
                    )) {
                    $non_covered_zero_matrix[$row][] = $column;
                }
            }
        }
        return $non_covered_zero_matrix;
    }

    public function solve($print = false)
    {
        $print && $this->printMatrix($this->matrix, 'Original cost matrix:');
        /*
         * Preliminary Steps:
         *  -  Generate reduced matrix
         *  -  For each row
         *     - Star first non-covered zero
         *     - Cover column of starred zero
         */
        $this->reduce();
        $print && $this->printMatrix($this->reduced, 'Reduced cost matrix:');
        foreach ($this->reduced as $row => $cells) {
            $columns = array_diff(array_keys($cells, 0, true), $this->covered['column']);
            if (isset($columns[0])) {
                $this->addStar($row, $columns[0]);
                $this->covered['column'][] = $columns[0];
            }
        }
        $print && $this->printMatrix($this->reduced, 'Final preliminary reduced cost matrix:');

        /*
         * Generate zero matrix
         */
        start:
        $zero_matrix = $this->getZeroMatrix();
        $non_covered_zero_matrix = $this->getNonCoveredZeroMatrix($zero_matrix);
        while ($non_covered_zero_matrix) {
            /*
             * Step 1:
             *  -  Select first non-covered zero and prime this selected zero
             *  -  If has starred zero in row of selected zero
             *     - Uncover column of starred zero
             *     - Cover row of starred zero
             *     Else
             *     - Step 2
             */
            $row = key($non_covered_zero_matrix);
            $column = $non_covered_zero_matrix[$row][0];
            $this->addPrime($row, $column);
            if ($this->hasStarInRow($row)) {
                // get column from the starred zero in the row
                $column = $this->getStarFromRow($row);

                // uncover the column of the starred zero
                $key = array_search($column, $this->covered['column'], true);
                unset($this->covered['column'][$key]);

                // cover the row
                $this->covered['row'][] = $row;
            } else {
                /*
                 * Step 2:
                 *  -  Get the sequence of starred and primed zeros connecting to the initial primed zero
                 *     - Get the starred zero in the column of the primed zero
                 *     - Get the primed zero in the row of the starred zero
                 *  -  Unstar the starred zeros from the sequence
                 *  -  Star the primed zeros from the sequence
                 *  -  Empty the list with primed zeros
                 *  -  Empty the list with covered columns and covered rows
                 *  -  Cover the columns with a starred zero in it
                 */
                $starred = [];
                $primed = [];
                $primed[$row] = $column;
                $i = $row;
                while (true) {
                    if (!$this->hasStarInColumn($primed[$i])) {
                        // Unstar the starred zeros from the sequence
                        foreach ($starred as $row => $column) {
                            unset($this->starred[$row]);
                        }

                        // Star the primed zeros from the sequence
                        foreach ($primed as $row => $column) {
                            $this->addStar($row, $column);
                        }

                        // Empty the list with primed zeros
                        $this->primed = [];

                        // Empty the list with covered columns
                        $this->covered['column'] = [];

                        // Empty the list with covered columns
                        $this->covered['row'] = [];

                        // Cover the columns with a starred zero in it
                        foreach ($this->starred as $row => $column) {
                            $this->covered['column'][] = $column;
                        }
                        break 1;
                    }

                    $star_row = $this->getStarFromColumn($primed[$i]);
                    $star_column = $primed[$i];
                    $starred[$star_row] = $star_column;

                    if ($this->hasPrimeInRow($star_row)) {
                        $prime_row = $star_row;
                        $prime_column = $this->getPrimeFromRow($prime_row);
                        $primed[$prime_row] = $prime_column;
                    } else {
                        die;
                    }

                    $i = $prime_row;
                }
            }

            $print && $this->printMatrix($this->reduced, 'Reduced cost matrix of non-covered zero iteration:');

            $zero_matrix = $this->getZeroMatrix();
            $non_covered_zero_matrix = $this->getNonCoveredZeroMatrix($zero_matrix);
        }

        /*
         * Step 3:
         *  -  If the number of covered columns is equal to the number of rows/columns of the cost matrix
         *     - The currently starred zeros show the optimal solution
         *
         */
        if (count($this->covered['column']) + count($this->covered['row']) === count($this->reduced)) {
            list($x, $y) = $this->getMatrixSize($this->matrix);

            return array_filter(
                $this->starred,
                function ($value, $key) use ($x, $y) {
                    return ($x > $value && $y > $key);
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        $non_covered_reduced_matrix = [];
        $once_covered_reduced_matrix = [];
        $twice_covered_reduced_matrix = [];
        foreach ($this->reduced as $row => $cells) {
            foreach ($cells as $column => $cell) {
                if (!in_array($row, $this->covered['row'], true) && !in_array(
                        $column,
                        $this->covered['column'],
                        true
                    )) {
                    $non_covered_reduced_matrix[$row][$column] = $cell;
                } elseif (in_array($row, $this->covered['row'], true) && in_array(
                        $column,
                        $this->covered['column'],
                        true
                    )) {
                    $twice_covered_reduced_matrix[$row][$column] = $cell;
                } else {
                    $once_covered_reduced_matrix[$row][$column] = $cell;
                }
            }
        }

        $min = INF;
        foreach ($non_covered_reduced_matrix as $row => $cells) {
            foreach ($cells as $cell) {
                $min = ($cell < $min) ? $cell : $min;
            }
        }
        foreach ($non_covered_reduced_matrix as $row => $cells) {
            foreach ($cells as $column => $cell) {
                $this->reduced[$row][$column] -= $min;
            }
        }
        foreach ($twice_covered_reduced_matrix as $row => $cells) {
            foreach ($cells as $column => $cell) {
                $this->reduced[$row][$column] += $min;
            }
        }

        goto start;
    }

    public function printMatrix(array $matrix, $title = null): void
    {
        if (!is_null($title)) {
            printf("\n" . $title);
        }

        printf("\n  ");

        foreach ($matrix[key($matrix)] as $column => $cell) {
            if (in_array($column, $this->covered['column'], true)) {
                printf("C \t  ");
                continue;
            }

            printf("  \t  ");
        }

        printf("\n\n");

        foreach ($matrix as $row => $cells) {
            printf("| ");

            foreach ($cells as $column => $cell) {
                printf("%d", $cell);

                if (isset($this->starred[$row]) && $this->starred[$row] === $column) {
                    printf("*");
                }

                if (isset($this->primed[$row]) && $this->primed[$row] === $column) {
                    printf("'");
                }

                printf(" \t| ");
            }

            if (in_array($row, $this->covered['row'], true)) {
                printf("C");
            }

            printf("\n");
        }
    }
}
