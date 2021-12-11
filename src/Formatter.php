<?php

namespace DIQA\Formatter;

use Exception;

class Formatter
{
    private const SINGLE_LINE = "\u{2500}";
    private const DOUBLE_LINE = "\u{2550}";
    private const PIPE = "\u{2502}";
    private const NC = "\033[0m"; # No Color

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

    public function formatLine(...$columns): string
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
        $resultLines = [];

        for ($j = 0; $j < count($rows); $j++) {

            if ($rows[$j] === Config::LINE_SEPARATOR || $rows[$j] === Config::DOUBLE_LINE_SEPARATOR) {
                $resultLines[] = $this->handleSeparator($rows[$j]);
                continue;
            }

            if ($this->config->hasBorder()) {
                $resultLines[] = $this->handleBorder($j, count($rows));
            }

            $this->formatOneLine($rows[$j], $resultLines);
        }
        if ($this->config->hasBorder()) {
            $resultLines[] = $this->handleBorder(count($rows), count($rows));
        }
        return implode("\n", $resultLines);
    }

    /**
     * Renders aligned text for a column.
     *
     * @param mixed $columnValue Lines a single row was split into
     * @param int $column The column
     * @return string
     * @throws Exception
     */
    private function alignColumn($columnValue, int $column): string
    {
        switch ($this->config->getAlignments($column)) {
            case Config::LEFT_ALIGN:
            default:
                $this->checkColumnInput($columnValue);
            $columnLine = TextUtilities::rightPad($columnValue, $this->config->getColumnWidths($column),
                    $this->config->paddingChar());
                break;
            case Config::RIGHT_ALIGN:
                $this->checkColumnInput($columnValue);
                $columnLine = TextUtilities::leftPad($columnValue, $this->config->getColumnWidths($column),
                    $this->config->paddingChar());
                break;
            case Config::CENTER_ALIGN:
                $this->checkColumnInput($columnValue);
                $columnLine = TextUtilities::centerPad($columnValue, $this->config->getColumnWidths($column),
                    $this->config->paddingChar());
                break;
            case Config::LEFT_AND_RIGHT_ALIGN:
                // if line consists of left and right part do left and right alignment
                // otherwise just do left alignment
                if (is_array($columnValue)) {
                    $columnLine = TextUtilities::leftAndRightPad($columnValue[0], $columnValue[1],
                        $this->config->getColumnWidths($column), $this->config->paddingChar());
                } else {
                    $columnLine = TextUtilities::rightPad($columnValue, $this->config->getColumnWidths($column),
                        $this->config->paddingChar());
                }
                break;
        }
        return $columnLine;
    }

    /**
     * Breaks a long text for a column in several short ones that fit the column in multiple lines.
     *
     * @param array $row columns of a row
     * @return array $linesOfRow = 2-dim array of columns and lines per column. usually columns do not have same
     * amount of lines
     *
     * @throws Exception
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
            } else if ($this->config->getAlignments($c) === Config::LEFT_AND_RIGHT_ALIGN) {
                if (!is_array($row[$c]) || count($row[$c]) !== 2) {
                    throw new Exception("expect an array of size 2 as content of column $c when using left-and-right-alignment");
                }

                // column consists of left and right part used for left-right alignment
                // if too long, treat it as normal line
                if (!TextUtilities::exceedsColumnWidth($row[$c][0], $row[$c][1], $this->config->getColumnWidths($c))) {
                    $linesOfRow[] = [$row[$c]];
                } else {
                    if ($this->config->wrapColumns()) {
                        $row[$c] = $row[$c][0] . " " . $row[$c][1];
                        $linesOfRow[] = TextUtilities::breakText(trim($row[$c]), $this->config->getColumnWidths($c));
                    } else {
                        $left = TextUtilities::shortenRight($row[$c][0], floor($this->config->getColumnWidths($c) * 0.66) - 1);
                        $right = TextUtilities::shortenLeft($row[$c][1], floor($this->config->getColumnWidths($c) * 0.33));
                        $linesOfRow[] = [[$left, $right]];
                    }
                }
            } else {
                if ($this->config->wrapColumns()) {
                    $linesOfRow[] = TextUtilities::breakText(trim($row[$c]), $this->config->getColumnWidths($c));
                } else {
                    $linesOfRow[] = [TextUtilities::shortenRight(trim($row[$c]), $this->config->getColumnWidths($c))];
                }
            }
        }

        return $linesOfRow;
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
                $s = str_replace($word, "$color$word" . self::NC, $s);
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
        $paddingCorrection = $this->config->hasBorderPadding() ? 2 : 0;

        if ($row === 0) {
            $line .= "\u{250C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{252C}";
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns - 1) + $paddingCorrection);
            $line .= "\u{2510}";

        } else if ($row < $totalNumberOfRows) {
            $line .= "\u{251C}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{253C}";
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns - 1) + $paddingCorrection);
            $line .= "\u{2524}";
        } else if ($row === $totalNumberOfRows) {
            $line .= "\u{2514}";
            for ($c = 0; $c < $numberOfColumns - 1; $c++) {
                $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($c) + $paddingCorrection);
                $line .= "\u{2534}";;
            }
            $line .= str_repeat(self::SINGLE_LINE, $this->config->getColumnWidths($numberOfColumns - 1) + $paddingCorrection);
            $line .= "\u{2518}";;
        }
        return $line;
    }

    /**
     * Formats one line of the input.
     *
     * @param array $inputLine Columns of the line
     * @param array $resultLines output lines
     */
    private function formatOneLine(array $inputLine, array &$resultLines)
    {
        $wrappedInputLines = $this->breakLinesIfNecessary($inputLine);

        // get maximum number of lines of all columns
        $sizes = array_map(function ($e) {
            return count($e);
        }, $wrappedInputLines);
        $maxLines = max($sizes);

        for ($i = 0; $i < $maxLines; $i++) {
            $currentLine = '';
            for ($c = 0; $c < count($wrappedInputLines); $c++) {
                if ($this->config->hasBorder() && $c < $this->config->getNumberOfColumns()) {
                    $currentLine .= self::PIPE;
                }

                $text = $wrappedInputLines[$c][$i] ?? '';
                $columnLine = $this->alignColumn($text, $c);

                $columnLine = $this->highlightIfNecessary($columnLine, $c);
                $currentLine .= $this->config->hasBorderPadding() ? $this->config->paddingChar() : '';
                $currentLine .= $columnLine;
                $currentLine .= $this->config->hasBorderPadding() ? $this->config->paddingChar() : '';

            }
            if ($this->config->hasBorder()) {
                $currentLine .= self::PIPE;
            }
            $resultLines[] = $currentLine;
        }

    }

    /**
     * @param $columnValue
     * @throws Exception
     */
    private function checkColumnInput($columnValue): void
    {
        if (is_array($columnValue)) {
            throw new Exception("Detected array where should be string. Wrong alignment used?");
        }
    }
}