<?php

class MissingViewException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing View';
        $details = 'The view <code>' . $details['view'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}