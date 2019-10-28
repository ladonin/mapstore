<?php
/*
 * Базовые функции
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */

if ($config['debug'] === 1) {

    $autoload_history = "<b>Автозагрузка классов:</b> <br>";
}

/*
 * Автозагрузка файлов вызываемых классов
 *
 * @param string $class - имя класса
 */
function __autoload($class)
{
    global $config;

    $filename = '';

    $class = strtolower($class);

    if (preg_match('/^' . MY_SERVICES_NAME . '/', $class)) {
        $class = preg_replace('/^' . MY_SERVICES_NAME . '/', MY_SERVICES_NAME . '\\' . get_service_name(), $class);
    }

    // класс с path, указанным в namespace
    $filename = str_replace('\\', '/', MY_APPLICATION_DIR . $class . '.php');

    if (file_exists($filename)) {

        if ($config['debug'] === 1) {

            global $autoload_history;

            $autoload_history.="[" . round((microtime(1) - MY_START_TIME), 5) . " сек] " . $filename . "<br>";
        }

        require_once($filename);

    } else if ($config['debug'] === 1) {

        var_dump(debug_backtrace());
        exit();
    }
}

/*
 * Проверка - пустая переменная или нет (инверсия)
 *
 * @param string $var - передаваемое значение
 *
 * @return boolean
 */
function my_is_not_empty($var)
{
    if (isset($var) && $var) {
        return true;
    }
    return false;
}

/*
 * Проверка - пустая переменная (массив) или нет (инверсия)
 *
 * @param array $var - передаваемый массив
 *
 * @return boolean
 */
function my_array_is_not_empty($var)
{
    if ((!is_array($var)) && (!is_null($var))){
        self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, '$var type:' . gettype($var)));
    }
    if (isset($var) && $var) {
        return true;
    }
    return false;
}

/*
 * Проверка - пустая переменная (массив) или нет
 *
 * @param array $var - передаваемый массив
 *
 * @return boolean
 */
function my_array_is_empty($var)
{
    if ((!is_array($var)) && (!is_null($var))){
        self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, '$var type:' . gettype($var)));
    }

    if (!isset($var) || !$var) {
        return true;
    }
    return false;
}

/*
 * Проверка - пустая переменная или нет
 *
 * @param string $var - передаваемое значение
 *
 * @return boolean
 */
function my_is_empty($var)
{
    if (!isset($var) || !$var) {
        return true;
    }
    return false;
}

/*
 * Проверка - существует ли метод у класса
 *
 * @param string $class_path - путь до класса
 * @param string $method_name - имя метода
 *
 * @return boolean
 */
function my_is_method_and_class_enable($class_path, $method_name)
{

    $security = \modules\base\Security\Security::get_instance();

    if (!\class_exists($class_path)) {
        $security::concrete_error(array(MY_ERROR_CLASS_NOT_FOUNT, 'class_path -> ' . $class_path));
    } else if (!\method_exists($class_path, $method_name)) {
        $security::concrete_error(array(MY_ERROR_METHOD_NOT_FOUNT, $class_path . ' -> ' . $method_name));
    }
    return true;
}

/*
 * Выводит значение переменной с гарантией, что оно не пустое
 *
 * @param string $var - выводимое значение
 *
 * @return string - выводимое значение
 */
function my_pass_through($var)
{
    if (!isset($var)) {
        $security = \modules\base\Security\Security::get_instance();
        $security::concrete_error(array(MY_ERROR_VALUE_NOT_PASSED_THROUGH));
    }
    return ($var);
}

/*
 * Возвращает mvc url текущей страницы
 *
 * @return string - mvc url текущей страницы
 */
function my_get_self_mvc_url()
{
    return MY_DOMEN . MY_DS . 'mvc' . MY_DS . \modules\base\Security\Security::get_instance()->get_self_url();
}

/*
 * Редиректит на mvc url текущей страницы
 */
function my_redirect_to_self_mvc_url()
{
    header('Location: ' . my_get_self_mvc_url(), true, 301);
    exit();
}


/*
 * Запись лога в файл
 *
 * @param string $file - имя файла, куда пишем
 * @param string $message - сообщение
 * @param boolean $generic_error - пишем в общий лог или лог сервиса
 */
function my_write_to_log($file, $message, $generic_error = false)
{

    if ($generic_error) {
        $file = MY_DOCROOT . $file;
    } else {
        $file = MY_SERVICES_DIR . get_service_name() . MY_DS . $file;
    }
    $config = \vendor\Component::get_config();

    if ($config['log']['on'] == 1) {
        $message = stripcslashes(stripcslashes(iconv('cp1251', 'utf-8', $message)));
        file_put_contents($file, date(DATE_RFC2822) . ': ' . $message . "\n\r", FILE_APPEND);
    }
}

/*
 * Проверка правильности введенной даты (есть такая дата в календаре или нет)
 *
 * @param string $day - день
 * @param string $month - месяц
 * @param string $year - год
 *
 * @return boolean
 */
function my_validate_date($day, $month, $year)
{
    $month = (int) $month;
    $day = (int) $day;
    $year = (int) $year;

    if ((!checkdate($month, $day, $year)) && ($month) && ($day) && ($year)) {

        return false;
    }
    return true;
}


/*
 * Функция для "дебажирования" переменной
 *
 * @param $data - переменная
 * @param boolean $return - формат вывода - echo или return
 *
 * @return string - данные пременной
 */
function my_pre($data = null, $return = false)
{
    if (is_string($data) && strlen($data) > 0)
        $data = 'string(' . strlen($data) . ') "' . $data . '"';

    if (is_bool($data)) {
        if ($data === true)
            $data = 'boolean (true)';
        else
            $data = 'boolean (false)';
    }

    if (is_null($data))
        $data = 'null';

    if (is_string($data) && strlen($data) === 0)
        $data = 'string(o) ""';

    if (PHP_SAPI === 'cli') {
        if ($return)
            return print_r($data, true);
        else
            return print_r($data) . PHP_EOL;
    }

    if ($return == true)
        return print_r($data, true);
    else
        echo '<pre style="white-space: pre-wrap; border: 1px solid #c1c1c1; border-radius: 10px; margin: 10px; padding: 10px; background-color: #fff; font-size: 11px; font-family: Tahoma; line-height: 15px;">' . htmlspecialchars(print_r($data, true)) . '</pre>';
    exit();
}

/*
 * Проверка запроса - ajax или нет
 *
 * @return boolean
 */
function is_ajax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    }
    return false;
}

/*
 * Возврат типа девайса - мобильный или десктопный
 *
 * @return string - тип девайса
 */
function get_device()
{
    return \vendor\Component::get_device_type();
}

/*
 * Возврат имени текущего контроллера
 *
 * @return string - имя текущего контроллера
 */
function get_controller_name()
{
    $security = \modules\base\Security\Security::get_instance();
    $controller = $security->get_controller();
    return $controller;
}

/*
 * Возврат имени action текущего контроллера
 *
 * @return string - имя action текущего контроллера
 */
function get_action_name()
{
    $security = \modules\base\Security\Security::get_instance();
    $action = $security->get_action();
    return $action;
}

/*
 * Проверка - мы на странице карты или нет
 *
 * @return boolean
 */
function is_map_page()
{
    return (get_controller_name() === MY_MODULE_NAME_MAP) ? true : false;
}

/*
 * Проверка - устройство мобильное или нет
 *
 * @return boolean
 */
function is_mobile()
{
    return \vendor\Component::get_device_type() === MY_DEVICE_MOBILE_TYPE_CODE ? true : false;
}

/*
 * Поучение типа расширения картинки
 *
 * @param string $path - путь до картинки
 * @param boolean $full - вернуть полный mime тип или просто расширение
 *
 * @return string - тип расширения картинки
 */
function my_get_image_type($path, $full = false)
{

    $sizes = getimagesize($path);
    if (my_array_is_empty(@$sizes)) {
        self::concrete_error(array(MY_ERROR_IMAGE_GET_TYPE, 'path:' . $path));
    }
    if ($full) {
        return $sizes['mime'];
    }
    $type_format_array = explode("/", $sizes['mime']);
    $format = $type_format_array[1];
    return $format;
}

/*
 * Возвращает сгенерированный случайный удобочитаемый пароль
 *
 * @return string
 */
function my_create_password()
{
    $gl = array(
        'a',
        'e',
        'i',
        'o',
        'u',
    );

    $so = array(
        'b',
        //'c',
        'd',
        'f',
        'g',
        'h',
        //'j',
        'k',
        'l',
        'm',
        'n',
        'p',
        //'q',
        'r',
        's',
        't',
        'v',
        //'w',
        'x',
        //'y',
        'z',
    );

    $result = '';
    for ($i = 0; $i < 8; $i++) {
        if ($i % 2 == 0) {
            $result .= $so[rand(0, 15)];
        } else {
            $result .= $gl[rand(0, 4)];
        }
    }

    return $result;
}

/*
 * Возвращает уникальное слово
 *
 * @return string
 */
function my_get_unique()
{
    return uniqid() . rand(1, 999);
}
