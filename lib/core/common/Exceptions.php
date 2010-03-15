<?php

class PhpErrorException extends Exception {
    public function __construct($message, $code, $file, $line, $context) {
        parent::__construct($message, $code);
    }
}