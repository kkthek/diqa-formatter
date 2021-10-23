<?php

namespace DIQA\Formatter;


class Formatter
{

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


        for ($j = 0; $j < count($rows); $j++) {

            if ($rows[$j] === Config::LINE_SEPARATOR || $rows[$j] === Config::DOUBLE_LINE_SEPARATOR) {
                $lines[] = $this->handleSeparator($rows[$j]);
                continue;
            }

            if ($this->config->hasBorder()) {
                $lines[] = $this->handleBorder($j, count($rows));
            }

            list($linesOfRow, $maxLines) = $this->breakLinesIfNecessary($rows[$j]);

            for ($i = 0; $i < $maxLines; $i++) {
                $currentLine = '';
                for ($c = 0; $c < count($linesOfRow); $c++) {
                    if ($this->config->hasBorder()) {
                        if ($c < $this->config->getNumberOfColumns()) {
                            $currentLine .= Config::PIPE;
                        }
                    }

                    $text = $linesOfRow[$c][$i] ?? '';
                    $columnLine = $this->alignColumn($text, $c);
                    $currentLine .= $columnLine;
                    if ($this->config->hasPadding()) {
                        $currentLine .= ' ';
                    }

                }
                if ($this->config->hasBorder()) {
                    $currentLine .= Config::PIPE;
                }
                $currentLine = $this->highlightIfNecessary($currentLine);
                $lines[] = $currentLine;
            }
        }
        if ($this->config->hasBorder()) {
            $lines[] = $this->handleBorder(count($rows), count($rows));
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
        switch ($this->config->getAlignments($column)) {
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
            if ($row[$c] === Config::LINE_SEPARATOR) {
                $separator = str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($c));
                $linesOfRow[] = [$separator];
            } else if ($row[$c] === Config::DOUBLE_LINE_SEPARATOR) {
                $separator = str_repeat(Config::DOUBLE_LINE, $this->config->getColumnWidths($c));
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
     * @return string
     */
    private function handleSeparator($row): string
    {
        if ($row === Config::LINE_SEPARATOR) {
            return str_repeat(Config::SINGLE_LINE, $this->config->getTotalColumnsWidth());

        } else if ($row === Config::DOUBLE_LINE_SEPARATOR) {
            return str_repeat(Config::DOUBLE_LINE, $this->config->getTotalColumnsWidth());

        }
        return '';
    }

    /**
     * Highlight configured substrings with a color
     *
     * @param string $s The string with substrings to highlight
     * @return string the string with color highlights
     */
    private function highlightIfNecessary(string $s): string
    {
        foreach ($this->config->getHighlights() as $word => $color) {
            $s = str_replace($word, "$color$word" . Config::NC, $s);
        }
        return $s;
    }

    /**
     * Renders a border separator line.
     *
     * @param int $row
     * @param $numberOfRows
     * @return string
     */
    private function handleBorder(int $row, $numberOfRows): string
    {
        $line = '';
        $numberOfColumns = $this->config->getNumberOfColumns();
        $paddingCorrection = $this->config->hasPadding() ? 1 : 0;

        if ($row === 0) {
            $line .= "\u{250C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{252C}";
            }
            $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2510}";

        } else if ($row < $numberOfRows) {
            $line .= "\u{251C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{253C}";
            }
            $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2524}";
        } else if ($row === $numberOfRows) {
            $line .= "\u{2514}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{2534}";;
            }
            $line .= str_repeat(Config::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2518}";;
        }
        return $line;
    }
}