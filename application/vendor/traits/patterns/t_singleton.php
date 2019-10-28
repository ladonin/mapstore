<?php
/*
 * Trait vendor\traits\patterns\T_Singleton
 *
 * Добавляется к классу, когда необходимо реализовать паттерн Singleton
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor\traits\patterns;

trait T_Singleton {

    static private $instance;

    private function __construct($var) {

    }

    private function __clone() {

    }

    private function __wakeup() {

    }

    public static function get_instance(array $var = null) {

        $class_name = get_called_class();

        if (empty(self::$instance[$class_name])) {

            self::$instance[$class_name] = new $class_name($var);
        }
        return self::$instance[$class_name];
    }

    public static function model(array $var = null) {

        $class_name = get_called_class();

        if (empty(self::$instance[$class_name])) {

            self::$instance[$class_name] = new $class_name($var);
        }
        return self::$instance[$class_name];
    }

}
