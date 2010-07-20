<?php

class MissingModelException extends MissingException {
    public function __construct($details = array()) {
        $message = 'Missing Model';
        $details = 'The model <code>' . $details['model'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingTableException extends InternalErrorException {
    public function __construct($details = array()) {
        $message = 'Missing Table';
        $details = 'The table <code>' . $details['table'] . '</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}

class MissingBehaviorMethodException extends InternalErrorException {
    public function __construct($details = array()) {
        $message = 'Missing Behavior Method';
        $details = 'The hook <code>' . $details['hook'] . '</code> could not find method <code>' . $details['behavior'] . '::' . $details['method'] . '()</code> could not be found.';
        parent::__construct($message, 0, $details);
    }
}