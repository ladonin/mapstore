<?php

/*
 * Копирует директорию
 *
 * @param string $srcdir - копируемый путь папки
 * @param string $dstdir - путь куда копируем
 * @param boolean $verbose - отображение хода событий
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
function dircopy($srcdir, $dstdir, $verbose = false)
{
    $num = 0;
    if (!is_dir($dstdir))
        mkdir($dstdir);
    if ($curdir = opendir($srcdir)) {
        while ($file = readdir($curdir)) {
            if ($file != '.' && $file != '..') {
                $srcfile = $srcdir . '/' . $file;
                $dstfile = $dstdir . '/' . $file;
                if (is_file($srcfile)) {
                    if (is_file($dstfile))
                        $ow = filemtime($srcfile) - filemtime($dstfile);
                    else
                        $ow = 1;
                    if ($ow > 0) {
                        if ($verbose)
                            echo "Copying $srcfile to $dstfile...";
                        if (copy($srcfile, $dstfile)) {
                            touch($dstfile, filemtime($srcfile));
                            $num++;
                            if ($verbose)
                                echo "OKn";
                        } else
                            echo "Error: File $srcfile could not be copied!n";
                    }
                }
                else if (is_dir($srcfile)) {
                    $num += dircopy($srcfile, $dstfile, $verbose);
                }
            }
        }
        closedir($curdir);
    }
    return $num;
}


/*
 * Преобразование нелатинских символов в латинские
 *
 * @param string $var - преобразуемое слово
 *
 * @return string - преобразованное слово
 */
function my_prepare_strange_words($var)
{
    $words_from = array('ö', 'ü', 'ß', 'ć', 'ț', 'ș', 'í', 'ó', 'á', 'ñ', 'ô', 'Î', 'Ō');
    $words_to = array('o', 'u', 'ss', 't', 't', 's', 'i', 'o', 'a', 'n', 'o', 'i', 'o');
    return str_replace($words_from, $words_to, $var);
}

/*
 * Убираем из текста специальные символы
 *
 * @param string $var - преобразуемый текст
 *
 * @return string - подготовленный текст
 */
function clear_special_symbols($text)
{
    return preg_replace('/[ \,\|«»]\'\"\`\!/', ' ', $text);
}

/*
 * Удаляем директорию вместе с содержимым
 *
 * @param string $delfile - путь до директории
 */
function removeDir($delfile)
{
    if (file_exists($delfile)) {
        chmod($delfile, 0777);
        if (is_dir($delfile)) {
            $handle = opendir($delfile);
            while ($filename = readdir($handle))
                if ($filename != "." && $filename != "..") {
                    removeDir($delfile . "/" . $filename);
                }

            closedir($handle);
            @rmdir($delfile);
        } else {
            @unlink($delfile);
        }
    }
}

/*
 * Переводим буквы текста с русского на английский (латинский)
 *
 * @param string $var - подготавливаемый текст
 *
 * @return string - подготовленный текст
 */
function my_translater_ru_to_en($text)
{
    $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
    $lat = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');
    return str_replace($rus, $lat, $text);
}

/*
 * Подготавливаем путь до фотографии метки
 * Если фотография отсутствует на локальном сервере, то возвращается путь до удаленного хранилища, где она должна быть
 * Сделано, для экономии ресурсов локального сервера
 *
 * @param integer $id - id метки
 * @param string $name - название фотографии без префикса
 * @param string $prefix - префикс (обозначает размер фотографии)
 * @param boolean $only_dir - возвратить только путь до папки
 * @param boolean $is_url - путь - URL или внутреннний
 * @param string $service_name - название сервиса (имя сайта) - для многосервисных проектов
 *
 * @return string - подготовленный путь
 */
function prepare_photo_path($id, $name, $prefix, $only_dir = false, $is_url = false, $service_name = null)
{
    if (!$service_name) {
        $service_name = get_service_name();
    }

    $photo_path = MY_FILES_DIR . 'map/' . $service_name . '/' . $id . '/' . $prefix . $name;
    if (file_exists($photo_path)) {
        $photo_name = '';
        if (!$only_dir) {
            $photo_name = $prefix . $name;
        }
        if ($is_url) {
            $current_photo_path = MY_FILES_MAP_URL . $service_name . '/' . $id . '/' . $photo_name;
        } else {
            $current_photo_path = MY_FILES_DIR . 'map/' . $service_name . '/' . $id . '/' . $photo_name;
        }
    } else {
        $photo_name = '';
        if (!$only_dir) {
            $photo_name = $prefix . $name;
        }
        $current_photo_path = 'http://140706.selcdn.ru/mapstore/02_04_2016/' . $service_name . '/' . $id . '/' . $photo_name;
    }
    return $current_photo_path;
}

/*
 * Возвращает имя сайта, прописанного в конфигурации сервиса
 *
 * @param string $service_name - имя сервиса
 *
 * @return string - имя сайта
 */
function get_site_name($service_name)
{
    $config = require(MY_SERVICES_DIR . $service_name . MY_DS . 'config' . MY_DS . 'config.php');
    if (isset($config['generic']['site_name'])) {
        return $config['generic']['site_name'];
    }
    self::concrete_error(array(MY_ERROR_SERVICE_CONFIG_ABSENT, 'generic-site_name'));
}


/*
 * Проверяет - есть ли сервис с таким именем
 *
 * @param string $service_name - имя сервиса
 *
 * @return boolean
 */
function is_valid_service($service_name)
{
    if (is_dir(MY_SERVICES_DIR . $service_name)) {
        return true;
    }

    return false;
}

/*
 * Подготавливает имя страны:
 * Пример:
 *     Папский Престол (Государство-город Ватикан) (преобразует в) => Ватикан
 *
 * @param string $name - имя страны
 *
 * @return string - подготовленное имя страны
 */
function prepare_country_name($name)
{
    $countries_names_replaces = require(MY_APPLICATION_DIR . 'components' . MY_DS . 'app' . MY_DS . 'countries' . MY_DS . 'countries_names_replaces.php');
    foreach ($countries_names_replaces as $replaced_name => $replace) {
        if ($name === $replaced_name) {
            return $replace;
        }
    }
    return $name;
}

/*
 * Подготавливает имя региона:
 * Пример (если у пользователя русский язык):
 *     'Niederösterreich' (преобразует в)=> 'Нижняя Австрия'
 *
 * @param string $country_code - код страны
 * @param string $state_name - имя региона
 * @param string $state_code - код региона
 * @param string $language - язык
 *
 * @return string - подготовленное имя региона
 */
function translate_state_names($country_code, $state_name, $state_code, $language)
{
    $countries_data = require(MY_APPLICATION_DIR . 'components' . MY_DS . 'app' . MY_DS . 'countries' . MY_DS . 'countries_data.php');
    $result = @$countries_data[$country_code]['translates'][$language][$state_name];

// если результат не найден или является массивом, то попробуем второй вариант
    if (@is_array($result) || @!$result) {
        $result = @$countries_data[$country_code]['translates'][$language][$state_code][$state_name];
    }

    if (@!$result) {
        $result = $state_name;
    }
    return $result;
}

/*
 * Проверяет - имеет ли страна регионы
 *
 * @param string $country_code - код страны
 *
 * @return boolean
 */
function has_states($country_code)
{
    $countries_data = require(MY_APPLICATION_DIR . 'components' . MY_DS . 'app' . MY_DS . 'countries' . MY_DS . 'countries_data.php');
    if ($country_code === MY_UNDEFINED_VALUE) {
        // Например в случае, если гугл не знает местопложения и country_code от этого стал undefined
        return false;
    }

    return $countries_data[$country_code]['has_states'];
}

/*
 * Подготавливает из переменной массив,
 * если переменная не массив, то возвратится пустой массив
 *
 * @param mix $value - переменная
 *
 * @return array - подготовленный массив
 */
function prepare_to_array($value)
{
    return my_array_is_not_empty($value) ? $value : array();
}

/*
 * Возвращает тип базы данных (mysql, redis и т.д.)
 *
 * @return string - тип базы данных
 */
function get_db_type()
{
    static $config;
    if (!isset($config) || !$config) {
        $config = require(MY_APPLICATION_DIR . 'config' . MY_DS . 'config.php');
    }
    return key($config['db']);
}

/*
 * Возвращает имя сервиса
 *
 * @return string - имя сервиса
 */
function get_service_name()
{
    if (isset($_REQUEST[MY_SERVICE_VAR_NAME]) && ($_REQUEST[MY_SERVICE_VAR_NAME])) {
        return $_REQUEST[MY_SERVICE_VAR_NAME];
    }
    return \components\app\Map::get_name();
}