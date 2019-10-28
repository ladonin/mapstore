<?php
/*
 * Class components\app\Language
 *
 * Отвечает за язык отображаемого текста в приложении
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace components\app;

use \vendor\component;

final class Language extends Component
{

    use \vendor\traits\patterns\t_singleton;

    /*
     *  Язык по умолчанию
     */
    const DEFAULT_LANGUAGE = MY_LANGUAGE_EN;

    /*
     *  Язык приложения
     *
     * @var string
     */
    protected static $language;

    /*
     * Доступные в приложении языки
     *
     * @var array
     */
    protected static $available_languages = array(
        MY_LANGUAGE_RU,
        MY_LANGUAGE_EN
    );

    /*
     * Базовый словарь
     *
     * @var array
     */
    protected $base_words = array();

    /*
     * Словарь сервиса
     *
     * @var array
     */
    protected $service_words = array();

    protected function __construct()
    {
        $service_module = self::get_module(MY_MODULE_NAME_SERVICE);
        $this->service_words = $service_module->get_words($this->get_language());
        $this->base_words = require_once(MY_APPLICATION_DIR . 'config' . MY_DS . 'language' . MY_DS . $this->get_language() . '.php');
    }

    /*
     * Проверка - доступен ли такой язык
     *
     * @param string $language - проверяемый код языка
     *
     * @return boolean
     */
    public function is_available_language($language)
    {
        if (in_array($language, self::$available_languages)) {
            return true;
        }
        self::concrete_error(array(MY_ERROR_LANGUAGE_CODE_NOT_FOUND, 'language="' . $language . '"'));
    }

    /*
     * Получить текст из массива по его идентификатору (адресу)
     *
     * @param string $adress - адрес текста в массиве
     * @param array $vars - дополнительные переменные, которыми может быть заменена часть возвращаемого текста
     *
     * @return string - найденный подготовленный текст
     */
    public function get_text($adress, $vars = null)
    {
        if (isset($this->base_words[$adress])) {

            $text = $this->base_words[$adress];

            if (is_array($vars)) {
                foreach ($vars as $key => $value) {
                    $text = str_replace('%' . $key . '%', $value, $text);
                }
            }
            return $text;
        } else if (isset($this->service_words[$adress])) {
            $text = $this->service_words[$adress];
            if (is_array($vars)) {
                foreach ($vars as $key => $value) {
                    $text = str_replace('%' . $key . '%', $value, $text);
                }
            }
            return $text;
        } else {
            self::concrete_error(array(MY_ERROR_LANGUAGE_WORD_NOT_FOUND, 'language="' . $this->get_language() . '", word="' . $adress . '"'));
        }
    }

    /*
     * Записать язык в сессию
     *
     * @param string $language - код языка
     */
    public function set_language_in_session($language)
    {
        if (in_array($language, self::$available_languages)) {
            $_SESSION['site']['language'] = $language;
        } else {
            $_SESSION['site']['language'] = self::DEFAULT_LANGUAGE;
        }
    }

    /*
     * Установить язык приложения
     */
    public static function set_language()
    {
        if (my_is_not_empty(@$_SESSION['site']['language'])) {
            if (in_array($_SESSION['site']['language'], self::$available_languages)) {
                $language = $_SESSION['site']['language'];
            } else {
                $language = self::DEFAULT_LANGUAGE;
            }
            unset($_SESSION['site']['language']);
            //обновляем куки
            self::set_cookie(MY_COOKIE_NAME_SITE_LANGUAGE, $language, MY_COOKIE_MAX_LIFETIME_VALUE);
        }

        $cookie_language = self::get_cookie(MY_COOKIE_NAME_SITE_LANGUAGE);
        //если куки нет или её значение неопознанно
        if (is_null($cookie_language) || (!in_array($cookie_language, self::$available_languages))) {
            //если первый раз зашли и сессия с куками пустые, то определяем язык из данных клинета
            $cookie_language = self::get_client_language();
            self::set_cookie(MY_COOKIE_NAME_SITE_LANGUAGE, $cookie_language, MY_COOKIE_MAX_LIFETIME_VALUE);
        }

        self::$language = $cookie_language;
    }

    /*
     * Получить язык приложения
     *
     * @return string
     */
    public function get_language()
    {
        return self::$language;
    }

    /*
     * Орпеделить язык клиента
     *
     * @return string - код языка клиента
     */
    public static function get_client_language()
    {

        $languages_data=self::get_module(MY_MODULE_NAME_SERVICE)->get_languages();
        $client_language=isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])?$_SERVER["HTTP_ACCEPT_LANGUAGE"]:'';

        foreach ($languages_data as $language) {

            if(preg_match('/^'.$language['code'].'\-/',$client_language)){
                return $language['code'];
            }
        }

        return self::DEFAULT_LANGUAGE;
    }
}
