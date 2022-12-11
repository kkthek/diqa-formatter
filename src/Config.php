<?php

namespace DIQA\Formatter;

use Exception;

class Config
{

    public const RIGHT_ALIGN = 0;
    public const LEFT_ALIGN = 1;
    public const CENTER_ALIGN = 2;
    public const LEFT_AND_RIGHT_ALIGN = 3;

    public const LINE_SEPARATOR = "__LINE_SEPERATOR__";
    public const DOUBLE_LINE_SEPARATOR = "__DOUBLELINE_SEPERATOR__";
    public const EMPTY_LINE_SEPARATOR = "__EMPTY_LINE_SEPERATOR__";

    private $columnWidths;
    private $alignments;
    private $options;

    private $highlights;
    private $sequencesToIgnore;

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
        if (!is_null($alignments) && count($alignments) !== count($columnWidths)) {
            throw new Exception("Number of columns and alignments must match");
        }
        $this->options = is_null($options) ? [] : $options;

        $this->highlights = [];

        if ($this->hasBorderPadding()) {
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
        $this->sequencesToIgnore = [];
    }

    /**
     * Highlights $word with $color.
     *
     * @param string $word the word to highlight with color
     * @param Color $color color to use
     * @param mixed $column column where to highlight only (optional)
     */
    public function highlightWord(string $word, Color $color, $column = NULL) {
        $this->highlights[$word] = [ 'color' => $color, 'column' => $column ];
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
     * Defines character sequences which are ignored by layout.
     *
     * @param $sequencesToIgnore
     */
    public function setSequencesToIgnore($sequencesToIgnore): void
    {
        $this->sequencesToIgnore = $sequencesToIgnore;
    }

    /**
     * Returns character sequences which are ignored by layout.
     *
     * @return array
     */
    public function getSequencesToIgnore(): array
    {
        return $this->sequencesToIgnore;
    }


    /**
     * Returns width of a column.
     *
     * @param int $index of column
     * @return int
     */
    public function getColumnWidth(int $index): int
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

    public function hasBorderPadding(): bool
    {
        return $this->options['borderPadding'] ?? false;
    }

    public function hasBorder(): bool
    {
        return $this->options['border'] ?? false;
    }

    public function paddingChar(): string
    {
        return $this->options['paddingChar'] ?? ' ';
    }

    public function wrapColumns(): string
    {
        return $this->options['wrapColumns'] ?? true;
    }

}