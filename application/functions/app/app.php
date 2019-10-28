<?php
/*
 * Пользовательские функции
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */

/*
 * Обрезание текста с сохраненим целостности слов
 *
 * @param string $text - обрезаемый текст
 * @param integer $length - длина обрезки
 *
 * @return string - обрезанный текст
 */
function get_cutted_text($text, $length)
{
    if (!$text) {
        return '';
    }

    $text = naking_text($text);

    //Первым делом, уберём все html элементы:
    $text = strip_tags($text);


    $str_length = mb_strlen($text, 'UTF-8');
    //может и не надо обрезать
    if ($str_length < $length) {
        return $text;
    }

    //Теперь обрежем его на определённое количество символов:
    $text = mb_substr($text, 0, $length, 'UTF-8');
    //Затем убедимся, что текст не заканчивается восклицательным знаком, запятой, точкой или тире:
    $text = rtrim($text, "!,.-");

    //Напоследок находим последний пробел, устраняем его и ставим троеточие:
    $text = preg_replace('#(.*?)(?: [^ ]*)$#', '$1', $text);

    return $text . ' ...';
}