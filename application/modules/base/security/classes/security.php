<?php
/*
 * Class modules\base\Security\classes\Security
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\Security\classes;

abstract class Security extends \vendor\Module
{
    /*
     * GET переменные
     *
     * @var array
     */
    protected $get_vars = array();

    /*
     * Название контроллера
     *
     * @var string
     */
    protected $controller;

    /*
     * Название action контроллера
     *
     * @var string
     */
    protected $action;

    /*
     * Модель подключения к БД
     *
     * @var resource
     */
    protected $db_model;

    /*
     * Путь к layout файлу
     *
     * @var string
     */
    protected $layout_file = null;

    /*
     * Путь к view файлу
     *
     * @var string
     */
    protected $view_file = null;

    /*
     * Название layout файла
     *
     * @var string
     */
    protected $layout_name = null;

    /*
     * Название view файла
     *
     * @var string
     */
    protected $view_name = null;

    /*
     * Изменяем view файл
     *
     * @param string $controller - название контроллера
     * @param string $action - название action
     */
    public function change_view_file($controller, $action)
    {
        $this->view_file = $controller . MY_DS . $action . '.php';
        $this->view_name = $action;
    }

    /*
     * Изменяем layout файл
     *
     * @param string $controller - название контроллера
     * @param string $action - название action
     */
    public function change_layout_file($controller, $action)
    {
        $config = self::get_config();
        $this->layout_file = my_pass_through(@$config['layouts'][$controller][$action]) . '.php';
        $this->layout_name = my_pass_through(@$config['layouts'][$controller][$action]);
    }

    /*
     * Получить название контроллера
     *
     * @return string
     */
    public function get_controller()
    {
        return $this->controller;
    }

    /*
     * Получить все GET переменные
     *
     * @return array
     */
    public function get_get_vars()
    {
        return $this->get_vars;
    }

    /*
     * Получить значение GET переменной
     *
     * @param string $var_name - имя GET переменной, значение которой хотим получить
     *
     * @return string
     */
    public function get_get_var($var_name)
    {
        if (isset($this->get_vars[$var_name])) {
            return $this->get_vars[$var_name];
        }
        return null;
    }

    /*
     * Получить название action
     *
     * @return string
     */
    public function get_action()
    {

        return \strtolower($this->action);
    }

    /*
     * Получить путь до view файла
     *
     * @return string
     */
    protected function get_view_file()
    {

        return $this->view_file ? $this->view_file : \strtolower($this->get_controller()) . MY_DS . $this->get_view_name() . '.php';
    }

    /*
     * Получить название view файла, без расширения и пути
     *
     * @return string
     */
    protected function get_view_name()
    {
        if (is_null($this->view_name)) {
            $this->view_name = $this->get_action();
        }

        return $this->view_name;
    }

    /*
     * Получить путь до layout файла
     *
     * @return string
     */
    protected function get_layout_file()
    {

        return $this->get_layout_name() . '.php';
    }

    /*
     * Получить название layout файла, без расширения и пути
     *
     * @return string
     */
    protected function get_layout_name()
    {
        $config = self::get_config();
        if (is_null($this->layout_name)) {
            $this->layout_name = my_pass_through(@$config['layouts'][strtolower($this->get_controller())][$this->get_action()]);
        }

        return $this->layout_name;
    }

    /*
     * Запуск контроллера
     *
     * @return array - полученные данные для view - результат выполнения контроллера
     */
    protected function run_controller()
    {
        if (my_is_not_empty(@self::$errors[MY_ERROR_WRONG_ADRESS])) {
            return false;
        }

        $controller_name = '\\controllers\\' . $this->get_controller();
        $action_name = 'action_' . $this->get_action();
        $controller_object = $controller_name::get_instance();

        $result = $controller_object->$action_name();

        return $result;
    }

    /*
     * Запуск операций, которые прописаны в конфигурации (запускаются перед или после выполнения контроллера)
     *
     * @param string $var - какие запускаем операции - перед или после выполнения контроллера
     */
    protected function app_operations($var)
    {
        $config = self::get_config();
        if (my_is_not_empty(@$config['operations'][$var])) {
            foreach ($config['operations'][$var] as $operation) {
                $class_name = $operation['class'];
                $method_name = $operation['method'];
                $class_name::$method_name();
            }
        }
    }

    /*
     * Запуск view сайта
     *
     * @param array $data - данные выполнения контроллера - учавствуют в построении view сайта
     */
    protected function run_view($data)
    {
        $config = self::get_config();

        $device_dir = (self::get_device_type() === MY_DEVICE_MOBILE_TYPE_CODE) ? MY_DIR_MOBILE_NAME : MY_DIR_DESCTOP_NAME;

        if (($data === false) && ($config['debug'] !== 1)) {
            self::redirect('error_works.php');
        } else if (($data === false) && ($config['debug'] === 1)) {

        } else if (my_array_is_not_empty(@$data)) {

            $layout_file = $this->get_layout_file();
            $view_file = $this->get_view_file();

            ob_start();
            require_once(\MY_APPLICATION_DIR . 'views' . \MY_DS . $device_dir . \MY_DS . 'controllers' . \MY_DS . $view_file);
            $content = ob_get_clean();
            require_once(\MY_APPLICATION_DIR . 'views' . \MY_DS . $device_dir . \MY_DS . 'layouts' . \MY_DS . $layout_file);
        }
    }

    /*
     * Получение и обработка всех GET переменных
     */
    protected function detect_get_vars()
    {
        $config = self::get_config();
        // Validate get vars
        $var_category = MY_VAR_CATEGORY_SYSTEM;
        foreach ($_GET as $get_name => $get_value) {
            if ($get_name === MY_GET_VARS_QUERY_STRING_NAME) {
                $var_category = MY_VAR_CATEGORY_USER;
                continue;
            }
            $var_category_array = my_pass_through(@$config['get_vars'][$var_category]);
            if (array_key_exists($get_name, $var_category_array)) {
                foreach ($var_category_array[$get_name]['rules'] as $key => $rule) {
                    if (!$this->validate($rule, $get_value, $key)) {
                        self::concrete_error(array(
                            MY_ERROR_WRONG_ADRESS,
                            'key="' . $key . '", rule="' . $rule . '", name="' . $get_name . '", value="' . $get_value . '"'));
                    }
                }
            } else {
                self::concrete_error(array(
                    MY_ERROR_WRONG_ADRESS,
                    $get_name . ':unknown GET variable'));
            }
        }

        // Дефолт
        if (my_is_not_empty(@$_GET['var1'])) {
            $_GET['var1'] = @$_GET['var1'];
        } else {
            $_GET['var1'] = my_pass_through(@$config['controllers']['default']);
        }

        if (my_is_not_empty(@$_GET['var2'])) {
            $_GET['var2'] = @$_GET['var2'];
        } else {
            $_GET['var2'] = 'index';
        }

        // после проверки извлекаем get-переменные
        $self_url = '';
        $get_vars_config = my_pass_through(@$config['get_vars']);
        // 1) System vars
        foreach (my_pass_through(@$get_vars_config[MY_VAR_CATEGORY_SYSTEM]) as $var_name => $arary) {
            if (array_key_exists($var_name, $_GET)) {
                $this->get_vars[$var_name] = strtolower($_GET[$var_name]);
                $self_url .= $this->get_vars[$var_name] !== 'index' ? $this->get_vars[$var_name] . '/' : '';
            }
        }
        $self_url = trim($self_url, '/');

        $self_url_without_query_string = $self_url;

        // 2) Query vars
        $self_url .= '?';
        $query_string = '';
        foreach (my_pass_through(@$get_vars_config[MY_VAR_CATEGORY_USER]) as $var_name => $array) {
            if (array_key_exists($var_name, $_GET)) {
                $this->get_vars[$var_name] = strtolower($_GET[$var_name]);
                $self_url .= $var_name . '=' . $this->get_vars[$var_name] . '&';
                $query_string .= $var_name . '=' . $this->get_vars[$var_name] . '&';
            }
        }

        $self_url = trim($self_url, '&');
        $query_string = trim($query_string, '&');
        $this->set_self_url($self_url);
        $this->set_query_string($query_string);
        $this->set_self_url_without_query_string($self_url_without_query_string);
    }

    /*
     * Установка контроллера и его action
     */
    protected function set_controller_and_action()
    {
        $config = self::get_config();
        $controller = '\\controllers\\' . $this->get_vars['var1'];
        $controller_name = $this->get_vars['var1'];

        if (\class_exists($controller)) {

            $method_name = $this->get_vars['var2'];

            if (\method_exists($controller, 'action_' . $method_name)) {

                if ($controller::get_instance() instanceof \vendor\Controller) {

                    $this->controller = $controller_name;
                    $this->action = $method_name;
                    return true;
                }
            }
        }
        self::concrete_error(array(
            MY_ERROR_WRONG_ADRESS,
            'url:' . json_encode($this->get_vars)));

        return false;
    }

    /*
     * Проверка ячейки paths в общем конфигурационном файле, что они ведут на существующие классы и методы
     */
    public function check_config_paths()
    {
        $config = self::get_config();
        foreach ($config['paths'] as $path) {
            if (\method_exists('\\controllers\\' . my_pass_through(@$path['controller']), 'action_' . my_pass_through(@$path['action']))) {
                return true;
            } else {
                self::concrete_error(array(MY_ERROR_CONFIG_PATH_NOT_FOUND, 'controller:' . $path['controller'] . '-> action:' . $path['action']));
            }
        }
    }

    /*
     * Старт приложения
     *
     * @param array $conf - общая конфигурация приложения
     */
    public function run(array $conf)
    {

        if (my_array_is_not_empty(@$conf)) {
            $this->set_config($conf);
        } else {
            exit('Config not found.');
        }
        $config = self::get_config();
        $this->db_model = $this->get_db();
        $db_connect = $this->db_model->get_connect();
        try {

            $db_connect->beginTransaction();

            $this->execute();

            $db_connect->commit();

            // Если задали редирект
            if (!is_null(self::get_redirect_url())) {
                self::redirect(self::get_redirect_url());
            }
        } catch (\Exception $e) {
            $db_connect->rollBack();

            if (is_ajax() === true) {
                http_response_code(501);
            } else {

                if ((my_is_not_empty(@self::$errors[MY_ERROR_WRONG_ADRESS]) ||
                        (my_is_not_empty(@self::$errors[MY_ERROR_MAP_WRONG_GET_VALUE]))
                        ) && ($config['debug'] !== 1)) {

                    self::redirect('error_404.php');
                } else if ((my_is_not_empty(@self::$errors[MY_ERROR_USER_NOT_VERIFICATED]))
                        && ($this->get_vars['var1'] != $config['controllers']['errors'][MY_ERROR_USER_NOT_VERIFICATED])) {

                    self::redirect($config['controllers']['errors'][MY_ERROR_USER_NOT_VERIFICATED]);
                } else {

                    if (!isset(self::$errors)) {
                        \my_write_to_log(MY_LOG_APPLICATION_PATH, '[UNDEFINED ERROR]:' . json_encode($e->getTrace()));
                    }

                    if ($config['debug'] !== 1) {

                        self::redirect('error_works.php');
                    }
                }
            }

            if ($config['debug'] === 1) {
                echo ("Ошибки:");
                my_pre(self::$errors);
                echo ("<br>");
                echo ("<b>debug_backtrace:</b>");
                echo ("<br>");
                var_dump(debug_backtrace());
            }
        }
    }

    /*
     * Запуск основных команд для построения страницы
     */
    private function execute()
    {

        $this->check_config_paths();

        $this->detect_get_vars();

        $this->app_operations('before');

        $this->set_controller_and_action();

        $this->run_view($this->run_controller());

        $this->app_operations('after');
    }

    /*
     * Возвращаем тип БД - mysql, redis и т.д.
     *
     * @return string
     */
    private function get_db()
    {
        $config = self::get_config();
        if (my_array_is_not_empty(@$config['db'])) {
            if (key($config['db']) === 'mysql') {
                return \vendor\DBase_Mysql::model();
            }
        }
        exit('Database settings not found.');
    }
}
