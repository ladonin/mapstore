<?php
/*
 * Class vendor\DBase_Mysql
 *
 * Базовый класс моделей db mysql
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor;

use \modules\base\Security\Security;

class DBase_Mysql extends Model
{
    /*
     * Соединение с БД
     *
     * @var resource
     */
    static protected $connect;

    /*
     * Первоначальные данные настроек полей таблицы
     *
     * @var array
     */
    protected $fields_primary_data = array();

    /*
     * Текущие данные настроек полей таблицы
     *
     * @var array
     */
    protected $fields = array();

    /*
     * Имя таблицы
     *
     * @var string
     */
    protected $table_name;


    protected function __construct()
    {
        $this->fields_primary_data = $this->fields;
        $this->connect_to_db();
    }

    /*
     * Получение переменной подключения
     *
     * @return resource
     */
    public function get_connect()
    {
        return self::$connect;
    }

    /*
     * Подключение к базе данных
     */
    protected function connect_to_db()
    {
        $config = self::get_config();
        //подключаемся к mysql
        if (empty(self::$connect)) {

            try {
                $db = $config['db']['mysql'];
                $pdo = new \PDO(
                        'mysql:host=' . $db['host'] . ';dbname=' . $db['dbase'] . ';charset=' . $db['charset'], $db['user'], $db['password'], array(
                    \PDO::ATTR_PERSISTENT => $db['persistent'],
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
                );
                $pdo->exec('set names ' . $db['charset']);
            } catch (\PDOException $e) {
                self::set_error(MY_ERROR_DB_NO_CONNECT, 'mysql', '[connect error] ' . $e->getMessage());
            }
            if (my_is_not_empty(@$pdo)) {
                self::$connect = $pdo;
            } else {
                self::set_error(MY_ERROR_DB_NO_CONNECT, 'mysql', '[connect error] ');
            }
        }
    }

    /*
     * Получение данных из таблицы по первичному ключу
     *
     * @param integer $id - идентификатор строки (первичный ключ)
     *
     * @return array
     */
    public function get_by_id($id)
    {
        if (!$id = (int) $id) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'id:' . $id));
        }

        $conn = self::$connect;

        $sql = "SELECT * FROM " . static::get_table_name() . " WHERE id = $id";
        $result = $conn->query($sql, \PDO::FETCH_ASSOC)->fetch();
        if (!is_array($result)) {
            self::concrete_error(array(MY_ERROR_MYSQL, 'request:' . $sql), MY_LOG_MYSQL_TYPE);
        }

        return $result;
    }



    /*
     * Обновление данных таблицы по первичному ключу и установленным значениям в свойстве fields
     *
     * @param integer $id - идентификатор строки (первичный ключ)
     *
     * @return boolean
     */
    protected function update($id = null)
    {

        if (!$id) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'id:' . $id));
        }

        $this->filter_all_fields(MY_FILTER_TYPE_WITHOUT_REQUIRED);

        try {
            $array_values = array();

            $sql = 'update ' . static::get_table_name() . " set modified='" . MY_TIME . "'";

            $r = 1;
            foreach ($this->fields as $key => $value) {

                // Только, если мы определили значение для поля(решили его обновить)
                if (isset($value['value'])) {

                    foreach ($value['rules'] as $value2) {

                        if ($value2 == 'none') {

                            $r = 0;
                            continue;
                        }
                    }

                    if (!$r) {

                        $r = 1;
                        continue;
                    }

                    $sql .= ',' . $key . '=?';

                    $this->processing_value($value);
                    $array_values[] = $value['value'];
                }
            }
            $sql .= ' where id = ' . (int) $id;

            $stmt = self::$connect->prepare($sql);

            $stmt = $this->execute($stmt, $array_values);

            // сбрасываем данные
            $this->fields = $this->fields_primary_data;
            return true;
        } catch (\PDOException $e) {
            self::concrete_error(array(MY_ERROR_MYSQL, '[error update] ' . $e->getMessage()), MY_LOG_MYSQL_TYPE);
        }
    }

    /*
     * Новая запись в таблицу по установленным значениям в свойстве fields
     *
     * @return integer - идентификатор новой записи
     */
    protected function insert()
    {

        $this->filter_all_fields(MY_FILTER_TYPE_ONLY_REQUIRED);

        try {

            $array_values = array();

            $sql = 'insert into ' . static::get_table_name() . ' (created,modified';

            $r = 1;
            foreach ($this->fields as $key => $value) {

                foreach ($value['rules'] as $value2) {

                    if ($value2 == 'none') {

                        $r = 0;
                    }
                }

                if (!$r) {

                    $r = 1;
                    continue;
                }

                $sql.=',' . $key;
            }
            $sql.=") values ('" . MY_TIME . "', '" . MY_TIME . "'";

            foreach ($this->fields as $key => $value) {

                foreach ($value['rules'] as $value2) {

                    if ($value2 == 'none') {

                        $r = 0;
                    }
                }

                if (!$r) {

                    $r = 1;
                    continue;
                }

                $sql.=",?";
                $this->processing_value($value);
                $array_values[] = isset($value['value']) ? $value['value'] : null;
            }
            $sql.=')';

            $stmt = self::$connect->prepare($sql);

            $stmt = $this->execute($stmt, $array_values);
            $id = self::$connect->lastInsertId();

            if (!$id) {
                self::concrete_error(array(MY_ERROR_MYSQL, '[error insert] (wrong id value) ' . $e->getMessage() . ', request:' . $sql), MY_LOG_MYSQL_TYPE);
            }
        } catch (\PDOException $e) {
            self::concrete_error(array(MY_ERROR_MYSQL, '[error insert] ' . $e->getMessage() . ', request:' . $sql), MY_LOG_MYSQL_TYPE);
        }
        // сбрасываем данные
        $this->fields = $this->fields_primary_data;
        return $id;
    }

    /*
     * Обработка записываемого значения поля по правилам указанным в его настройках
     *
     * @param array $value - данные поля
     */
    protected function processing_value(array &$value)
    {
        if (my_is_not_empty(@$value['processing']) && my_is_not_empty(@$value['value'])) {

            if (in_array("strip_tags", $value['processing'])) {
                $value['value'] = strip_tags($value['value']);
            }

            //
            // сюда можно добавить дополнительные преобразования тегов
            //

            if (in_array("htmlspecialchars", $value['processing'])) {
                $value['value'] = htmlspecialchars($value['value']);
            }

            $flag_a_tag_used = false;
            if (in_array("spec_tags", $value['processing'])) {

                $tags = self::get_module(MY_MODULE_NAME_SERVICE)->get_text_form_tags();
                foreach ($tags as $tag) {
                    if ($tag['code'] === MY_FORM_TEXT_TAG_CODE_B) {
                        $value['value'] = preg_replace('#\[b\](.+?)\[\/b\]#', '<b>$1</b>', $value['value']);
                    } else if ($tag['code'] === MY_FORM_TEXT_TAG_CODE_P) {

                        // Удаляем переносы строк в абзаце
                        $value['value'] = preg_replace_callback(
                                "#\[p\](.+?)\[\/p\]#s", function ($matches) {
                            return str_replace("\n", '', str_replace("\n\r", '', str_replace("\r\n", '', $matches[0])));
                        }, $value['value']);
                        $value['value'] = preg_replace('#\[p\](.+?)\[\/p\]#s', '<p class="text_form_tag_p">$1</p>', $value['value']);
                        //убираем переносы строк после блока, которые добавляли для наглядности редакторе
                        $value['value'] = str_replace("</p>\n", '</p>', str_replace("</p>\n\r", '</p>', str_replace("</p>\r\n", '</p>', $value['value'])));
                    } else if ($tag['code'] === MY_FORM_TEXT_TAG_CODE_STRONG) {
                        $value['value'] = preg_replace('#\[strong\](.+?)\[\/strong\]#', '<strong>$1</strong>', $value['value']);
                    } else if ($tag['code'] === MY_FORM_TEXT_TAG_CODE_A) {
                        if (self::get_module(MY_MODULE_NAME_ACCOUNT)->is_admin()) {
                            $follow = '';
                        } else {
                            $follow = 'rel="nofollow"';
                        }
                        $flag_a_tag_used = true;
                        $value['value'] = preg_replace('#\[a\=(.+?)\](.+?)\[\/a\]#', '<a href="$1" ' . $follow . ' title="$2">$2</a>', $value['value']);
                    }
                }
            }

            if ( in_array("urls", $value['processing'])) {

                if ((self::get_module(MY_MODULE_NAME_ACCOUNT)->is_admin() && (self::get_module(MY_MODULE_NAME_SERVICE)->is_available_to_process_links_in_text_for_admin())) || (self::get_module(MY_MODULE_NAME_SERVICE)->is_available_to_process_links_in_text_for_free_users())) {

                    $value['value'] = preg_replace(
                            "/([^\"]?)\b((http(s?):\/\/)|(www\.))([\w\.\:\-]+)([\/\%\w+\.\:\-#\(\)]+)([\?\w+\.\:\=\-#\(\)]+)([\&\w+\.\:\=\-#\(\)]+)\b([\-]?)([\/]?)/iu", "$1 <a href=\"http$4://$5$6$7$8$9$10\" target=\"_blank\">$5$6$7$8$9$10</a>", $value['value']);
                    //$value['value'] = preg_replace('#(http[s]?:\/\/(?:www\.)?([^ \n\t\r]+))#', ' <a href="$1" target="_blank">$2</a>',$value['value']);
                }
            }
            if (in_array("new_line", $value['processing'])) {
                $value['value'] = str_replace("\n", '<br>', str_replace("\n\r", '<br>', str_replace("\r\n", '<br>', $value['value'])));
            }
        }
    }

    /*
     * Удаление записи из таблицы
     *
     * @param integer $id - идентификатор строки (первичный ключ)
     */
    public function delete($id)
    {
        $id = (int) $id;
        if (!$id) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'id:' . $id));
        }
        try {
            $sql = 'DELETE FROM ' . static::get_table_name() . ' WHERE id = ' . $id;
            self::$connect->exec($sql);
        } catch (\PDOException $e) {
            self::concrete_error(array(MY_ERROR_MYSQL, '[error delete] ' . $e->getMessage()), MY_LOG_MYSQL_TYPE);
        }
    }



    /*
     * Задаем значения полям;
     * мы просто проверяем существующие заданные значения;
     * переменные (возможно нужные) с пустыми значениями запишутся, но не пройдут следующюу проверку при insert/update;
     * смысл в том, что эти значения можно перед insert/update несколько раз изменять, главное, чтобы существенные части передавались правильно
     *
     * @param array $data - значения полей
     *
     * @return boolean
     */
    public function set_values_to_fields(array $data)
    {
        // Вставляем массив значений в данные для таблицы
        foreach ($data as $name => $value) {

            // Фильтруем значения
            $this->filter($name, $value, MY_FILTER_TYPE_WITHOUT_REQUIRED);
            // Если передалось пустое значение, но оно не должно быть пустым
            if (!$value && my_is_not_empty(@$this->fields[$name]['default_value'])) {
                $value = $this->fields[$name]['default_value'];
            }
            $this->fields[$name]['value'] = $value;
        }
        return true;
    }

    /*
     * Проверяем все поля на валидность значений
     *
     * @param string $filter_type - тип валидации
     *
     * @return boolean
     */
    protected function filter_all_fields($filter_type = MY_FILTER_TYPE_ALL)
    {

        // Проверяем все поля модели
        foreach ($this->fields as $name => $data) {

            // Фильтруем все значения полей
            $this->filter($name, @$data['value'], $filter_type);
        }
        return true;
    }

    /*
     * Получаем массив строк, полученных при select
     *
     * @param resource $stmt - statement
     *
     * @return array
     */
    protected function fetch_many($stmt)
    {
        $array = array();
        while ($row = $stmt->fetch()) {
            $array[] = $row;
        }

        return $array;
    }

    /*
     * Получаем массив данных одной строки, полученной при select
     *
     * @param resource $stmt - statement
     *
     * @return array
     */
    protected function fetch_one($stmt)
    {

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    /*
     * Возвращает сортировку для select, если не asc, то desc
     *
     * @param string $order - тип сортировки
     *
     * @return string
     */
    protected function return_order($order)
    {

        if ($order === 'asc') {
            return $order;
        }

        return 'desc';
    }

    /*
     * Выполнение подготовленного запроса
     *
     * @param resource $stmt - statement
     * @param array $params - параметры запроса
     *
     * @return statement
     */
    protected function execute($stmt, array $params = null)
    {
        $stmt->execute($params);
        return $stmt;
    }

    /*
     * Подготовка строкового значения limit
     *
     * @param array $limit - параметры $limit
     *
     * @return string
     */
    protected function return_limit(array $limit = array(1))
    {

        if ((int) $limit[1] > 0) {

            $text = (int) $limit[0] . ',' . (int) $limit[1];
        } else if ((int) $limit[0] > 0) {

            $text = (int) $limit[0];
        } else {

            $text = '1';
        }

        return $text;
    }

    /*
     * Получение результата по прямому запросу
     *
     * @param string $condition - условие where
     * @param string $order - условие order by
     * @param string $group - условие group by
     * @param string $select - условие select
     * @param string $limit - условие limit
     * @param boolean $need_result - приемлем ли пустой результат
     *
     * @return array - полученные данные из таблицы
     */
    public function get_by_condition($condition, $order = '', $group = '', $select = '*', $limit = false, $need_result = true)
    {
        if (!$condition) {
            $condition = 1;
        }

        $conn = self::$connect;

        $sql = 'SELECT ' . $select . ' FROM ' . static::get_table_name() . ' WHERE ' . $condition;

        try {
            if ($group) {
                $sql .= ' GROUP BY ' . $group . ' ';
            }
            if ($order) {
                $sql .= ' ORDER BY ' . $order . ' ';
            }
            if ($limit == false) {
                $result = $conn->query($sql, \PDO::FETCH_ASSOC)->fetchAll();
            } else if ($limit === 1) {
                $result = $conn->query($sql . ' limit 1', \PDO::FETCH_ASSOC)->fetch();
            } else if ($limit > 1) {
                $result = $conn->query($sql . ' limit ' . $limit, \PDO::FETCH_ASSOC)->fetchAll();
            }
        } catch (\PDOException $e) {
            self::concrete_error(array(MY_ERROR_MYSQL, 'request:' . $sql), MY_LOG_MYSQL_TYPE);
        }

        if (!isset($result) || !is_array($result) || !$result) {
            if ($need_result === true) {
                self::concrete_error(array(MY_ERROR_MYSQL, 'request:' . $sql), MY_LOG_MYSQL_TYPE);
            } else {
                return array();
            }
        }

        return $result;
    }
    /*
      public function set_values_to_fields_from_form(\vendor\Form $form) {

      $data = $form->get_all_fields();

      //вставл¤ем массив значений в таблицу

      foreach ($data as $name => $field) {

      // Только если имя формы сответствует имени пол¤ в таблице
      if (array_key_exists($name, $this->fields)) {

      //фильтруем значени¤
      $this->filter($name, $field['value'], MY_FILTER_TYPE_ALL);

      $this->fields[$name]['value'] = $field['value'];
      }
      }
      return true;
      }
     */
}
