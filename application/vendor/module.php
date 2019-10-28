<?php
/*
 * Class vendor\Module
 *
 * Базовый класс модулей
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor;

abstract class Module extends Component {

    use \vendor\traits\patterns\t_singleton;

}
