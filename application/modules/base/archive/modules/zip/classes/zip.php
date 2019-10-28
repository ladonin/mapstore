<?php
/*
 * Class modules\base\archive\modules\zip\classes\Zip
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\archive\modules\zip\classes;

// Used also outer of framework, so we removed 'abstract' termin
class Zip extends \ZipArchive
{

    /*
     * Zip архивация файла/папки
     *
     * @param string $source - что архивируем
     * @param string $destination - куда архивируем
     *
     * @return boolean - результат архивации
     */
    function archive($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);
                $file = str_replace('\\', '/', $file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        return $zip->close();
    }
}
