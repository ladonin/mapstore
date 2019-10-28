<?php
/*
 * Общий конфигурационный файл
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
return array(

    ########################
    #
    # Системные настройки
    #
    ########################

    //Режим отладки
    'debug' => 1,
    //Вкл/откл логирование ошибок
    'log' => array(
        'on' => 1
    ),
    //Контроллеры, которые вызываются в следующих ситуациях
    'controllers' => array(
        //Контроллер, на который происходит редирект в случае одной из следующих ошибок,
        //ошибки могут быть вызваны вручную из любого места
        //Пример:
        #    self::set_error(MY_ERROR_USER_NOT_VERIFICATED);
        'errors' => array(
            //MY_ERROR_USER_NOT_VERIFICATED: "пользователь не зарегистрирован"
            MY_ERROR_USER_NOT_VERIFICATED => MY_CONTROLLER_NAME_MAP,
        ),
        // Если контроллер не указан в URL
        'default' => MY_CONTROLLER_NAME_MAP,
    ),
    //Операции, которые вызываются до или после выполнения контроллера
    'operations' => array(
        // До
        'before' => array(
            array(
                'class' => 'components\app\user',
                'method' => 'authentication',
            ),
            array(
                'class' => 'components\app\language',
                'method' => 'set_language',
            ),
        ),
        // После
        'after' => array(
        /*
            array(
                'class'         =>  '',
                'method'        =>  '',
            )
        */
        ),
    ),

    //Подключение к БД, выбрано MySQL
    'db' => require(MY_APPLICATION_DIR . 'config' . MY_DS . 'ignore' . MY_DS . 'mysql.php'),

    //Настройка шаблонов страниц -  contoler/action => layout file
    'layouts' => array(
        //Контроллеры
        'main' => array(
            'index' => 'main',
        ),
    ),

    //Класс языка сайта
    'language' => array(
        'class' => '\components\app\language'
    ),

    //Настройки куков
    'cookies' => array(
        //Дефолтное время жизни
        'lifetime' => 3600 * 24 * 21,
    ),
    //Настройка GET переменных
    'get_vars' => array(
        // Примечание - правило 'not_empty' означает : если найден в GET запросе - то должен не быть пустым
        // Все переменные также имеют правило not required

        //Системные пепременные
        MY_VAR_CATEGORY_SYSTEM => array(
            'var1' => array(
                'rules' => array('varname', 'max' => 50, 'not_empty'),
            ),
            'var2' => array(
                'rules' => array('varname', 'max' => 50, 'not_empty'),
            ),
            'var3' => array(
                'rules' => array('varname', 'max' => 50, 'not_empty'),
            ),
            'var4' => array(
                'rules' => array('varname', 'max' => 50, 'not_empty'),
            ),
        ),
        //Пользовательские переменные
        MY_VAR_CATEGORY_USER => array(
            MY_SERVICE_VAR_NAME => array(
                'rules' => array('get_query_string_var_value', 'not_empty'),
            ),
        ),
    ),


    ########################
    #
    # Пользовательские настройки
    #
    ########################

    // Автоматическая генерация пути
    //Пример:
    #    self::get_path('ajax_set_site_language')
    'paths' => array(
        'set_site_language' => array(
            'controller' => 'map',
            'action' => 'ajax_set_site_language'
        ),
    ),
    //Вспомогательные переменные - можно использовать вместо констант
    'help_vars' => array(
    ),
);

