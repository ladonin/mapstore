<?php
/*
 * Class modules\base\archive\modules\zip\Zip
 *
 * Отвечает за zip-архивацию
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\archive\modules\zip;

use modules\base\archive\modules\zip\classes;

final class Zip extends classes\zip {
        use \vendor\traits\patterns\t_singleton;
}