<?php
/*
 * Class modules\app\service\classes\Service
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\app\service\classes;

abstract class Service extends \vendor\Module
{
    /*
     * Конфигурация сервиса
     *
     * @var array
     */
    protected $config = array();

    /*
     * Словарь сервиса
     *
     * @var array
     */
    protected $words = array();

    /*
     * Путь до сервиса
     *
     * @var string
     */
    protected $path = '';

    protected function __construct()
    {
        $this->path = MY_SERVICES_DIR . get_service_name() . MY_DS;
        $this->config = require_once($this->path . 'config' . MY_DS . 'config.php');
    }

    /*
     * Вернуть имя сайта
     *
     * @return string
     */
    public function get_site_name()
    {
        return '';
    }

    /*
     * Вернуть путь до блоков сервиса
     *
     * @return string
     */
    public function get_blocks_path()
    {
        return '';
    }
}

//Пример: self::get_module(MY_MODULE_NAME_SERVICE)->get_blocks_path();
