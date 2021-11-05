<?php
namespace DIQA\Formatter;

trait Tools {

    static function normalize($output) {
        return str_replace("\r", "", $output);
    }
}