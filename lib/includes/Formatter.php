<?php

namespace DIQA\Formatter;


class Formatter
{

    public const LINE_SEPARATOR = 0;
    public const DOUBLE_LINE_SEPARATOR = 1;

    private $config;

    /**
     * Creates a formatter object with the given configuration.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Formats text according to set configuration.
     *
     * @param array $rows Rows to print as 2-dim array (rows with columns)
     * @return string
     */
    public function format(array $rows): string
    {
        $lines = [];
        foreach ($rows as $row) {
            if ($this->handleSeparatorIfNecessary($row, $lines)) {
                continue;
            }
            list($linesOfRow, $maxLines) = $this->breakLinesIfNecessary($row);

            for ($i = 0; $i < $maxLines; $i++) {
                $currentLine = '';
                for ($c = 0; $c < count($linesOfRow); $c++) {
                    $text = $linesOfRow[$c][$i] ?? '';
                    $columnLine = $this->alignColumn($text, $c);
                    $currentLine .= $columnLine;
                    if ($this->config->hasPadding()) {
                        if ($c < count($linesOfRow)-1) {
                            $currentLine .= ' ';
                        }
                    }
                }
                $lines[] = $currentLine;
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Returns aligned text for a column.
     *
     * @param string $text Lines a single row was split into
     * @param int $column The column
     * @return string
     */
    private function alignColumn(string $text, int $column): string
    {
        switch($this->config->getAlignments($column)) {
            case Config::LEFT_ALIGN:
            default:
                $columnLine = TextUtilities::rightPad($text, $this->config->getColumnWidths($column));
                break;
            case Config::RIGHT_ALIGN:
                $columnLine = TextUtilities::leftPad($text, $this->config->getColumnWidths($column));
                break;
            case Config::CENTER_ALIGN:
                $columnLine = TextUtilities::centerPad($text, $this->config->getColumnWidths($column));
                break;
        }
        return $columnLine;
    }

    /**
     * Breaks a long text for a column in several short ones that fit the column in multiple lines.
     *
     * @param array $row columns of a row
     * @return array $linesOfRow = 2-dim array of columns and lines per column
     *               $maxLines = maximum size of lines for a column
     */
    private function breakLinesIfNecessary(array $row): array
    {
        $linesOfRow = [];
        for ($c = 0; $c < count($row); $c++) {
            if ($row[$c] === self::LINE_SEPARATOR) {
                $separator = str_repeat("-", $this->config->getColumnWidths($c));
                $linesOfRow[] = [$separator];
            } else if ($row[$c] === self::DOUBLE_LINE_SEPARATOR) {
                $separator = str_repeat("=", $this->config->getColumnWidths($c));
                $linesOfRow[] = [$separator];
            } else {
                $linesOfRow[] = TextUtilities::breakText($row[$c], $this->config->getColumnWidths($c));
            }
        }

        $sizes = array_map(function ($e) {
            return count($e);
        }, $linesOfRow);
        $maxLines = max($sizes);

        return [$linesOfRow, $maxLines];
    }

    /**
     * Add a separator line if necessary.
     *
     * @param mixed $row all columns of a row
     * @param array $lines where separators are added
     * @return bool true if a separator line was added, otherwise false
     */
    private function handleSeparatorIfNecessary($row, array & $lines) : bool {
        if ($row === self::LINE_SEPARATOR) {
            $lines[] = str_repeat("-", $this->config->getTotalColumnsWidth());
            return true;
        } else if ($row === self::DOUBLE_LINE_SEPARATOR) {
            $lines[] = str_repeat("=", $this->config->getTotalColumnsWidth());
            return true;
        }
        return false;
    }
}