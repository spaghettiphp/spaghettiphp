<?php

class MissingViewException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing View';
        $details = 'The view <code>' . $details['view'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingLayoutException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Layout';
        $details = 'The layout <code>' . $details['layout'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingElementException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Element';
        $details = 'The element <code>' . $details['element'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingHelperException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Helper';
        $details = 'The helper <code>' . $details['helper'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}