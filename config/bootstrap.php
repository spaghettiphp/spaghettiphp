<?php

// defines the root directory
define('SPAGHETTI_ROOT', dirname(dirname(__FILE__)));

// adds the root directory to the include path
set_include_path(SPAGHETTI_ROOT . PATH_SEPARATOR . get_include_path());


// includes core.common
require 'lib/core/common/Loader.php';
require 'lib/core/common/Config.php';
require 'lib/core/common/Inflector.php';
require 'lib/core/common/Error.php';
require 'lib/core/common/Utils.php';
require 'lib/core/common/Exceptions.php';
require 'lib/core/common/String.php';
require 'lib/core/common/Filesystem.php';
require 'lib/core/common/Validation.php';

// includes and initializes core.debug
require 'lib/core/debug/Debug.php';
Debug::errorHandler();

// includes core.dispatcher
require 'lib/core/dispatcher/Dispatcher.php';
require 'lib/core/dispatcher/Mapper.php';

// includes core.model
require 'lib/core/model/Model.php';
require 'lib/core/model/Connection.php';

// includes core.controller
require 'lib/core/controller/Controller.php';
require 'lib/core/controller/Component.php';

// includes core.view
require 'lib/core/view/View.php';
require 'lib/core/view/Helper.php';

// includes core.storage
require 'lib/core/storage/Cookie.php';
require 'lib/core/storage/Session.php';

// includes core.security
require 'lib/core/security/Security.php';
require 'lib/core/security/Sanitize.php';

// includes application's files
require 'app/controllers/app_controller.php';
require 'app/models/app_model.php';

// sets up the application with config files
require 'config/settings.php';
require 'config/routes.php';

// enable error reporting
Debug::reportErrors(Config::read('Debug.level'));