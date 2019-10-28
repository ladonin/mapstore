<?php
echo("Данный код имеет ознакомительный характер, поскольку часть классов и методов удалена");
exit();

date_default_timezone_set('UTC');

define('MY_SERVICES_NAME', 'services');
define('MY_TIME', time());
define('MY_START_TIME', microtime(1));
define('MY_DS', DIRECTORY_SEPARATOR);
define('MY_DOCROOT', realpath(dirname(__FILE__)) . MY_DS);
define('MY_PROTOCOL', 'http://');
define('MY_DOMEN_NAME', $_SERVER['SERVER_NAME']);
define('MY_DOMEN', MY_PROTOCOL . MY_DOMEN_NAME);
define('MY_APPLICATION_DIR', MY_DOCROOT . 'application' . MY_DS);
define('MY_SERVICES_DIR', MY_APPLICATION_DIR . MY_SERVICES_NAME . MY_DS);
define('MY_VIEWS_DIR', MY_APPLICATION_DIR . 'views' . MY_DS);
define('MY_JS_DIR', MY_DOCROOT . 'javascript' . MY_DS);
define('MY_LOG_MYSQL_PATH', 'log' . MY_DS . 'mysql.log');
define('MY_LOG_APPLICATION_PATH', 'log' . MY_DS . 'application.log');
define('MY_LOG_APPLICATION_TYPE', 'app');
define('MY_LOG_MYSQL_TYPE', 'mysql');
define('MY_CRYPT_HASH_ALGORYTM_CODE', '$6$');
define('MY_FILES_DIR_NAME', 'files');
define('MY_FILES_DIR', MY_DOCROOT . MY_FILES_DIR_NAME . MY_DS);
define('MY_TEMP_FILES_DIR', MY_FILES_DIR . 'temp' . MY_DS);
define('MY_FUNCTIONS_DIR', MY_APPLICATION_DIR . 'functions' . MY_DS);
define('MY_LOG_DIR', MY_APPLICATION_DIR . 'log' . MY_DS);
define('MY_FILES_URL', MY_DOMEN . '/files/');
define('MY_FILES_MAP_URL', MY_DOMEN . '/files/map/');
define('MY_IMG_URL', MY_DOMEN . '/img/');
define('MY_SERVICE_IMGS_URL', '/imgs/');

require_once(MY_APPLICATION_DIR . 'config' . MY_DS . 'constants'. MY_DS .'generic.php');

session_start();

$config = require_once(MY_APPLICATION_DIR . 'config' . MY_DS . 'config.php');

if ($config['debug'] === 1) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}
// Общие функции, доступные вне приложения (не используют обертку Security)
require_once(MY_APPLICATION_DIR . 'functions' . MY_DS . 'generic.php');

// Базовые функции платформы (используются внутри приложения)
require_once(MY_APPLICATION_DIR . 'functions' . MY_DS . 'base' . MY_DS . 'base.php');

// Пользовательские функции (используются внутри приложения)
require_once(MY_APPLICATION_DIR . 'functions' . MY_DS . 'app' . MY_DS . 'app.php');

// Основной класс Security, отвечающий за запуск приложения
require_once MY_DOCROOT . 'application' . MY_DS . 'modules' . MY_DS . 'base' . MY_DS . 'security' . MY_DS . 'security.php';

use \modules\base\Security\Security;

Security::get_instance()->run($config);

// Отладка приложения
if (($config['debug'] === 1) && !is_ajax()) {
    echo ("<b>Отладка:</b>");
    echo ("<br>");
    echo ("<b>Время выполнения:</b> " . round((microtime(1) - MY_START_TIME), 5) . " сек<br>");
    echo ($autoload_history);
}