<?php
namespace DIQA\Formatter;

class TextUtilities {

    /**
     * Breaks text in multiple lines of $maxLength. Tries to preserve words.
     *
     * @param string $line Text
     * @param int $maxLength maximum length of line
     * @return array
     */
    public static function breakText(string $line, int $maxLength): array
    {
        $rows = [];
        $tokens = self::splitTokens($line, $maxLength);

        $line = '';
        $token = reset($tokens);
        do {
            if (mb_strlen($line.$token) > $maxLength) {
                $rows[] = $line;
                $line = $token;
            } else {
                $line .= $line == '' ? $token : ' '.$token;
            }
        } while (($token = next($tokens)) !== false);
        if ($line != '') {
            $rows[] = $line;
        }

        return $rows;
    }

    public static function leftPad($text, $length): string
    {
        return str_repeat(' ', $length - mb_strlen($text)) . $text;
    }

    public static function rightPad($text, $length): string
    {
        return $text . str_repeat(' ', $length - mb_strlen($text));
    }

    public static function centerPad($text, $length): string
    {
        $padSize = $length - mb_strlen($text);
        $leftSize = $padSize % 2 === 0 ? $padSize / 2 : ($padSize-1) / 2;
        $rightSize = $padSize % 2 === 0 ? $padSize / 2 : ($padSize+1) / 2;
        return str_repeat(' ', $leftSize) . $text . str_repeat(' ', $rightSize);
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