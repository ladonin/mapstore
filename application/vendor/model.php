<?php
/*
 * Class vendor\Model
 *
 * Базовый класс всех моделей
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor;

abstract class Model extends Component
{

    use \vendor\traits\patterns\t_singleton;

    protected $fields = array();

    /*
     * Проверка значения поля по указанным в нем правилам
     *
     * @param string $name - название поля
     * @param string $value - значение поля
     * @param string $filter_type - тип валидации (все правила, только правило required, все правила кроме required)
     * @param string $with_rollback - если найдено несоответствие - то вызывать ошибку с откатом или возвращать просто false
     *
     * @return boolean
     */
    public function filter($name, $value, $filter_type = MY_FILTER_TYPE_ALL, $with_rollback = true)
    {

        if (!isset($this->fields[$name])) {
            self::concrete_error(array(MY_ERROR_DB_UNDEFINED_FIELD, 'unknown field_name: ' . $name . ", value='" . $value . "'"));
        }
        if (isset($this->fields[$name]['rules']) && is_array($this->fields[$name]['rules'])) {

            foreach ($this->fields[$name]['rules'] as $key => $rule) {
                if (($filter_type === MY_FILTER_TYPE_ALL) || (($filter_type === MY_FILTER_TYPE_ONLY_REQUIRED) && $rule === 'required') || (($filter_type === MY_FILTER_TYPE_WITHOUT_REQUIRED) && $rule !== 'required')) {
                    $result = $this->validate($rule, $value, $key);
                    if ($result === MY_ERROR_UNKNOWN_VALIDATION_RULE) {
                        self::concrete_error(array(MY_ERROR_MODEL_FILTER, 'unknown rule: name=' . $name . ", value='" . $value . "', key='" . $key . "', rule='" . $rule . "'"));
                    } else if (!$result) {
                        if ($with_rollback === true) {
                            self::concrete_error(array(MY_ERROR_MODEL_FILTER, 'wrong value: name=' . $name . ", value='" . $value . "', key='" . $key . "', rule='" . $rule . "'"));
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
