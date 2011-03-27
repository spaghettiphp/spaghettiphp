<?php

require_once 'lib/utils/Date.php';

class DateHelper extends Helper {
    public function format($date, $format) {
        return Date::format($date, $format);
    }

    public function timeAgo($date) {
        return Date::timeAgo($date);
    }
}