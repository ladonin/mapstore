<?php
/*
 * Db модель geocode_collection
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace models\dbase\mysql\geocode_collection;

use \components\app as components;

abstract class Model extends \vendor\DBase_Mysql
{
    /*
     * Имя таблицы
     */
    public function get_table_name()
    {
        if (!$this->table_name) {
            $this->table_name = get_service_name() . '_geocode_collection';
        }
        return $this->table_name;
    }

    /*
     * Поля таблицы
     *
     * @var array
     */
    protected $fields = array(
        'map_data_id' => array(
            // Правила валидации значений поля
            'rules' => array('numeric', 'required'),
        ),
        'language' => array(
            'rules' => array('required'),
        ),
        'country_code' => array(
            'rules' => array(), //теперь required не нужен, раз есть  default_value
            // Дефолтное значение поля
            'default_value' => MY_UNDEFINED_VALUE, // если сюда передается пустое значение, то оно будет заменено на это
            // Текущее значение поля
            'value' => MY_UNDEFINED_VALUE, // если это поле вообще не передалось с данными извне, то поле будет иметь такое значение
        ),
        'state_code' => array(
            'rules' => array(), //теперь required не нужен, раз есть  default_value
            'default_value' => MY_UNDEFINED_VALUE,
            'value' => MY_UNDEFINED_VALUE, // если это поле вообще не передалось с данными извне, то поле будет иметь такое значение
        ),
        'json_data' => array(
            'rules' => array(),
        ),
        'formatted_address' => array(
            'rules' => array(),
        ),
        'street_address' => array(
            'rules' => array(),
        ),
        'route' => array(
            'rules' => array(),
        ),
        'intersection' => array(
            'rules' => array(),
        ),
        'political' => array(
            'rules' => array(),
        ),
        'country' => array(
            'rules' => array(),
        ),
        'administrative_area_level_1' => array(
            'rules' => array(),
        ),
        'administrative_area_level_2' => array(
            'rules' => array(),
        ),
        'administrative_area_level_3' => array(
            'rules' => array(),
        ),
        'administrative_area_level_4' => array(
            'rules' => array(),
        ),
        'administrative_area_level_5' => array(
            'rules' => array(),
        ),
        'colloquial_area' => array(
            'rules' => array(),
        ),
        'locality' => array(
            'rules' => array(),
        ),
        'ward' => array(
            'rules' => array(),
        ),
        'sublocality' => array(
            'rules' => array(),
        ),
        'neighborhood' => array(
            'rules' => array(),
        ),
        'premise' => array(
            'rules' => array(),
        ),
        'subpremise' => array(
            'rules' => array(),
        ),
        'postal_code' => array(
            'rules' => array(),
        ),
        'natural_feature' => array(
            'rules' => array(),
        ),
        'airport' => array(
            'rules' => array(),
        ),
        'park' => array(
            'rules' => array(),
        ),
        'point_of_interest' => array(
            'rules' => array(),
        ),
    );

    /*
     * Добавляем запись одной метки на одном языке
     *
     * @param integer $data_id - id метки
     * @param string $language - язык
     * @param array $en_data - данные этой же метки на английском
     *
     * @return array - добавленные данные
     */
    public function add_one_language($data_id, $language, array $en_data)
    {
        $db_model_data = components\Map::get_db_model('data');

        $data = $db_model_data->get_by_id($data_id);
        $data = $this->prepare_address(array('x' => $data['x'], 'y' => $data['y']), $data_id, $language, array('country' => $en_data['country'], 'administrative_area_level_1' => $en_data['administrative_area_level_1']));

        //перед этим удалим возможно уже существующую запись
        $this->delete_adresses($data_id, $language);

        //теперь пишем
        $this->set_values_to_fields($data);
        $this->insert();

        return $data;
    }

    /*
     * Добавляем записи одной метки на всех доступных языках
     *
     * @param array $coords - координаты метки
     * @param integer $data_id - id метки
     *
     * @return array - добавленные данные
     */
    public function add($coords, $data_id)
    {
        $result = array();

        $languages_data = self::get_module(MY_MODULE_NAME_SERVICE)->get_languages();

        // Английский язык нужен обязательно вначале, чтобы подготовить данные для кода страны и т.д. неанглийских языков
        $data = $this->prepare_address($coords, $data_id, MY_LANGUAGE_EN);
        $this->set_values_to_fields($data);
        $result[] = $this->insert();

        // Берем все языки, что указали для сервиса
        foreach ($languages_data as $language_data) {

            $language = $language_data['code'];
            // Передаем остальным языкам английские данные, чтобы из них сформировать код страны и штата
            if ($language !== MY_LANGUAGE_EN) {
                $data = $this->prepare_address($coords, $data_id, $language, array(
                    'country' => isset($data['country']) ? $data['country'] : MY_UNDEFINED_VALUE,
                    'administrative_area_level_1' => isset($data['administrative_area_level_1']) ? $data['administrative_area_level_1'] : MY_UNDEFINED_VALUE));
                $this->set_values_to_fields($data);
                $result[] = $this->insert();
            }
        }

        return $result;
    }


    /*
     * Проверяем есть ли такая метка по указанному адресу
     *
     * @param integer $id - id метки
     * @param string $country_code - код страны
     * @param array $state_code - код региона
     *
     * @return boolean
     */
    public function check_placemark($id, $country_code, $state_code)
    {
        $id = (int) $id;
        $country = self::$connect->quote($country_code);
        $data_db_model = components\Map::get_db_model('data');

        $condition = "map_data_id = $id AND country_code = $country ";
        if ($state_code) {
            $state = self::$connect->quote($state_code);
            $condition .= "AND state_code = $state";
        }
        $result = $this->get_by_condition($condition, '', '', 'id', 1, false);
        return my_is_not_empty(@$result['id']) ? true : false;
    }


    /*
     * Возвращает все существующие в сервисе страны
     *
     * @return array
     */
    public function get_countries()
    {
        $data_db_model = components\Map::get_db_model('data');

        $language_model = components\Language::get_instance();
        $language = $language_model->get_language();

        $condition = "language = '" . $language . "' AND country_code!='" . MY_UNDEFINED_VALUE . "' AND country_code!=''";
        $result = $this->get_by_condition($condition, 'country', '', 'DISTINCT country, country_code');
        return $result;
    }

    /*
     * Обновляет все геоданные метки
     *
     * @param array $coords - кординаты новые данные метки
     * @param integer $data_id - id обновляемой метки
     *
     * @return boolean(false)/array - новые геоданные метки на всех языках
     */
    public function update_record($coords, $data_id)
    {
        if (my_is_not_empty(@$coords['x']) && my_is_not_empty(@$coords['y'])) {
            // Удалим ВСЕ старые записи для этой метки
            $this->delete_adresses($data_id);
            // Добавляем новые
            return $this->add($coords, $data_id);
        }
        return false;
    }

    /*
     * Удаляем геоданные метки
     *
     * @param integer $data_id - id обновляемой метки
     * @param string $language - язык, данные которого удаляем (если не указан, то удаляем данные на всех языках)
     */
    public function delete_adresses($data_id, $language = null)
    {
        $data_id = (int) $data_id;

        if (my_is_empty(@$data_id)) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'data_id:' . $data_id));
        }
        $data_db_model = components\Map::get_db_model('data');

        // Берем ids
        $condition = "map_data_id=" . $data_id;
        if ($language) {
            $condition.=" AND language='" . $language . "'";
        }
        $results = $this->get_by_condition($condition, '', '', '*', false, false);

        foreach ($results as $result) {
            $this->delete($result['id']);
        }
    }

    /*
     * Подготавливаем геоданные
     *
     * @param array $data - подготавливаемые геоданные
     * @param integer $data_id - id метки
     * @param string $language - язык, данные на котором подготавливаем
     * @param array $en_data - образец данных на английском языке
     *
     * @return array - подготовленные геоданные
     */
    public function prepare_address($data, $data_id, $language, $en_data = array())
    {

        if ((my_is_empty(@$data['x'])) || (my_is_empty(@$data['y']))) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'data:' . @json_encode($data)));
        }
        if (my_is_empty(@$data_id)) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'data_id:' . $data_id));
        }
        if (my_is_empty(@$language)) {
            self::concrete_error(array(MY_ERROR_FUNCTION_ARGUMENTS, 'language:' . $language));
        }

        $language_model = components\Language::get_instance();
        $language_model->is_available_language($language);

        $map_module = self::get_module(MY_MODULE_NAME_MAP);
        $adress = $map_module->get_adress_by_coords($data, $language);
        $adress_json = json_encode($adress);
        $data_db_model = components\Map::get_db_model('data');

        $data = array(
            'map_data_id' => $data_id,
            'language' => $language,
            'json_data' => $adress_json,
            'formatted_address' => $adress['formatted_adress']
        );
        foreach ($adress['address_components'] as $parameters) {

            if ($parameters['types'][0] === 'street_address') {
                $data['street_address'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'route') {
                $data['route'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'intersection') {
                $data['intersection'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'political') {
                $data['political'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'country') {
                $data['country'] = $parameters['long_name'];
                $data['country_code'] = my_prepare_to_one_word(@$en_data['country'], $parameters['long_name']);
            } else if ($parameters['types'][0] === 'administrative_area_level_1') {
                $data['administrative_area_level_1'] = $parameters['long_name'];
                $data['state_code'] = my_prepare_to_one_word(@$en_data['administrative_area_level_1'], $parameters['long_name']);
            } else if ($parameters['types'][0] === 'administrative_area_level_2') {
                $data['administrative_area_level_2'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'administrative_area_level_3') {
                $data['administrative_area_level_3'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'administrative_area_level_4') {
                $data['administrative_area_level_4'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'administrative_area_level_5') {
                $data['administrative_area_level_5'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'colloquial_area') {
                $data['colloquial_area'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'locality') {
                $data['locality'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'ward') {
                $data['ward'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'sublocality') {
                $data['sublocality'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'neighborhood') {
                $data['neighborhood'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'premise') {
                $data['premise'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'subpremise') {
                $data['subpremise'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'postal_code') {
                $data['postal_code'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'natural_feature') {
                $data['natural_feature'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'airport') {
                $data['airport'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'park') {
                $data['park'] = $parameters['long_name'];
            } else if ($parameters['types'][0] === 'point_of_interest') {
                $data['point_of_interest'] = $parameters['long_name'];
            }
        }

        if (my_is_empty(@$data['state_code'])) {
            $data['administrative_area_level_1'] = MY_UNDEFINED_VALUE;
            $data['state_code'] = MY_UNDEFINED_VALUE;
        }

        return $data;
    }

    /*
     * Получаем геоданные (и их ids) по коду страны и региона
     *
     * @param string $country_code - код страны
     * @param string $state_code - код региона
     * @param boolean $need_result - обязателен ли возвращаемый результат (или можно пустой, если не найдено ничего)
     *
     * @return array
     */
    public function get_placemarks_data($country_code = null, $state_code = null, $need_result = false)
    {
        $connect = $this->get_connect();
        $data_db_model = components\Map::get_db_model('data');
        $country_component = components\Countries::get_instance();

        $config = self::get_config();
        $language_component = components\Language::get_instance();
        $language = $language_component->get_language();

        $condition = "language='" . $language . "'";
        if ($country_code) {
            $condition .= " AND country_code=" . $connect->quote($country_code);
        }
        if ($state_code) {
            $condition .= " AND state_code=" . $connect->quote($state_code);
        }

        $order = "id DESC";
        $select = 'DISTINCT map_data_id as placemarks_id, state_code, country_code, formatted_address, country, administrative_area_level_1 as state, locality';
        $limit = false;

        $placemarks_data = $this->get_by_condition($condition, $order, '', $select, $limit, $need_result);

        $result = array();
        if (my_array_is_not_empty(@$placemarks_data)) {
            foreach ($placemarks_data as $placemark) {
                if (($placemark['state']) && ($placemark['state_code']) && ($placemark['country_code'])) {
                    $placemark['state'] = $country_component->translate_state_names($language, $placemark['country_code'], $placemark['state'], $placemark['state_code']);
                }
                // массив id для implode
                $result['ids'][] = $placemark['placemarks_id'];
                $result['data'][$placemark['placemarks_id']] = $placemark;
            }
        }

        return $result;
    }
}
