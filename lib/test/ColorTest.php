<?php
namespace DIQA\Formatter;

use Exception;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    use Tools;

    /**
     * @throws Exception
     */
    public function testHighlightWord(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::CENTER_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );
        $config->highlightWord("OK", Config::GREEN);

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "[ERROR]"],
            ["row 3 column 1", "row 3 column 2", "[OK]"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
row 1 column 1              row 1 column 2                               [ERROR]
row 3 column 1              row 3 column 2                                  [OK]
EOT;
        $expectedOutput = str_replace("OK", Config::GREEN."OK".Config::NC, $expectedOutput);

        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    /**
     * @throws Exception
     */
    public function testHighlightWordWithColumn(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::CENTER_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );
        $config->highlightWord("OK", Config::GREEN, 2);

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "[ERROR]"],
            ["row 3 column 1", "row 3 OK column 2", "[OK]"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
row 1 column 1              row 1 column 2                               [ERROR]
row 3 column 1            row 3 OK column 2                                 [OK]
EOT;
        $expectedOutput = str_replace("[OK]", "[".Config::GREEN."OK".Config::NC."]", $expectedOutput);

        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    /**
     * @throws Exception
     */
    public function testHighlightWordBackground(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::CENTER_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );
        $config->highlightWord("OK", Config::BLACK_WITH_RED_BACKGROUND);

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "[ERROR]"],
            ["row 3 column 1", "row 3 column 2", "[OK]"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
row 1 column 1              row 1 column 2                               [ERROR]
row 3 column 1              row 3 column 2                                  [OK]
EOT;
        $expectedOutput = str_replace("OK", Config::BLACK_WITH_RED_BACKGROUND."OK".Config::NC, $expectedOutput);

        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }
}