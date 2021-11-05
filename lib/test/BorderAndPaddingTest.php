<?php
namespace DIQA\Formatter;

use PHPUnit\Framework\TestCase;

final class BorderAndPaddingTest extends TestCase
{
    use Tools;

    public function test3ColumnsWithBorderAndPadding(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::LEFT_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => true, 'border' => true]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "254,00"],
            ["row 2 column 1", "row 2 column 2", "1.233,00"],
            ["row 3 column 1", "row 3 column 2 ", "424,21"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
┌──────────────────┬─────────────────────────────┬─────────────────────────────┐
│ row 1 column 1   │ row 1 column 2              │                      254,00 │
├──────────────────┼─────────────────────────────┼─────────────────────────────┤
│ row 2 column 1   │ row 2 column 2              │                    1.233,00 │
├──────────────────┼─────────────────────────────┼─────────────────────────────┤
│ row 3 column 1   │ row 3 column 2              │                      424,21 │
└──────────────────┴─────────────────────────────┴─────────────────────────────┘
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    public function test3ColumnsWithBorder(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::LEFT_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => true]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "254,00"],
            ["row 2 column 1", "row 2 column 2", "1.233,00"],
            ["row 3 column 1", "row 3 column 2 ", "424,21"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
┌──────────────────┬─────────────────────────────┬─────────────────────────────┐
│row 1 column 1    │row 1 column 2               │                       254,00│
├──────────────────┼─────────────────────────────┼─────────────────────────────┤
│row 2 column 1    │row 2 column 2               │                     1.233,00│
├──────────────────┼─────────────────────────────┼─────────────────────────────┤
│row 3 column 1    │row 3 column 2               │                       424,21│
└──────────────────┴─────────────────────────────┴─────────────────────────────┘
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    public function test3Columns(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::LEFT_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "254,00"],
            ["row 2 column 1", "row 2 column 2", "1.233,00"],
            ["row 3 column 1", "row 3 column 2 ", "424,21"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
row 1 column 1      row 1 column 2                                        254,00
row 2 column 1      row 2 column 2                                      1.233,00
row 3 column 1      row 3 column 2                                        424,21
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    public function test3ColumnsWithPadding(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::LEFT_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => true, 'border' => false]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "254,00"],
            ["row 2 column 1", "row 2 column 2", "1.233,00"],
            ["row 3 column 1", "row 3 column 2 ", "424,21"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
 row 1 column 1      row 1 column 2                                      254,00 
 row 2 column 1      row 2 column 2                                    1.233,00 
 row 3 column 1      row 3 column 2                                      424,21 
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

}