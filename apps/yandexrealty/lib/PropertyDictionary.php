<?php
namespace yandexrealty\lib;

class PropertyDictionary {
    public function get () {
        return array(
            'entrance-type' => array(
                'name' => 'entrance-type',
                'title' => 'Вход в помещение',
                'multi' => false,
                'type' => \system\types\model\Dictionary::SELECT_BOX,
                'href' => 'https://yandex.ru/support/realty/requirements/requirements-commercial.html#non-residential',
                'variants' => array(
                    'common' => 'common',
                    'separate' => 'separate',
                ),
            ),
            'renovation' => array(
                'name' => 'renovation',
                'title' => 'Ремонт (отделка)',
                'multi' => false,
                'type' => \system\types\model\Dictionary::SELECT_BOX,
                'href' => 'https://yandex.ru/support/realty/requirements/requirements-commercial.html#concept6',
                'variants' => array(
                    'дизайнерский' => 'дизайнерский',
                    'евро' => 'евро',
                    'с отделкой' => 'с отделкой',
                    'требует ремонта' => 'требует ремонта',
                    'хороший' => 'хороший',
                    'частичный ремонт' => 'частичный ремонт',
                    'черновая отделка' => 'черновая отделка',
                ),
            ),
            'agent-fee' => array(
                'name' => 'agent-fee',
                'title' => 'Комиссия агента',
                'multi' => false,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => 'https://yandex.ru/support/realty/requirements/requirements-sale-housing.html#conditions',
            ),
            'prepayment' => array(
                'name' => 'prepayment',
                'title' => 'Предоплата',
                'multi' => false,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => 'https://yandex.ru/support/realty/requirements/requirements-sale-housing.html#conditions',
            ),
            'sitebill_id' => array(
                'name' => 'sitebill_id',
                'title' => 'ID из системы Sitebill',
                'multi' => false,
                'extended' => true,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => '',
            ),
            'client_id' => array(
                'name' => 'client_id',
                'title' => 'Клиент',
                'multi' => false,
                'extended' => true,
                'type' => \system\types\model\Dictionary::CLIENT_ID,
                'href' => '',
            ),
            'owner_phone' => array(
                'name' => 'owner_phone',
                'title' => 'Телефон собственника',
                'multi' => false,
                'extended' => true,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => '',
            ),
            'owner_fio' => array(
                'name' => 'owner_fio',
                'title' => 'Имя собственника',
                'multi' => false,
                'extended' => true,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => '',
            ),
            'active' => array(
                'name' => 'active',
                'title' => 'Active',
                'multi' => false,
                'extended' => true,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => '',
            ),
            'lot-cadastral-number' => array(
                'name' => 'lot-cadastral-number',
                'title' => 'Кадастровый номер участка',
                'multi' => false,
                'extended' => false,
                'type' => \system\types\model\Dictionary::SAFE_STRING,
                'href' => 'https://help.domclick.ru/%D0%B0%D0%B3%D0%B5%D0%BD%D1%82%D1%81%D1%82%D0%B2%D0%B0%D0%BC-%D0%B7%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D1%89%D0%B8%D0%BA%D0%B0%D0%BC-%D0%B8-%D0%BE%D1%86%D0%B5%D0%BD%D0%BE%D1%87%D0%BD%D1%8B%D0%BC-%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC/%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%89%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BC%D0%BA%D0%BB%D0%B8%D0%BA/%D1%82%D0%B5%D1%85%D0%BD%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B5-%D1%82%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F-%D0%BA-xml-%D1%84%D0%B8%D0%B4%D0%B0%D0%BC/',
            ),
        );
    }
}
