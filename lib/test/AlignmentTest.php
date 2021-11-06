<?php
namespace DIQA\Formatter;

use PHPUnit\Framework\TestCase;

final class AlignmentTest extends TestCase
{
    use Tools;

    public function testAllAlignments(): void
    {
        $config = new Config(
            [20, 30, 30],
            [Config::LEFT_ALIGN, Config::CENTER_ALIGN, Config::RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            ["row 1 column 1", "row 1 column 2", "254,00"],
            ["row 2 column 1", "row 2 column 2", "1.233,00"],
            ["row 3 column 1", "row 3 column 2", "424,21"],
        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
row 1 column 1              row 1 column 2                                254,00
row 2 column 1              row 2 column 2                              1.233,00
row 3 column 1              row 3 column 2                                424,21
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    public function testLeftAndRightAlignment(): void
    {
        $config = new Config(
            [20],
            [Config::LEFT_AND_RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            [["left", "right"]]

        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
left           right
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }

    public function testLeftAndRightTooLongAlignment(): void
    {
        $config = new Config(
            [20],
            [Config::LEFT_AND_RIGHT_ALIGN],
            ['padding' => false, 'border' => false]
        );

        $formatter = new Formatter($config);

        $formattedOutput = $formatter->format([
            [["left and a super long line", "right and a super long line"]]

        ]);

        print "\n$formattedOutput";

        $expectedOutput = <<<EOT
left and a super    
long line right and 
a super long line   
EOT;
        $this->assertEquals(
            self::normalize($expectedOutput),
            self::normalize($formattedOutput)
        );
    }


}