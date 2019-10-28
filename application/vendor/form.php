<?php
/*
 * Class vendor\Form
 *
 * Базовый класс моделей формы
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace vendor;

use \modules\base\Security\Security;

abstract class Form extends Model
{
    /*
     * Статус, что hidden поля уже отображены
     *
     * @var boolean
     */
    protected $hidden_fields_is_shown = false;

    /*
     * Параметры формы
     *
     * @var array
     */
    protected $form_params = array();

    /*
     * Вернуть массив всех полей
     *
     * @return array
     */
    public function get_all_fields()
    {
        return $this->fields;
    }

    /*
     * Вернуть параметры формы
     *
     * @return array
     */
    public function get_form_params()
    {
        if (my_is_not_empty(@$this->form_params)) {
            return $this->form_params;
        }
        return null;
    }

    /*
     * Вернуть данные поля по его имени
     *
     * @return array
     */
    public function get_field($name)
    {
        if (my_is_not_empty(@$this->fields[$name])) {
            return $this->fields[$name];
        }
        return null;
    }

    /*
     * Вернуть кнопку формы
     *
     * @return array
     */
    public function get_button()
    {
        if (my_is_not_empty(@$this->button)) {
            return $this->button;
        }
        return null;
    }

    /*
     * Замена специальных символов в html формы значениями переменных
     *
     * @return string
     */
    protected function change_special_names(&$text)
    {

        $text = str_replace('%form_name%', "'" . static::FORM_NAME . "'", $text);
        $text = str_replace('%form_object%', "my_" . static::FORM_NAME . "_object", $text);

        return $text;
    }

    /*
     * Получение html тега <form ...>
     *
     * @return string
     */
    public function start_form()
    {

        $params = $this->get_form_params();

        $return = '';

        if (my_array_is_not_empty(@$params['js_before'])) {
            $return .="\n<script type=\"text/javascript\">\n"
                    . "$(document).ready(function () {";
            foreach ($params['js_before'] as $js_code) {
                $return .=$js_code . ";\n";
            }
            $return .="})</script>\n";
        }
        $this->change_special_names($return);

        $return .= "\n<div class='form_div_wrapper'><form";

        if (my_is_not_empty(@$params['action'])) {

            $return.=' action="' . MY_DOMEN . MY_DS . $params['action'] . '?' . self::get_query_string() . '"';
        }
        if (my_is_not_empty(@$params['method'])) {

            $return.=' method="' . $params['method'] . '"';
        }

        $return.=' name="' . static::FORM_NAME . '"';

        if (my_is_not_empty(@$params['enctype'])) {

            $return.=' enctype="' . $params['enctype'] . '"';
        }

        $return.=' id="' . $this->get_id() . '"';

        if (my_is_not_empty(@$params['js'])) {

            $return.=$this->get_inner_js($params['js']);
        }


        $return.=">\n";
        return $return;
    }

    /*
     * Получение html тега </form ...>
     *
     * @return string
     */
    public function end_form()
    {
        $params = $this->get_form_params();
        $return = "</form></div>\n";

        if (my_array_is_not_empty(@$params['js_after'])) {
            $return .="\n<script type=\"text/javascript\">\n"
                    . "$(document).ready(function () {";
            foreach ($params['js_after'] as $js_code) {
                $return .=$js_code . ";\n";
            }
            $return .="})"
                    . ";</script>\n";
        }

        if (isset($this->submit_bind_id) && $this->submit_bind_id) {
            $return .="\n<script type=\"text/javascript\">\n
                    $(document).ready(function () {
                        $('#" . $this->submit_bind_id . "').click(function () {
                            %form_object%.submit();
                        });\n
                    });
                    </script>\n";
        }

        $this->change_special_names($return);
        return $return;
    }

    /*
     * Получение html тега кнопки формы
     *
     * @return string
     */
    public function show_form_button()
    {

        $button = $this->get_button();
        if (!$button) {
            return '';
        }

        $entity = $button['entity'];
        if ($button['kind'] == 'div') {

            $text = "<div ";

            if (my_is_not_empty(@$entity['id'])) {

                $text.=' id="' . $this->get_id($entity['id']) . '"';
            } else {
                $text.=' id="' . $this->get_id(MY_FORM_BUTTON_CODE) . '"';
            }

            if (my_is_not_empty(@$entity['class'])) {

                $text.=' class="' . $entity['class'] . '"';
            }

            if (my_is_not_empty(@$entity['js'])) {

                $text.=$this->get_inner_js($entity['js']);
            }
            $text.=">";

            if (my_is_not_empty(@$entity['value'])) {

                $text.= my_pass_through(@self::trace($entity['value']));
            }

            $text.="</div>\n";
        } else if ($button['kind'] = 'input') {

            $text = "<input ";
            if (my_is_not_empty(@$entity['type'])) {

                $text.=' type="' . $entity['type'] . '"';
            } else {
                $text.=' type="submit"';
            }
            if (my_is_not_empty(@$entity['name'])) {

                $text.=' name="' . $entity['name'] . '"';
            }
            if (my_is_not_empty(@$entity['class'])) {

                $text.=' class="' . $entity['class'] . '"';
            }
            if (my_is_not_empty(@$entity['value'])) {

                $text.=' value="' . my_pass_through(@self::trace($entity['value'])) . '"';
            }
            if (my_is_not_empty(@$entity['js'])) {

                $text.=$this->get_inner_js($entity['js']);
            }

            $text.=' id="' . $this->get_id(MY_FORM_BUTTON_CODE) . '"';

            $text.=">\n";
        }
        return $text;
    }

    /*
     * Получение внутренего js кода элемента формы
     *
     * @param string $entity_js - тип js события
     *
     * @return string
     */
    protected function get_inner_js($entity_js)
    {
        $text = ' ';

// 1) Событие onClick
        if (my_is_not_empty(@$entity_js['onClick'])) {
            $text.=' onclick="' . $entity_js['onClick'] . '"';
        }
// 2) Событие onChange
        if (my_is_not_empty(@$entity_js['onChange'])) {
            $text.=' onchange="' . $entity_js['onChange'] . '"';
        }
// 3) Событие onSubmit
        if (my_is_not_empty(@$entity_js['onSubmit'])) {
            $text.=' onsubmit="' . $entity_js['onSubmit'] . '"';
        }

// Заменяем спец переменые
        $this->change_special_names($text);

        return $text;
    }

    /*
     * Получение id элемента формы или самой формы
     *
     * @param string $name - имя элемента
     *
     * @return string
     */
    public function get_id($name = false)
    {

        if ($name === MY_FORM_BUTTON_CODE) {
            return static::FORM_NAME . '_button';
        } else if ($name) {
            return static::FORM_NAME . '_' . $name;
        } else {
            return static::FORM_NAME;
        }
    }

    /*
     * Получение html hidden элементов формы
     *
     * @return string
     */
    public function show_hidden_fields()
    {
        $result = '';
// выводим поля один раз только
        if ($this->hidden_fields_is_shown === true) {
            return $result;
        }

        foreach ($this->fields as $name => $field) {

            if (isset($field['entity']['type']) && $field['entity']['type'] === 'hidden') {
                $result .= $this->show_field($name);
            }
        }
// помечаем, что вывели hidden поля
        $this->hidden_fields_is_shown = true;
        return $result;
    }

    /*
     * Получение html label для элемента формы
     *
     * @param string $name - имя элемента
     *
     * @return string
     */
    public function show_label($name)
    {
        $field = $this->fields[$name];
        $entity = $field['entity'];
        return "<label for=\"" . $this->get_id($name) . '">' . my_pass_through(@self::trace($entity['label'])) . "</label>\n";
    }

    /*
     * Получение html элемента формы
     *
     * @param string $name - имя элемента
     * @param boolean $show_label - показывать label или нет
     * @param string $input_value - значение value элемента
     *
     * @return string
     */
    public function show_field($name, $show_label = false, $input_value = null)
    {

        if ($name === MY_FORM_SUBMIT_REDIRECT_URL_VAR_NAME) {
// если поле для возврата после submit - конфигурируется автоматически
            if ($this->fields[$name][0] === MY_FORM_SUBMIT_REDIRECT_URL_VALUE_SELF) {
                $value = self::get_self_url();
            } else {
                $value = $this->fields[$name][0];
            }

            $field = array(
                'kind' => 'input',
                'entity' => array(
                    'type' => 'hidden',
                    'required' => true,
                    'value' => $value,
                    'autocomplete' => 'off'
                ),
            );
        } else {
            $field = $this->fields[$name];
        }

        if ($field['kind'] === 'input') {

            $text = '';
            $entity = $field['entity'];

            if (($show_label === true) && (my_is_not_empty(@$entity['label']))) {
                $text.="    <label for=\"" . $this->get_id($name) . '">' . my_pass_through(@self::trace($entity['label'])) . "</label>\n";
            }

            $text.='<input id="' . $this->get_id($name) . '" name="' . static::FORM_NAME . '[' . $name . ']';

            if (my_is_not_empty(@$entity['multiple'])) {

                $text.='[]" multiple';
            } else {
                $text.='"';
            }

            if (my_is_not_empty(@$entity['type'])) {

                $text.=' type="' . $entity['type'] . '"';
            }

            if (my_is_not_empty(@$entity['autocomplete'])) {

                $text.=' autocomplete="' . $entity['autocomplete'] . '"';
            }


            if ($input_value) {
                $text.=' value="' . $input_value . '"';
            } else if (my_is_not_empty(@$entity['value'])) {

                if ($name === MY_FORM_SUBMIT_REDIRECT_URL_VAR_NAME) {
                    $text.=' value="' . $entity['value'] . '"';
                } else {
                    $text.=' value="' . my_pass_through(@self::trace($entity['value'])) . '"';
                }
            }

            if (my_is_not_empty(@$entity['required'])) {

                $text.=' required';
            }
            if (my_is_not_empty(@$entity['class'])) {

                $text.=' class="' . $entity['class'] . '"';
            }
            if (my_is_not_empty(@$entity['maxlength'])) {

                $text.=' maxlength="' . $entity['maxlength'] . '"';
            }

            if (my_is_not_empty(@$entity['pattern'])) {

                $text.=' pattern="' . $entity['pattern'] . '"';
            }

            if (my_is_not_empty(@$entity['js'])) {

                $text.=$this->get_inner_js($entity['js']);
            }

//...........и т.д.

            $text.=">\n";
        } else if ($field['kind'] === 'select') {
            $entity = $field['entity'];
            $text = '<select id="' . $this->get_id($name) . '" name="' . static::FORM_NAME . '[' . $name . ']"';
            if (my_is_not_empty(@$entity['class'])) {

                $text.=' class="' . $entity['class'] . '"';
            }
            if (my_is_not_empty(@$entity['js'])) {

                $text.=$this->get_inner_js($entity['js']);
            }

            $text.=">\n";
            if (my_is_not_empty(@$entity['options'])) {

                foreach ($entity['options'] as $value) {

                    $text.="    <option value=\"" . $value[0] . '"';

                    if (my_is_not_empty(@$value[2])) {
                        $text.=' ' . $value[2];
                    }

                    if (($input_value && !isnull($input_value) && ($input_value === $value[0])) || (isset($entity['value']) && ($entity['value'] === $value[0]))) {
                        $text .= ' selected';
                    }

                    $text.='>' . my_pass_through(@self::trace($value[1])) . "</option>\n";
                }
            }
            $text.="</select>\n";
        } else if ($field['kind'] === 'textarea') {

            $text = '';
            $entity = $field['entity'];

            if (($show_label === true) && (my_is_not_empty(@$entity['label']))) {
                $text.="    <label for=\"" . $this->get_id($name) . '">' . my_pass_through(@self::trace($entity['label'])) . "</label>\n";
            }

            $text.='<textarea id="' . $this->get_id($name) . '" name="' . static::FORM_NAME . '[' . $name . ']"';

            if (my_is_not_empty(@$entity['required'])) {
                $text.=' required';
            }
            if (my_is_not_empty(@$entity['autocomplete'])) {

                $text.=' autocomplete="' . $entity['autocomplete'] . '"';
            }
            if (my_is_not_empty(@$entity['class'])) {

                $text.=' class="' . $entity['class'] . '"';
            }
            if (my_is_not_empty(@$entity['maxlength'])) {

                $text.=' maxlength="' . $entity['maxlength'] . '"';
            }

            if (my_is_not_empty(@$entity['pattern'])) {

                $text.=' pattern="' . $entity['pattern'] . '"';
            }

            if (my_is_not_empty(@$entity['js'])) {

                $text.=$this->get_inner_js($entity['js']);
            }

            $text.='>';
            if ($input_value) {
                $text .= $input_value;
            } else if (my_is_not_empty(@$entity['value'])) {
                $text.= my_pass_through(@self::trace($entity['value']));
            }
            $text.="</textarea>\n";
        }

        return $text;
    }


    /*
     * Проверяем приходящие из POST значения, которые должны потом записаться в свойство fields
     *
     * @param boolean $call_error - вызывать ошибку в случае находждения несоответствия или нет
     *
     * @return boolean
     */
    public function prepare_fields($call_error = true)
    {
        $post = &$_POST[static::FORM_NAME];
        foreach ($this->fields as $field_name => $parameters) {
            if (my_is_not_empty(@$parameters['entity']['required']) && my_is_empty(@$post[$field_name])) {

                if ($call_error) {
                    self::concrete_error(array(MY_ERROR_FORM_WRONG_DATA, 'calling field:' . $field_name . '=' . @$post[$field_name] . '; POST array:' . json_encode($_POST)));
                } else {
                    return false;
                }
            } else {
                if (!isset($post[$field_name])) {
                    $post[$field_name] = '';
                }

                // проверяем что ввели
                if (isset($parameters['kind']) && ($parameters['kind'] === 'select')) {
                    $this->check_select_type_field($field_name, $post[$field_name]);
                }
            }
        }
        return true;
    }

    /*
     * Проверяем, что пришедшее из POST значение равно одному из возможных значений в элементе select
     *
     * @param string $field_name - название поля
     * @param string $post_value - значение POST для этого поля
     * @param boolean $call_error - вызывать ошибку в случае находждения несоответствия или нет
     *
     * @return boolean
     */
    public function check_select_type_field($field_name, $post_value, $call_error = true)
    {
        if (!$field_name) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'field_name:' . $field_name));
        }

        $parameters = $this->fields[$field_name];
        $post_value = (int) $post_value;
        foreach (@$parameters['entity']['options'] as $value => $option) {
            // Равно ли значение одному из допустимых параметров
            if ($post_value === $value) {
                return true;
            }
        }
        if ($call_error === true) {
            self::concrete_error(array(MY_ERROR_FORM_WRONG_DATA, 'calling field:' . $field_name . '=' . $post_value . '; POST array:' . json_encode($_POST)));
        } else {
            return false;
        }
    }
}
