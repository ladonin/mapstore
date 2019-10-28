<?php
/*
 * Класс Modules
 *
 * Отвечает за работу с имеющимися модулями в проекте
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */

namespace components\base;

use \vendor\component;

final class Modules extends Component
{

    /*
     * Возвратить экземпляр модуля по его имени
     *
     * @param string $name - имя модуля
     *
     * @return \vendor\Module - экземпляр модуля
     */
    public static function get($name)
    {
        if ($name === MY_MODULE_NAME_SECURITY) {
            return \modules\base\Security\Security::get_instance();
        } else if ($name === MY_MODULE_NAME_SERVICE) {
            return \modules\app\service\service::get_instance();
        } else {
            self::concrete_error(array(MY_ERROR_UNDEFINED_MODULE_NAME, 'name:' . $name));
        }
    }
}
