<?php

namespace DIQA\Formatter;

class Config
{

    public const RIGHT_ALIGN = 0;
    public const LEFT_ALIGN = 1;
    public const CENTER_ALIGN = 2;

    public const RED = "\033[0;31m";
    public const GREEN = "\033[0;32m";
    public const ORANGE = "\033[0;33m";
    public const BLUE = "\033[0;34m";
    public const NC = "\033[0m"; # No Color

    public const SINGLE_LINE ="\u{2500}";
    public const DOUBLE_LINE ="\u{2550}";

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
     */
    public function __construct(array $columnWidths, array $alignments = null, array $options = null)
    {
        $this->columnWidths = $columnWidths;
        $this->alignments = $alignments;
        $this->options = is_null($options) ? [] : $options;
        $this->totalColumnsWidth = array_sum($this->columnWidths);
        $this->highlights = [];

        if ($this->hasPadding()) {
            for ($i = 0; $i < count($columnWidths) - 1; $i++) {
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
     * @return array|null
     */
    public function getAlignments(int $index)
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
}