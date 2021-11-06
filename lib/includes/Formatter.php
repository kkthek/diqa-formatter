<?php

namespace DIQA\Formatter;


class Formatter
{
    private const SINGLE_LINE ="\u{2500}";
    private const DOUBLE_LINE ="\u{2550}";
    private const PIPE = "\u{2502}";

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

    public function formatLine(... $columns): string
    {
        return $this->format([$columns]);
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
                    if ($this->config->hasBorder() && $c < $this->config->getNumberOfColumns()) {
                        $currentLine .= self::PIPE;
                    }

                    $text = $linesOfRow[$c][$i] ?? '';
                    $columnLine = $this->alignColumn($text, $c);

                    $columnLine = $this->highlightIfNecessary($columnLine, $c);
                    $currentLine .= $this->config->hasPadding() ? ' ' : '';
                    $currentLine .= $columnLine;
                    $currentLine .= $this->config->hasPadding() ? ' ' : '';

                }
                if ($this->config->hasBorder()) {
                    $currentLine .= self::PIPE;
                }
                $lines[] = $currentLine;
            }
        }
        if ($this->config->hasBorder()) {
            $lines[] = $this->handleBorder(count($rows), count($rows));
        }
        return implode("\n", $lines);
    }

    /**
     * Renders aligned text for a column.
     *
     * @param mixed $columnValue Lines a single row was split into
     * @param int $column The column
     * @return string
     */
    private function alignColumn($columnValue, int $column): string
    {
        switch ($this->config->getAlignments($column)) {
            case Config::LEFT_ALIGN:
            default:
                $columnLine = TextUtilities::rightPad($columnValue, $this->config->getColumnWidths($column));
                break;
            case Config::RIGHT_ALIGN:
                $columnLine = TextUtilities::leftPad($columnValue, $this->config->getColumnWidths($column));
                break;
            case Config::CENTER_ALIGN:
                $columnLine = TextUtilities::centerPad($columnValue, $this->config->getColumnWidths($column));
                break;
            case Config::LEFT_AND_RIGHT_ALIGN:
                // if line consists of left and right part do left and right alignment
                // otherwise just do left alignment
                if (is_array($columnValue)) {
                    $columnLine = TextUtilities::leftAndRightPad($columnValue[0], $columnValue[1], $this->config->getColumnWidths($column));
                } else {
                    $columnLine = TextUtilities::rightPad($columnValue, $this->config->getColumnWidths($column));
                }
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
                $separator = str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c));
                $linesOfRow[] = [$separator];
            } else if ($row[$c] === Config::DOUBLE_LINE_SEPARATOR) {
                $separator = str_repeat(self::DOUBLE_LINE, $this->config->getColumnWidths($c));
                $linesOfRow[] = [$separator];
            } else if (is_array($row[$c])) {
                if (count($row[$c]) !== 2) {
                    continue;
                }
                // column consists of left and right part used for left-right alignment
                // if too long, treat it as normal line
                if (!TextUtilities::exceedsColumnWidth($row[$c][0], $row[$c][1], $this->config->getColumnWidths($c))) {
                    $linesOfRow[] = [$row[$c]];
                } else {
                    $row[$c] = $row[$c][0] . " " . $row[$c][1];
                    $linesOfRow[] = TextUtilities::breakText(trim($row[$c]), $this->config->getColumnWidths($c));
                }
            } else {
                $linesOfRow[] = TextUtilities::breakText(trim($row[$c]), $this->config->getColumnWidths($c));
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
            return str_repeat(self::SINGLE_LINE, $this->config->getTotalColumnsWidth());

        } else if ($row === Config::DOUBLE_LINE_SEPARATOR) {
            return str_repeat(self::DOUBLE_LINE, $this->config->getTotalColumnsWidth());

        }
        return '';
    }

    /**
     * Highlight configured substrings with a color
     *
     * @param string $s The string with substrings to highlight
     * @return string the string with color highlights
     */
    private function highlightIfNecessary(string $s, int $column): string
    {
        foreach ($this->config->getHighlights() as $word => $colorDescriptor) {
            $color = $colorDescriptor['color']->getColorString();
            if (is_null($colorDescriptor['column']) || $colorDescriptor['column'] === $column) {
                $s = str_replace($word, "$color$word" . Config::NC, $s);
            }
        }
        return $s;
    }

    /**
     * Renders a border separator line.
     *
     * @param int $row current row
     * @param int $totalNumberOfRows total number of rows
     * @return string
     */
    private function handleBorder(int $row, int $totalNumberOfRows): string
    {
        $line = '';
        $numberOfColumns = $this->config->getNumberOfColumns();
        $paddingCorrection = $this->config->hasPadding() ? 2 : 0;

        if ($row === 0) {
            $line .= "\u{250C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{252C}";
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2510}";

        } else if ($row < $totalNumberOfRows) {
            $line .= "\u{251C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{253C}";
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2524}";
        } else if ($row === $totalNumberOfRows) {
            $line .= "\u{2514}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{2534}";;
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns-1) + $paddingCorrection);
            $line .= "\u{2518}";;
        }
        return $line;
    }
}