<?php
/*
 * Class modules\base\mailer\classes\Mailer
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\mailer\classes;
use \components\app as components;

abstract class Mailer extends \vendor\Module
{
    /*
     * Email info отправителя
     *
     * @var string
     */
    protected $info_email;

    /*
     * Имя info отправителя
     *
     * @var string
     */
    protected $info_name;

    protected function __construct(){
        $this->info_email=self::get_module(MY_MODULE_NAME_SERVICE)->get_email_from(1);
        $this->info_name=self::get_module(MY_MODULE_NAME_SERVICE)->get_email_name(1);
    }
}