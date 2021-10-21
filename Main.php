<?php

$loader = require __DIR__ . '/vendor/autoload.php';
$loader->addPsr4('Acme\\Test\\', __DIR__);

$config = new DIQA\Formatter\Config([20, 30, 20],
    [\DIQA\Formatter\Config::LEFT_ALIGN,
    \DIQA\Formatter\Config::CENTER_ALIGN,
    \DIQA\Formatter\Config::RIGHT_ALIGN],
    ['padding' => true]);
$formatter = new \DIQA\Formatter\Formatter($config);

$formattedtext = $formatter->format([
    ["row 1 column 1", "row 1 column 2", "254,00"],
    [\DIQA\Formatter\Formatter::LINE_SEPARATOR, "", ""],
    ["row 2 column 1", "row 2 column 2 das is t ein dsdsdsfdfdfdfjkdfjkdjfkdjkfjdkfjdkjfkddhsjdhsjhdjs", "1.233,00"],
    ["","d",""],
    ["row 3 column 1", "row 3 column 2", "424,21"],
]);

print "\n";
print $formattedtext;
print "\n";


//$t = \DIQA\Formatter\TextUtilities::breakText("row 2 column 2 das is t ein dsdsdsfdfdfdfjkdfjkdjfkdjkfjdkfjdkjfkddhsjdhsjhdjs", 20);
//print_r($t);



