<?php
/*
 * Class vendor\Component
 *
 * Базовый класс, от которого должны наследоваться все остальные классы
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor;

use \modules\base\Security\Security;

abstract class Component
{

    /*
     * Тип устройства - мобильное или декстопное
     *
     * @var string
     */
    static private $device_type = null;

    /*
     * Имя директории устройства
     *
     * @var string
     */
    static private $device_dir = null;

    /*
     * Массив общей конфигурации
     *
     * @var array
     */
    static private $config = null;

    /*
     * Текущий url
     *
     * @var string
     */
    static private $self_url = null;

    /*
     * Query string текущего url
     *
     * @var string
     */
    static private $query_string = null;

    /*
     * Основные GET переменные текущего url без query string
     *
     * @var string
     */
    static private $self_url_without_query_string = null;

    /*
     * Массив ошибок, возниающих в процессе выполнения
     *
     * @var array
     */
    static protected $errors = array();

    /*
     * Если установлено, то редирект на этот url после выполнения всех действий
     *
     * @var string
     */
    static private $redirect_url = null;

    /*
     * Установка url редиректа
     *
     * @param $url - url редиректа
     */
    protected function set_redirect_url($url)
    {
        if (is_null(self::$redirect_url)) {
            self::$redirect_url = $url;
        }
    }

    /*
     * Получение всех GET переменных
     *
     * @return array
     */
    public function get_get_vars()
    {
        $security = \modules\base\Security\Security::get_instance();
        $get_vars = $security->get_get_vars();
        return $get_vars;
    }

    /*
     * Получение значения отдельной GET переменной
     *
     * @param $var_name - имя переменной
     *
     * @return string
     */
    public function get_get_var($var_name)
    {
        $security = \modules\base\Security\Security::get_instance();
        $get_vars = $security->get_get_vars();
        if (isset($get_vars[$var_name])) {
            return $get_vars[$var_name];
        }
        return null;
    }

    /*
     * Получение типа устройктва
     *
     * @return string
     */
    public static function get_device_type()
    {
        if (is_null(self::$device_type)) {
            $detect = new \components\app\Device_Detect;
            // Если мобильное устройство (телефон или планшет).
            if (($detect->isMobile()) && (!$detect->isTablet())) {
                self::$device_type = MY_DEVICE_MOBILE_TYPE_CODE;
            } else {
                self::$device_type = MY_DEVICE_DESCTOP_TYPE_CODE;
            }
        }
        return self::$device_type;
    }

    /*
     * Получение названия папки устройктва
     *
     * @return string
     */
    protected static function get_device_dir()
    {
        if (is_null(self::$device_dir)) {
            self::$device_dir = (self::get_device_type() === MY_DEVICE_MOBILE_TYPE_CODE) ? MY_DIR_MOBILE_NAME : MY_DIR_DESCTOP_NAME;
        }
        return self::$device_dir;
    }

    /*
     * Получение url редиректа, если он задан
     *
     * @return string
     */
    protected function get_redirect_url()
    {
        if (is_null(self::$redirect_url)) {
            return null;
        }

        return self::$redirect_url;
    }

    /*
     * Запись текущего url
     *
     * @param $url - url
     */
    protected function set_self_url($url)
    {
        if (is_null(self::$self_url)) {
            self::$self_url = $url;
        }
    }

    /*
     * Запись query string
     *
     * @param string $query_string - query string
     */
    protected function set_query_string($query_string)
    {
        if (is_null(self::$query_string)) {
            self::$query_string = $query_string;
        }
    }

    /*
     * Запись основных GET переменных без query string
     *
     * @param string $url - url (var1, var2, var3, var4)
     */
    protected function set_self_url_without_query_string($url)
    {
        if (is_null(self::$self_url_without_query_string)) {
            self::$self_url_without_query_string = $url;
        }
    }

    /*
     * Получение основных GET переменных без query string
     *
     * @return string
     */
    public static function get_self_url_without_query_string()
    {
        return self::$self_url_without_query_string;
    }

    /*
     * Получение GET переменных query string
     *
     * @return string
     */
    public static function get_query_string()
    {
        return self::$query_string;
    }

    /*
     * Получение текущего url
     *
     * @return string
     */
    public static function get_self_url()
    {
        return self::$self_url;
    }

    /*
     * Получение основного конфигурационного массива
     *
     * @return string
     */
    public static function get_config()
    {
        return self::$config;
    }

    /*
     * Одноразовая запись конфигурационного массива
     *
     * @param array $config - конфигурационные данные
     */
    protected function set_config($config)
    {
        if (is_null(self::$config)) {
            self::$config = $config;
        }
    }

    /*
     * Запись в лог
     *
     * @param array $comment - комментарий лога
     */
    public static function log($comment = '')
    {
        $debug = debug_backtrace();
        // убираем 2 ненужных больших элемента
        array_pop($debug);
        array_pop($debug);
        // первый тоже не нужен
        array_shift($debug);
        $log = array();
        $log['[NOTICE]'] = array(
            'debug' => json_encode($debug),
            'comment' => $comment ? $comment : 'no comment');

        \my_write_to_log(MY_LOG_APPLICATION_PATH, json_encode($log));
    }

    /*
     * Вызов ошибки
     *
     * @param integer $code - код ошибки (внутренний)
     * @param string $type - тип ошибки (приложение, база данных)
     * @param boolean $generic_error - общая ошибка или ошибка сервиса, куда писать лог в общую папку и папку сервиса
     */
    public static function set_error($code, $type = MY_LOG_APPLICATION_TYPE, $generic_error = false)
    {
        self::$errors[$code] = true;

        if ($type === MY_LOG_MYSQL_TYPE) {
            $path = MY_LOG_MYSQL_PATH;
        } else if ($type === MY_LOG_APPLICATION_TYPE) {
            $path = MY_LOG_APPLICATION_PATH;
        }
        \my_write_to_log($path, json_encode(self::$errors), $generic_error);
        throw new \Exception();
    }

    /*
     * Остановка сценария и откат без указания ошибки и логирования.
     * Обычно применяется в ajax запросах, если не ввели пароль и т.д.,
     * Текст ошибки выводится с помощью echo() перед вызовом этого метода roolback()
     */
    public function rollback()
    {
        throw new \Exception();
    }

    /*
     * Вызов ошибки с добавлением комментария
     *
     * @param array $var массив данных ошибки (
     *    0 => код ошибки (внутренний),
     *    1 => текст комментария
     * )
     * @param string $type - тип ошибки (приложение, база данных)
     * @param boolean $generic_error - общая ошибка или ошибка сервиса, куда писать лог в общую папку и папку сервиса
     */
    public static function concrete_error(array $var, $type = MY_LOG_APPLICATION_TYPE, $generic_error = false)
    {
        $debug = debug_backtrace();

        // убираем 3 ненужных больших элемента
        array_pop($debug);
        array_pop($debug);
        array_pop($debug);

        self::$errors[$var[0] . ' -> concrete'][] = array(
            'debug' => json_encode($debug),
            'comment' => @$var[1] ? $var[1] : 'no comment');

        self::set_error($var[0], $type, $generic_error);
    }

    /*
     * Подготавливаем view для ajax запроса
     *
     * @param array $data - данные сгенерированные контроллером для view
     * @param boolean $use_device_type - использовать блоки из общей папки или из папки устройства (mobile, desctop)
     *
     * @return string - готовый html код
     */
    public static function prepare_ajax_view($data, $use_device_type = false)
    {
        $security = \modules\base\Security\Security::get_instance();

        // Without 'ajax' word
        $action_name = substr(my_pass_through(@$security->get_action()), 5);
        $dir = $use_device_type ? self::get_device_dir() : MY_DIR_GENERIC_NAME;
        ob_start();
        require_once (\MY_APPLICATION_DIR
                . 'views'
                . \MY_DS
                . $dir
                . \MY_DS
                . 'ajax'
                . \MY_DS
                . my_pass_through(@$security->get_controller()) . "/" . $action_name . '.php');
        return ob_get_clean();
    }

    /*
     * Возвращает текст согласно установленному языку
     *
     * @param string $adress - путь до текста
     * @param array $vars - переменные, которые можно вставлять в текст, если они в нем поддерживаются
     *
     * @return string
     */
    public static function trace($adress, $vars = null)
    {
        $class_name = self::$config['language']['class'];

        if (\class_exists($class_name)) {
            return $class_name::get_instance()->get_text($adress, $vars);
        } else {
            self::set_error(MY_ERROR_LANGUAGE_MODEL_NOT_FOUND);
        }
    }

    /*
     * Преобразуем путь по его коду (установлен в конфиге) в http mvc адрес
     *
     * @return string
     */
    public static function get_path($name)
    {
        return MY_DOMEN . '/mvc/' . my_pass_through(@self::$config['paths'][$name]['controller']) . "/" . my_pass_through(@self::$config['paths'][$name]['action'] . '?' . self::get_query_string());
    }

    /*
     * Получаем данные ошибки по её коду
     *
     * @param integer $code - код ошибки
     *
     * @return string
     */
    public static function get_error($code)
    {

        return self::$errors[$code];
    }

    /*
     * Получаем все ошибки
     *
     * @return array
     */
    public static function get_errors()
    {

        return self::$errors;
    }

    /*
     * Отображаем css стили сервиса
     *
     * @param string $path - путь до css файла
     */
    public function trace_service_css($path)
    {
        echo("<style>\n");
        include($path . 'styles.css');
        echo("\n</style>");
    }

    /*
     * Отображаем js скрипт сервиса
     *
     * @param string $path - путь до js файла
     */
    public function trace_service_js($path)
    {
        echo("<script>\n");
        include($path . 'scripts.js');
        echo("\n</script>");
    }

    /*
     * Отображаем все css и js файлы сервиса
     * Нужны для локального переопределения/добавления стилей и js кода относящихся непосредственно к сервису
     */
    public function trace_service_frontend()
    {
        //уровни самый глубокий уровень может переопределить верхний
        $paths = array(
            '', //frontend
            self::get_device_dir() . MY_DS, //frontend_device
            self::get_device_dir() . MY_DS . get_controller_name() . MY_DS, //frontend_device_layout
            self::get_device_dir() . MY_DS . get_controller_name() . MY_DS . get_action_name() . MY_DS //frontend_device_layout_controller
        );
        $path_result = self::get_module(MY_MODULE_NAME_SERVICE)->get_frontend_path();
        echo("\n\n<!--Локальные переопределения-->");
        foreach ($paths as $key => $path) {
            echo("\n\n<!--уровень $key-->\n");
            $this->trace_service_css($path_result . $path);
            echo("\n");
            $this->trace_service_js($path_result . $path);
        }
    }

    /*
     * Отображаем phtml блок основного приложения
     *
     * @param string $path - путь до блока в папке blocks/
     * @param boolean $use_device_type - из общей папки брать или из папки устройства (mobile, desctop)
     * @param array $data - данные для блока
     */
    public function trace_block($path, $use_device_type = false, $data = null)
    {
        if (my_is_not_empty(@$path)) {
            $dir = $use_device_type ? self::get_device_dir() : MY_DIR_GENERIC_NAME;
            require(MY_VIEWS_DIR . $dir . MY_DS . 'blocks' . MY_DS . $path . '.php');
        } else {
            self::set_error(MY_ERROR_BLOCK_NOT_FOUND);
        }
    }

    /*
     * Отображаем phtml блок текущего сервиса
     *
     * @param string $path - путь до блока в папке blocks/
     * @param boolean $use_device_type - из общей папки брать или из папки устройства (mobile, desctop)
     * @param array $data - данные для блока
     */
    public function trace_service_block($path, $use_device_type = false, $data = null)
    {
        if (my_is_not_empty(@$path)) {
            $device_dir = $use_device_type ? self::get_device_dir() : MY_DIR_GENERIC_NAME;
            require(self::get_module(MY_MODULE_NAME_SERVICE)->get_blocks_path() . $device_dir . MY_DS . $path . '.php');
        } else {
            self::set_error(MY_ERROR_BLOCK_NOT_FOUND);
        }
    }


    /*
      public function trace_js($path, $use_device_type = false)
      {
      if (my_is_not_empty(@$path)) {

      $dir = $use_device_type ? self::get_device_dir() : MY_DIR_GENERIC_NAME;
      require(MY_JS_DIR . $dir . MY_DS . $path . '.php');

      } else {
      self::set_error(MY_ERROR_JS_NOT_FOUND);
      }
      }



      public function trace_js_model_object($model_type, $path = '', $use_device_type = false)
      {
      if (my_is_not_empty(@$model_type)) {
      $dir = $use_device_type ? self::get_device_dir() : MY_DIR_GENERIC_NAME;

      $path = $path ? \components\app\Map::get_name() . '_' . $path : \components\app\Map::get_name();
      require(MY_VIEWS_DIR . $dir . MY_DS . 'blocks' . MY_DS . '_models' . MY_DS . $model_type . MY_DS . $path . '.php');
      } else {
      self::set_error(MY_ERROR_JS_NOT_FOUND);
      }
      } */

    /*
     * Редирект по указанному url
     *
     * @param string $url - путь редиректа
     */
    public static function redirect($url)
    {
        $url = trim($url, '/');
        \header('Location: ' . MY_DOMEN . MY_DS . $url, true, 301);
        exit();
    }

    /*
     * Запись cookie
     *
     * @param string $name - имя cookie
     * @param string $value - значение cookie
     * @param integer $lifetime - время жизни cookie
     * @param string $path - область видимости cookie
     */
    public static function set_cookie($name, $value, $lifetime = null, $path='/')
    {

        if (is_null($lifetime)) {
            //Дефолтное значение
            $lifetime = self::$config['cookies']['lifetime'];
        }

        setcookie($name, $value, time() + $lifetime,$path);
        $_COOKIE[$name] = $value;
    }


    /*
     * Получить значение cookie
     *
     * @param string $name - имя cookie
     *
     * @return string/integer
     */
    public static function get_cookie($name)
    {
        if (my_is_empty(@$_COOKIE[$name])) {
            return null;
        }

        return $_COOKIE[$name];
    }

    /*
     * Получить экземпляр модуля
     *
     * @param string $name - имя модуля
     *
     * @return \vendor\Module
     */
    public static function get_module($name)
    {
        return \components\base\Modules::get($name);
    }

    /*
     * Получить экземпляр модели
     *
     * @param string $name - имя модели
     *
     * @return Model
     */
    public static function get_model($name)
    {
        $db_model_dir = '\\models\\dbase\\' . get_db_type() . '\\';
        $form_model_dir = '\\models\\forms\\';

        $model_path = '';
        if ($name === MY_MODEL_NAME_DB_USERS) {
            $model_path = $db_model_dir . 'users';
        } else if ($name === MY_MODEL_NAME_DB_MAP_DATA) {
            return components\Map::get_db_model('data');
        } else if ($name === MY_MODEL_NAME_DB_MAP_PHOTOS) {
            return components\Map::get_db_model('photos');
        } else if ($name === MY_MODEL_NAME_DB_EMAILS_SENT) {
            $model_path = $db_model_dir . 'emails_sends';
        } else if ($name === MY_MODEL_NAME_DB_GEOCODE_COLLECTION) {
            $model_path = $db_model_dir . 'geocode_collection';
        } else if ($name === MY_MODEL_NAME_DB_USERS_REGISTERED) {
            $model_path = $db_model_dir . 'users_registered';
        } else if ($name === MY_MODEL_NAME_FORM_ADD_NEW_POINT) {
            $model_path = $form_model_dir . 'add_new_point';
        } else if ($name === MY_MODEL_NAME_DB_SPAM) {
            $model_path = $db_model_dir . 'spam';
        }
        if ($model_path) {
            return $model_path::model();
        }

        self::concrete_error(array(MY_ERROR_UNDEFINED_MODEL_NAME, 'name:' . $name));
    }

    /*
     * Проверка значений по указанным правилам
     *
     * @param string $rule - правило
     * @param string/integer $value - значение
     * @param string $key - дополнение к правилу
     *
     * @return boolean - результат проверки
     */
    public function validate($rule = false, $value = false, $key = false)
    {

        if (!$rule) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'rule:' . $rule));
        }

        if ($key === 'max') {

            if (strlen($value) > $rule) {
                return false;
            }
        } else if ($key === 'min') {

            if ((!empty($value) && (strlen($value) < $rule))) {

                return false;
            }
        } else if ($key === 'pattern') {//соответствие регулярному выражению
            if (!preg_match($rule, $value)) {

                return false;
            }
        } else if (($rule === 'required') || ($rule === 'not_empty')) {//хоть что-нибудь
            // 'not_empty' - аналог required, но с условием, что параметр не обязательно должен передаваться - то есть: если найден, то не должен быть пустым
            // 'required' - для моделей, 'not_empty'- для всего остального
            // оба несут разный информационный смысл
            if (empty($value)) {

                return false;
            }
        } else if ($rule === 'none') {//должно быть пустое значение
            if (!empty($value)) {

                return false;
            }
        } else if ($rule === 'word') {

            if (!empty($value) && (preg_match('/[^\w]/u', $value))) {

                return false;
            }
        } else if ($rule === 'hash') {

            if (!empty($value) && (preg_match('/[^\w\d\$\/\.]/', $value))) {

                return false;
            }
        } else if ($rule === 'login') {

            if (!empty($value) && (preg_match('/[^a-z\d\.@_]/i', $value))) {

                return false;
            }
        } else if ($rule === 'name') {//применяется для имен
            if (!empty($value) && (preg_match("/[^a-zа-я]/iu", $value))) {

                return false;
            }
        } else if (($rule === 'phone')) {

            if (!empty($value) && (preg_match('/[^\d]/', $value))) {

                return false;
            }
        } else if ($rule === 'numeric') {

            if (!empty($value) && (!is_numeric($value))) {

                return false;
            }
        } else if ($rule === 'float') {

            if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_FLOAT))) {

                return false;
            }
        } else if ($rule === 'boolean') {

            if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_BOOLEAN))) {

                return false;
            }
        } else if ($rule === 'email') {

            if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_EMAIL))) {

                return false;
            }
        } else if ($rule === 'ip') {

            if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_IP))) {

                return false;
            }
        } else if ($rule === 'url') {//для нелокальных ссылок
            if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_URL))) {

                return false;
            }
        } else if ($rule === 'varname') {// названия переменных
            if (!empty($value) && (!preg_match('/^[a-zA-Z]+[\w]*/u', $value))) {

                return false;
            }
        } else if (($rule === 'get_query_string_var_value') || ('db_table_name')) {// значение GET переменной после '?' или имя таблицы БД
            if (!empty($value) && (preg_match('/[^_a-zA-Z0-9\.]+/', $value))) {

                return false;
            }
        } else {

            return MY_ERROR_UNKNOWN_VALIDATION_RULE;
        }

        return true;
    }
}
