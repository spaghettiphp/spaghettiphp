<?php

define('SPAGHETTI_ROOT', dirname(dirname(__FILE__)));
define('SPAGHETTI_APP', SPAGHETTI_ROOT . '/app');

set_include_path(SPAGHETTI_ROOT . PATH_SEPARATOR . get_include_path());

define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST']);

require 'lib/core/common/Object.php';
require 'lib/core/common/Loader.php';
require 'lib/core/common/Config.php';
require 'lib/core/common/Inflector.php';
require 'lib/core/common/Error.php';
require 'lib/core/common/Utils.php';

require 'lib/core/debug/Debug.php';

require 'lib/core/dispatcher/Dispatcher.php';
require 'lib/core/dispatcher/Mapper.php';

require 'lib/core/model/Model.php';
require 'lib/core/model/Connection.php';

require 'lib/core/controller/Controller.php';
require 'lib/core/controller/Component.php';

require 'lib/core/view/View.php';
require 'lib/core/view/Helper.php';

require 'lib/core/storage/Cookie.php';
require 'lib/core/storage/Session.php';

require 'lib/core/security/Security.php';
require 'lib/core/security/Sanitize.php';

require 'lib/core/class_registry.php';
require 'lib/core/validation.php';

require 'config/settings.php';
require 'config/routes.php';

require 'app/controllers/app_controller.php';
require 'app/models/app_model.php';