<?php
/*
 * Class components\app\User
 *
 * Отвечает за работу с пользователем
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace components\app;

use \vendor\Component;

final class User extends Component
{

    /*
     * Идентификация пользователя
     */
    public static function authentication()
    {
        self::get_module(MY_MODULE_NAME_ACCOUNT)->authentication();
    }

}