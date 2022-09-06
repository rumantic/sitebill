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

        );
    }
}
