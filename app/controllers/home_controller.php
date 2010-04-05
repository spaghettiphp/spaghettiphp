<?php

class Posts extends AppModel {}

class HomeController extends AppController {
    public $uses = array('Posts');
    
    public function index() {
    }
}