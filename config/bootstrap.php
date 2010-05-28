<?php

define('SPAGHETTI_ROOT', dirname(dirname(__FILE__)));

set_include_path(SPAGHETTI_ROOT . PATH_SEPARATOR . get_include_path());

require 'lib/core/common/Loader.php';
require 'lib/core/common/Config.php';
require 'lib/core/common/Inflector.php';
require 'lib/core/common/Error.php';
require 'lib/core/common/Utils.php';
require 'lib/core/common/Exceptions.php';
require 'lib/core/common/String.php';
require 'lib/core/common/Filesystem.php';
require 'lib/core/common/Validation.php';

require 'lib/core/debug/Debug.php';

Debug::errorHandler();

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


require 'app/controllers/app_controller.php';
require 'app/models/app_model.php';

require 'config/settings.php';
require 'config/routes.php';

Debug::reportErrors(Config::read('Debug.level'));