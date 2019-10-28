<?php
/*
 * Системные и пользовательские константы
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */

/*
 *  Системные
 */
define('MY_ERROR_WRONG_ADRESS', 1);
define('MY_ERROR_DB_NO_CONNECT', 2);
define('MY_ERROR_USER_NOT_VERIFICATED', 3);
define('MY_ERROR_MODEL_FILTER', 4);
define('MY_ERROR_REG_LOGIN_ITERATION', 5);
define('MY_ERROR_FORM_NOT_PASSED', 6);
define('MY_ERROR_BLOCK_NOT_FOUND', 7);
define('MY_ERROR_CONFIG_PATH_NOT_FOUND', 8);
define('MY_ERROR_DB_UNDEFINED_FIELD', 9);
define('MY_ERROR_JS_NOT_FOUND', 10);
define('MY_ERROR_IMAGE_NOT_PASSED_TO_OBJECT', 11);
define('MY_ERROR_WRONG_MODULE_NAME', 12);
define('MY_ERROR_LOADING_FILE', 13);
define('MY_ERROR_LOADING_IMAGE_WRONG_TYPE', 14);
define('MY_ERROR_FUNCTION_ARGUMENTS', 15);
define('MY_ERROR_UNKNOWN_VALIDATION_RULE', 16);
define('MY_ERROR_MYSQL', 17);
define('MY_ERROR_CLASS_NOT_FOUNT', 18);
define('MY_ERROR_METHOD_NOT_FOUNT', 19);
define('MY_ERROR_MAP_WRONG_GET_VALUE', 20);
define('MY_ERROR_VALUE_NOT_PASSED_THROUGH', 21);
define('MY_ERROR_SERVICE_CONFIG_ABSENT', 22);
define('MY_ERROR_LANGUAGE_WORD_NOT_FOUND', 23);
define('MY_ERROR_LANGUAGE_MODEL_NOT_FOUND', 24);
define('MY_ERROR_FORM_WRONG_DATA', 25);
define('MY_ERROR_COOKIE_NAME_UNDEFINED', 26);
define('MY_ERROR_FORM_UPDATE_POINT_A_LOT_OF_PHOTOS', 30);
define('MY_ERROR_WRONG_DB_WHERE_CONDITION', 31);
define('MY_ERROR_UNDEFINED_MODULE_NAME', 33);
define('MY_ERROR_UNDEFINED_MODEL_NAME', 34);
define('MY_ERROR_LANGUAGE_NOT_FOUND', 35);
define('MY_ERROR_LANGUAGE_CODE_NOT_FOUND', 36);
define('MY_ERROR_VARIABLE_EMPTY', 37);
define('MY_ERROR_UNRESOLVED_ACCESS', 38);

define('MY_MODULE_NAME_SECURITY', 'security');
define('MY_MODULE_NAME_SERVICE', 'service');
define('MY_COOKIE_NAME_SITE_LANGUAGE', 'site_language');
define('MY_ACTION_NAME_INDEX', 'index');

define('MY_GET_VARS_QUERY_STRING_NAME', 'query_string');
define('MY_SERVICE_VAR_NAME', 'type');
define('MY_LANGUAGE_CODE_VAR_NAME', 'language');
define('MY_ADMIN_PASSWORD_VAR_NAME', 'apsw');

define('MY_COOKIE_MAX_LIFETIME_VALUE', 999999999);

define('MY_UNDEFINED_VALUE', 'undefined');

define('MY_FORM_SUBMIT_REDIRECT_URL_VAR_NAME', 'url_redirect');
define('MY_FORM_SUBMIT_REDIRECT_URL_VALUE_SELF', 'self');
define('MY_FORM_BUTTON_CODE', '@1');

define('MY_DEVICE_MOBILE_TYPE_CODE', 'mobile');
define('MY_DEVICE_DESCTOP_TYPE_CODE', 'desctop');
define('MY_DIR_GENERIC_NAME', 'generic');
define('MY_DIR_DESCTOP_NAME', 'desctop');
define('MY_DIR_MOBILE_NAME', 'mobile');

define('MY_SUCCESS_CODE', 'success');

define('MY_FILTER_TYPE_ALL', 'all');
define('MY_FILTER_TYPE_ONLY_REQUIRED', 'only_required');
define('MY_FILTER_TYPE_WITHOUT_REQUIRED', 'without_required');

define('MY_LANGUAGE_RU', 'ru');
define('MY_LANGUAGE_EN', 'en');


/*
 *  Пользовательские
 */
define('MY_NONE_CATEGORY_CODE', 'none');
define('MY_CONTROLLER_NAME_MAIN', 'main');