<?php

namespace DIQA\Formatter;

class TextUtilities
{

    /**
     * Breaks text in multiple lines of $maxLength. Tries to preserve words.
     *
     * @param string $line Text
     * @param int $maxLength maximum length of line
     * @param array $sequencesToIgnore Character sequences to ignore
     * @return array
     */
    public static function breakText(string $line, int $maxLength, array $sequencesToIgnore = []): array
    {

        $rows = [];
        $tokens = self::splitTokens($line, $maxLength);

        $line = '';
        $token = reset($tokens);
        do {
            if (mb_strlen(str_replace($sequencesToIgnore, '', $line . $token)) >= $maxLength) {
                if ($line != '') $rows[] = $line;
                $line = $token;
            } else {
                $line .= $line == '' ? $token : ' ' . $token;
            }
        } while (($token = next($tokens)) !== false);
        if ($line != '') {
            $rows[] = $line;
        }

        return $rows;
    }

    public static function shortenRight($text, $length, $ignore = []): string
    {
        if (mb_strlen(str_replace($ignore, '', $text)) <= $length) {
            return $text;
        }
        if (count($ignore) === 0 && $length > 3) {
            return mb_substr($text, 0, $length - 3) . "...";
        }
        $reg_Escaped = array_map(function($e) { return preg_quote($e, "/"); }, $ignore);
        $pattern = "/" . implode("|", $reg_Escaped) . "/";
        if ($length <= 3) {
            $text = preg_replace($pattern, "", $text);
            return mb_substr($text, 0, $length);
        }

        preg_match_all($pattern, $text, $matches);
        $count = 0;
        $result = '';
        $textWithoutIgnored = preg_replace($pattern, "\u{0000}", $text);
        for($i = 0; $i < mb_strlen($textWithoutIgnored); $i++) {
            if ($count < $length-3 || $textWithoutIgnored[$i] === "\u{0000}") {
                $result .= $textWithoutIgnored[$i];
            }
            if ($textWithoutIgnored[$i] != "\u{0000}") $count++;
        }
        foreach($matches[0] as $m) {
            $result = self::replaceFirst($result, "\u{0000}", $m);
        }
        return "$result...";
    }

    public static function shortenLeft($text, $length, $ignore = []): string
    {
        $ignoreReversed = array_map(function($e) { return strrev($e); }, $ignore);
        return strrev(self::shortenRight(strrev($text), $length, $ignoreReversed));
    }

    private static function replaceFirst($haystack, $needle, $replace) {
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            return substr_replace($haystack, $replace, $pos, strlen($needle));
        }
        return $haystack;
    }


    public static function leftPad($text, $length, $paddingChar = ' ', $ignore = []): string
    {
        return str_repeat($paddingChar, $length - mb_strlen(str_replace($ignore, '', $text))) . $text;
    }

    public static function rightPad($text, $length, $paddingChar = ' ', $ignore = []): string
    {
        return $text . str_repeat($paddingChar, $length - mb_strlen(str_replace($ignore, '', $text)));
    }

    public static function centerPad($text, $length, $paddingChar = ' ', $ignore = []): string
    {
        $padSize = $length - mb_strlen(str_replace($ignore, '', $text));
        $leftSize = $padSize % 2 === 0 ? $padSize / 2 : ($padSize - 1) / 2;
        $rightSize = $padSize % 2 === 0 ? $padSize / 2 : ($padSize + 1) / 2;
        return str_repeat($paddingChar, $leftSize) . $text . str_repeat(' ', $rightSize);
    }

    public static function leftAndRightPad($leftText, $rightText, $columnWidth, $paddingChar = ' ', $ignore = []): string
    {
        $repeatTimes = $columnWidth - mb_strlen(str_replace($ignore, '', $leftText)) - mb_strlen(str_replace($ignore, '', $rightText));
        return $leftText . str_repeat($paddingChar, $repeatTimes) . $rightText;
    }

    /**
     * Splits $line in tokens delimited by space with a maximum length of $maxLength
     *
     * @param string $line Text
     * @param int $maxLength maximum length of line
     * @return array
     */
    private static function splitTokens(string $line, int $maxLength): array
    {
        $tokens = explode(" ", $line);
        $newTokens = [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) > $maxLength) {
                $tokensWithMaxLength = str_split($token, $maxLength);
                $newTokens = array_merge($newTokens, $tokensWithMaxLength);
            } else {
                $newTokens[] = $token;
            }
        }
        return $newTokens;
    }
}