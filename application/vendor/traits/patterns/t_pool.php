<?php
/*
 * Trait vendor\traits\patterns\T_Pool
 *
 * Добавляется к классу, когда необходимо реализовать паттерн Pool Objects
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor\traits\patterns;

trait T_Pool {

    protected $objects = array();

    final public function get_object($num = 0) {

        return self::$objects[(int) $num];
    }

    final public function get_all_objects() {

        return self::$objects;
    }

    final public function set_object($num = null, IPool $object) {

        if (is_null($num)) {
            self::$objects[] = $object;
        } else {
            self::$objects[(int) $num] = $object;
        }
    }

}
