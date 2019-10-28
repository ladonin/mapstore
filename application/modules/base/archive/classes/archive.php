<?php
/*
 * Class modules\base\archive\classes\Archive
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\archive\classes;

use \modules\base\archive\modules\zip as zip;

class Archive extends \vendor\Component
{

    /*
     * Архивация файла/папки
     *
     * @param string $source - что архивируем
     * @param string $destination - куда архивируем
     * @param string $format - формат архивации
     *
     * @return boolean - результат архивации
     */
    public function archive($source, $destination, $format = 'zip')
    {
        if ($format === 'zip') {
            $zip = \zip\zip::get_instance();
            return $zip->archive($source, $destination);
        }
    }
}
