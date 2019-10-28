<?php
/*
 * Class modules\base\archive\Archive
 *
 * Отвечает за архивацию файлов и каталогов
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\archive;

use modules\base\archive\classes;
use modules\base\archive\modules\zip as zip;

final class archive extends classes\archive
{

    use \vendor\traits\patterns\t_singleton;

}
