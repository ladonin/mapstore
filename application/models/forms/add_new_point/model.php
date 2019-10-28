<?php
/*
 * Class models\forms\add_new_point\Model
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace models\forms\add_new_point;

use \components\app as components;

abstract class Model extends \vendor\Form
{
    /*
     * Name формы
     */
    const FORM_NAME = 'add_new_point_form';

    /*
     * Общие параметры формы
     *
     * @var array
     */
    protected $form_params = array(
        'js_before' => array(),
        'js_after' => array(),
        'action' => 'mvc/map/ajax_add_new_point',
        'method' => 'POST',
        'name' => '',
        'enctype' => '',
        'js' => array(
        //'onSubmit' => 'return my_New_Point.check()'
        ),
    );

    /*
     * При клике по какому блоку с id будет субмит
     *
     * @var string
     */
    protected $submit_bind_id = 'placemark_add_commit';

    /*
     * Поля формы
     *
     * @var array
     */
    protected $fields = array(
        // hidden поле редиректа
        MY_FORM_SUBMIT_REDIRECT_URL_VAR_NAME => array(MY_FORM_SUBMIT_REDIRECT_URL_VALUE_SELF),
        'x' => array(
            // Тип поля
            'kind' => 'input',
            // Параметры поля
            'entity' => array(
                'type' => 'hidden',
                'required' => true,
                'autocomplete' => 'off'
            ),
        ),
        'y' => array(
            'kind' => 'input',
            'entity' => array(
                'type' => 'hidden',
                'required' => true,
                'autocomplete' => 'off'
            ),
        ),
        'photos' => array(
            'kind' => 'input',
            'entity' => array(
                'type' => 'hidden',
                'required' => true,
                'autocomplete' => 'off'
            ),
        ),
        'comment' => array(
            'kind' => 'textarea',
            'entity' => array(
                'label' => 'form/map_new_point/comment/label', //для Language
                'class' => 'new_point_comment',
                'maxlength' => 20000,
                'value' => '',
                'autocomplete' => 'off'
            ),
        ),
        'title' => array(
            'kind' => 'input',
            'entity' => array(
                'type' => 'text',
                'label' => 'form/map_new_point/title/label', //для Language
                'class' => '',
                'maxlength' => 255,
                'value' => '',
                'autocomplete' => 'off'
            ),
        ),
        'category' => array(
            'kind' => 'select',
            'entity' => array(
                'label' => 'form/map_new_point/category/label', //для Language
                'autocomplete' => 'off',
                /* 'class' => 'width-30-percents', */
                'options' => array()// определяем ниже в конструкторе
            ),
        ),
        'email' => array(
            'kind' => 'input',
            'entity' => array(
                'type' => 'text',
                'label' => 'form/map_generic_new_point/email/label',
                'class' => 'width-50-percents',
                'maxlength' => 255,
                'value' => '',
                'autocomplete' => 'off'
            ),
        ),
        'password' => array(
            'kind' => 'input',
            'entity' => array(
                'type' => 'password',
                'label' => 'form/map_generic_new_point/password/label',
                'class' => 'width-50-percents',
                'maxlength' => 255,
                'value' => '',
                'autocomplete' => 'off'
            ),
        ),
            // 'delete_photos[photo_N]' checkboxes- written by hand
            // 'id' hidden - written by hand
    );


    protected function __construct()
    {
        // Задаем options для поля 'category'
        $this->fields['category']['entity']['options'] = self::get_module(MY_MODULE_NAME_SERVICE)->get_categories_add_new_point_form_options();

        if (self::get_module(MY_MODULE_NAME_SERVICE)->is_need_photos_for_placemarks() === false) {
            $this->fields['photos']['entity']['required'] = false;
        }
    }
}
