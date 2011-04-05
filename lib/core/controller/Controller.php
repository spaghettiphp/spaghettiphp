<?php

require 'lib/core/controller/Exceptions.php';

/*
    Class: Controller

    Controllers are the core of a web request. They provide actions that
    will be executed and (generally) render a view that will be sent
    back to the user.

    An action is just a public method on your controller. They're available
    automatically to the user throgh the <Mapper>. Any protected or private
    method will NOT be accessible to requests.

    By default, only your <AppController> will inherit Controller directly.
    All other controllers will inherit AppController, that can contain
    specific rules such as filtering and access control.

    A typical controller will look something like this

    (start code)
    class ArticlesController extends AppController {
        public function index() {
            $this->articles = $this->Articles->all();
        }

        public function view($id = null) {
            $this->article = $this->Articles->firstById($id);
        }
    }
    (end)

    By default, all actions render a view in app/views. A call to the
    index action in the ArticlesController, for example, will render
    the view app/views/articles/index.htm.php.

    All controllers also can load models for you. By default, the
    controller loads the model with the same. Be aware that, if the
    model does not exist, the controller will throw an exception.
    If you don't want the controller to load models, or if you want
    to specific models, use <Controller::$uses>.

    Dependencies:
        - <Model>
        - <View>
        - <Filesystem>
        - <Inflector>

    Todo:
        - Remove all current non-common dependencies. Controller should
            be model and view agnostic.
*/
class Controller extends Hookable {
    /*
        Variable: $render

        Specifies if the controller should render output automatically.
        Usually this will be true, but if you want to generate custom
        output you can set this to false.
    */
    protected $render = true;

    /*
        Variable: $data

        Contains $_POST and $_FILES data, merged into a single array.
        This is what you should use when getting data from the user.
        A common pattern is checking if there is data in this variable
        like this

        (start code)
        if(!empty($this->data)) {
            new Articles($this->data)->save();
        }
        (end)
    */
    protected $data = array();

    /*
        Variable: $layout

        Layout used for rendering the current view. By default, 'default'
        layout will be rendered. If you don't want a layout rendered
        with your view, set this to false.
    */
    protected $layout = 'default';

    /*
        Variable: $beforeFilter

        beforeFilters are methods run before a controller action. They
        may stop a action from running, for example when a user does not
        have permission to access certain actions.

        (start code)
        protected $beforeFilter = array('requireLogin');

        protected function requireLogin() {
            if(!$this->loggedIn()) {
                // Controller::redirect stops the action from running
                // and redirects the user to a login page
                $this->redirect('/users/login');
            }
        }
        (end)
    */
    protected $beforeFilter = array();

    /*
        Variable: $beforeRender

        beforeRenders are methods run after a controller action has been
        executed, but before they render any output. You can use it to
        suppress output for some reason.
    */
    protected $beforeRender = array();

    /*
        Variable: $afterFilter

        afterFilters are methods run after the controller executed an
        action and sent output to the browser.
    */
    protected $afterFilter = array();

    /*
        Variable: $uses

        Defines which models the controller will load. When null, the
        controller will load only the model with the same name of the
        controller. When an empty array, the controller won't load any
        model.

        You can load as many models as you want, but be aware that this
        can decrease your application's performance. So the rule is to
        include here only models you need in all (or almost all)
        actions, and manually load less used models.

        Be aware that, when we start using autoload, this feature will
        be removed, so don't rely on this.

        See Also:
            <Controller::loadModel>, <Model::load>
    */
    protected $uses = null;

    /*
        Variable: $name

        Defines the name of the controller. Shouldn't be used directly.
        It is used just for loading a default model if none is provided
        and will be removed in the near future.
    */
    protected $name = null;

    /*
        Variable: $params

        Keeps the parameters parsed by <Mapper>. Shouldn't be used
        directly, use <Controller::param> instead.

        See Also:
            <Controller::param>
    */
    protected $params = array();

    /*
        Variable: $view

        Data to be sent to views. Should not be used directly. Use the
        appropriate methods for this.

        See Also:
            <Controller::__get>, <Controller::__set>, <Controller::get>,
            <Controller::set>
    */
    protected $view = array();

    /*
        Variable: $models

        Keeps the models attached to the controller. Shouldn't be used
        directly. Use the appropriate methods for this. This will be
        removed when we start using autoload.

        See Also:
            <Controller::__get>, <Controller::loadModel>, <Model::load>
    */
    protected $models = array();

    /*
        Method: __construct

        Load models defined in <Controller::$uses> and sets
        <Controller::$data>
    */
    public function __construct() {
        if(is_null($this->name)) {
            $this->name = $this->name();
        }

        if(is_null($this->uses)) {
            if($this->name == 'App') {
                $this->uses = array();
            }
            else {
                $this->uses = array($this->name);
            }
        }

        array_map(array($this, 'loadModel'), $this->uses);
        $this->data = array_merge_recursive($_POST, $_FILES);
    }

    /*
        Method: __set

        Magic method to set values to be sent to the view. It enables
        you to send values by setting instance variables in the
        controller

        (start code)
        public function index() {
            $this->articles = $this->Articles->all();
            // will be available to the view as $articles
        }
        (end)

        Params:
            $name - name of the variable to be sent to the view.
            $value - value to be sent to the view.
    */
    public function __set($name, $value) {
        $this->view[$name] = $value;
    }

    /*
        Method: __get

        Magic methods to get values sent to the view. It enables you to
        get values by using instance variables in the controller

        (start code)
        public function edit($id = null) {
            $this->user = $this->Users->firstById($id);

            if(!empty($this->data)) {
                $this->user->updateAttributes($this->data);
                $this->user->save();
            }
        }
        (end)

        This method also allows you to use the $this->Model syntax, but
        this will be removed in the future.

        Params:
            $name - name of the value to be read.

        Returns:
            The value sent to the view, or the model instance.

        Throws:
            - Runtime exception if the attribute does not exist.
    */
    public function __get($name) {
        $attrs = array('models', 'view');

        foreach($attrs as $attr) {
            if(array_key_exists($name, $this->{$attr})) {
                return $this->{$attr}[$name];
            }
        }

        throw new RuntimeException(get_class($this) . '->' . $name . ' does not exist.');
    }

    /*
        Method: load

        Loads a controller. Typically used by the Dispatcher.

        Params:
            $name - class name of the controller to be loaded.
            $instance - true to return an instance of the controller,
                false if you just want the class loaded.

        Returns:
            If $instance == false, returns true if the controller was
            loaded. If $instance == true, returns an instance of the
            controller.

        Throws:
            - MissingControllerException if the controller can't be
            found.

        Todo:
            - Replace by auto-loading.
    */
    public static function load($name, $instance = false) {
        $filename = 'app/controllers/' . Inflector::underscore($name) . '.php';

        if(!class_exists($name) && Filesystem::exists($filename)) {
            require_once $filename;
        }

        if(class_exists($name)) {
            if($instance) {
                return new $name();
            }
            else {
                return true;
            }
        }
        else {
            throw new MissingControllerException($name . ' could not be found');
        }
    }

    public static function hasViewForAction($request) {
        return Filesystem::exists('app/views/' . $request['controller'] . '/' . $request['action'] . '.' . $request['extension']);
    }

    public function name() {
        $classname = get_class($this);
        $lenght = strpos($classname, 'Controller');

        return substr($classname, 0, $lenght);
    }

    public function callAction($request) {
        if($this->hasAction($request['action']) || self::hasViewForAction($request)) {
            return $this->dispatch($request);
        }
        else {
            throw new MissingActionException('The action ' . $request['controller'] . '::' . $request['action'] . '() could not be found.');
        }
    }

    public function hasAction($action) {
        $class = new ReflectionClass(get_class($this));
        if($class->hasMethod($action)) {
            $method = $class->getMethod($action);
            return $method->class != 'Controller' && $method->isPublic();
        }
        else {
            return false;
        }
    }

    protected function dispatch($request) {
        $this->params = $request;
        $this->fireAction('beforeFilter');
        $view = View::path($request);

        if($this->hasAction($request['action'])) {
            call_user_func_array(array($this, $request['action']), $request['params']);
            $view = null;
        }

        $output = '';
        if($this->render) {
            $this->fireAction('beforeRender');
            $output = $this->render($view);
        }

        $this->fireAction('afterFilter');

        return $output;
    }

    /*
        Method: loadModel

        Loads a model and attaches it to the controller. It is not
        considered a good practice to include all models you will ever
        need in <Controller::$uses>. If you need models that are not
        used throughout your controller, you can load them using this
        method.

        Be aware, though, that is generally better to use <Model::load>
        itself if you don't need to use the instance more than once in
        your action, because it does not have the overhead to attach
        the model to the controller. Also, this method will be removed
        in the next versions in favor of autloading, so don't rely on
        this.

        Params:
            $model - camel-cased name of the model to be loaded.

        Returns:
            The model's instance.
    */
    protected function loadModel($model) {
        return $this->models[$model] = Model::load($model);
    }

    /*
        Method: setAction

        Re-route the controller to execute another action. Note that it
        does NOT stop the current action, so every statement after the
        call to setAction will still be executed.

        Params:
            $action - new action to be executed.
    */
    public function setAction($action) {
        $args = func_get_args();
        $this->params['action'] = array_shift($args);

        return call_user_func_array(array($this, $action), $args);
    }

    /*
        Method: render

        Renders a view.

        Params:
            $action - action to render. null will render the current
            action.

        Returns:
            The content rendered by the view.
    */
    public function render($action = null) {
        $view = new View;
        $layout = $this->layout;
        $view->controller = $this;
        $this->render = false;

        if(is_null($action)) {
            $action = Inflector::underscore($this->name) . '/' . $this->params['action'] . '.' . $this->params['extension'];
        }

        return $view->render($action, $this->view, $layout);
    }

    /*
        Method: redirect

        Redirects the user to another location.

        Params:
            $url - location to be redirected to.
            $status - HTTP status code to be sent with the redirect
                header.
            $exit - if true, stops the execution of the controller.
    */
    public function redirect($url, $status = null, $exit = true) {
        $this->render = false;
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out'
        );

        if(!is_null($status) && isset($codes[$status])) {
            header('HTTP/1.1 ' . $status . ' ' . $codes[$status]);
        }

        header('Location: ' . Mapper::url($url, true));
        if($exit) $this->stop();
    }

    /*
        Method: set

        Sets a value to be sent to the view. It is not commonly used
        anymore, and was abandoned in favor of <Controller::__set>,
        which is much more convenient and readable. Use this only if
        you need extra performance.

        Params:
            $name - name of the variable to be sent to the view. Can
                also be an array where the keys are the name of the
                variables. In this case, $value will be ignored.
            $value - value to be sent to the view.
    */
    public function set($name, $value = null) {
        if(is_array($name)) {
            foreach($name as $key => $value) {
                $this->set($key, $value);
            }
        }
        else {
            $this->view[$name] = $value;
        }
    }

    /*
        Method: get

        Gets a value of a variable sent to the view. It is not commonly
        used anymore, and was abandoned in favor of <Controller::__get>,
        which is much more convinent and readable. Use this only if you
        need extra performance.

        Params:
            $var - name of the value to be read

        Returns:
            The value sent to the view. null if it was not defined yet.
    */
    public function get($var) {
        if(array_key_exists($var, $this->view)) {
            return $this->view[$var];
        }
    }

    /*
        Method: param

        Returns the value of a param. Params could be the params sent
        by the <Mapper> (check <Mapper::parse> for more info) or named
        parameters in the URL.

        Named parameters are just a pretty name for a query string. But
        not only that, <Mapper> also understand an alternative notation
        for named parameters. For example, both URLs are equivalent

        > /articles/index?sort=title&order=asc
        > /articles/index/sort:title/order:asc

        Sometimes you want a value for a parameter even when it was not
        set in the URL. For example, you would want to have a default
        field to sort. Use the $default param to provide this default
        value.

        Params:
            $key - name of the param to be read.
            $default - default value of the param if none was provided.

        Returns:
            The value of the param in the URL or the default value if
            it was not provided.
    */
    public function param($key, $default = null) {
        if(array_key_exists($key, $this->params['named'])) {
            return $this->params['named'][$key];
        }
        elseif(in_array($key, array_keys($this->params))) {
            return $this->params[$key];
        }
        else {
            return $default;
        }
    }

    /*
        Method: page

        Returns the current page. Used for pagination. It is just a
        conveniency method for calling Controller::param('page', 1).

        Params:
            $param - name of the param you use for pagination. Default
            is 'page'.

        Returns:
            The value of the page param. 1 if it was not explicitly
            defined.
    */
    public function page($param = 'page') {
        return (integer) $this->param($param, 1);
    }

    /*
        Method: isXhr

        Checks whether the request was made through XMLHttpRequest or
        not.

        Returns:
            True if the request was made by a XMLHttpRequest (provided
            that the correct HTTP_X_REQUESTED_WITH header was sent),
            false otherwise.
    */
    public function isXhr() {
        if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
            return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        }
        else {
            return false;
        }
    }

    /*
        Method: stop

        Stops the execution of the controller. After this method is
        called, no code will be executed anymore, and any rendered
        output will be sent to the user.
    */
    public function stop() {
        exit(0);
    }
}