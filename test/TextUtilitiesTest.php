<?php
namespace DIQA\Formatter;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Report\Text;

final class TextUtilitiesTest extends TestCase
{
    use Tools;

    public function testTextBreak(): void
    {
        $lines = TextUtilities::breakText("left and a super long line", 20);
        $this->assertEquals("left and a super", $lines[0]);
        $this->assertEquals("long line", $lines[1]);
    }

    public function testTextBreakNoWords(): void
    {
        $lines = TextUtilities::breakText("abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz", 20);
        $this->assertEquals("abcdefghijklmnopqrst", $lines[0]);
        $this->assertEquals("uvwxyzabcdefghijklmn", $lines[1]);
        $this->assertEquals("opqrstuvwxyz", $lines[2]);
    }

    public function testShortenRight(): void {
        $text = TextUtilities::shortenRight("abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz", 20);
        $this->assertEquals("abcdefghijklmnopq...", $text);
    }

    public function testShortenLeft(): void {
        $text = TextUtilities::shortenLeft("abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz", 20);
        $this->assertEquals("...jklmnopqrstuvwxyz", $text);
    }
}