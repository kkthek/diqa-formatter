<?php

namespace DIQA\Formatter;

use Exception;

class Config
{

    public const RIGHT_ALIGN = 0;
    public const LEFT_ALIGN = 1;
    public const CENTER_ALIGN = 2;
    public const LEFT_AND_RIGHT_ALIGN = 3;

    public const RED = "\033[0;31m";
    public const GREEN = "\033[0;32m";
    public const ORANGE = "\033[0;33m";
    public const BLUE = "\033[0;34m";
    public const NC = "\033[0m"; # No Color

    public const LINE_SEPARATOR = 0;
    public const DOUBLE_LINE_SEPARATOR = 1;

    private $columnWidths;
    private $alignments;
    private $totalColumnsWidth;
    private $options;

    private $highlights;

    /**
     * Formatting configuration.
     *
     * @param array $columnWidths array of column widths (in characters)
     * @param array|null $alignments array of column alignments
     * @param array|null $options Options
     * @throws Exception in case the configuration is inconsistent
     */
    public function __construct(array $columnWidths, array $alignments = null, array $options = null)
    {
        $this->columnWidths = $columnWidths;
        $this->alignments = $alignments;
        if (count($alignments) !== count($columnWidths)) {
            throw new Exception("Number of columns and alignments must match");
        }
        $this->options = is_null($options) ? [] : $options;
        $this->totalColumnsWidth = array_sum($this->columnWidths);
        $this->highlights = [];

        if ($this->hasPadding()) {
            for ($i = 0; $i < count($columnWidths) ; $i++) {
                $this->columnWidths[$i] -= 2;
            }
        }
        if ($this->hasBorder()) {
            $this->columnWidths[0]--;
            for ($i = 0; $i < count($columnWidths) ; $i++) {
                $this->columnWidths[$i]--;
            }
        }
    }

    /**
     * Highlights $word with $color.
     *
     * @param $word
     * @param $color
     */
    public function highlightWord($word, $color) {
        $this->highlights[$word] = $color;
    }

    /**
     * Returns words which should be highlighted with a color.
     *
     * @return array
     */
    public function getHighlights(): array
    {
        return $this->highlights;
    }

    /**
     * Returns width of a column.
     *
     * @param int $index of column
     * @return mixed
     */
    public function getColumnWidths(int $index)
    {
        return $this->columnWidths[$index];
    }

    /**
     * Returns number of columns.
     *
     * @return int
     */
    public function getNumberOfColumns(): int
    {
        return count($this->columnWidths);
    }

    /**
     * Returns total length of columns.
     *
     * @return int
     */
    public function getTotalColumnsWidth(): int
    {
        return $this->totalColumnsWidth;
    }

    /**
     * Returns alignment for a column.
     *
     * @param int $index of column
     * @return int
     */
    public function getAlignments(int $index): int
    {
        if (is_null($this->alignments)) {
            return self::LEFT_ALIGN;
        }
        return $this->alignments[$index];
    }

    public function hasPadding(): bool
    {
        return isset($this->options['padding']) && $this->options['padding'] === true;
    }

    public function hasBorder(): bool
    {
        return isset($this->options['border']) && $this->options['border'] === true;
    }
}