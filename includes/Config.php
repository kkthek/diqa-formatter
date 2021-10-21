<?php
namespace DIQA\Formatter;

class Config {

    public const RIGHT_ALIGN = 0;
    public const LEFT_ALIGN = 1;
    public const CENTER_ALIGN = 2;

    private $columnWidths;
    private $alignments;
    private $totalColumnsWidth;
    private $options;

    /**
     * Formatting configuration.
     *
     * @param array $columnWidths array of column widths (in characters)
     * @param array|null $alignments array of column alignments
     */
    public function __construct(array $columnWidths, array $alignments = null, array $options = null)
    {
        $this->columnWidths = $columnWidths;
        $this->alignments = $alignments;
        $this->options = is_null($options) ? [] : $options;

        if ($this->hasPadding()) {
            for($i = 0; $i < count($columnWidths)-1; $i++) {
                $this->columnWidths[$i]--;
            }
        }
        $this->totalColumnsWidth = array_sum($this->columnWidths);
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