<?php

// defines the root directory
define('SPAGHETTI_ROOT', dirname(dirname(__FILE__)));

// adds the root directory to the include path
set_include_path(SPAGHETTI_ROOT . PATH_SEPARATOR . get_include_path());

// includes core.common
require 'lib/core/common/Config.php';
require 'lib/core/common/Inflector.php';
require 'lib/core/common/Utils.php';
require 'lib/core/common/Exceptions.php';
require 'lib/core/common/String.php';
require 'lib/core/common/Filesystem.php';
require 'lib/core/common/Hookable.php';

// includes and initializes core.debug
require 'lib/core/debug/Debug.php';

/**
 * Debug::errorHandler() can cause some trouble, so it's disabled by default.
 * Uncomment the following line if you want your errors to throw exceptions.
 */
// Debug::errorHandler();

// includes core.dispatcher
require 'lib/core/dispatcher/Dispatcher.php';
require 'lib/core/dispatcher/Mapper.php';

// includes core.model
require 'lib/core/model/Model.php';

// includes core.controller
require 'lib/core/controller/Controller.php';

// includes core.view
require 'lib/core/view/View.php';

// includes application's files
require 'app/controllers/app_controller.php';
require 'app/models/app_model.php';
