<?php
/*
 * Class modules\app\seo\classes\Seo
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\app\seo\classes;

use \components\app as components;

abstract class Seo extends \vendor\Module
{

    /*
     * Вернуть title страницы
     *
     * $param string $action - action контроллера
     * $param array $params - переменные для постороения title
     *
     * @return string
     */
    public function get_title($action, $params = null)
    {
        return '';
    }

    /*
     * Вернуть keywords страницы
     *
     * $param string $action - action контроллера
     *
     * @return string
     */
    public function get_keywords($action)
    {
        return '';
    }

    /*
     * Вернуть description страницы
     *
     * $param string $action - action контроллера
     * $param array $params - переменные для постороения description
     *
     * @return string
     */
    public function get_description($action, $params = null)
    {
        return '';
    }
}
