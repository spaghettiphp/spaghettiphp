<?php

class MissingControllerException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Controller';
        $details = 'The controller <code>' .  $details['controller']. '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingActionException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Action';
        $details = 'The action <code>' . $details['controller'] . '::' .  $details['action']. '()</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingComponentException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Component';
        $details = 'The component <code>' . $details['component'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}