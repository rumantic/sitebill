<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Created by PhpStorm.
 * User: unclead.nsk
 * Date: 06.04.14
 * Time: 15:17
 */

require_once 'DataSourceInterface.php';

/**
 * Class ApiDataSource
 *
 * http://{URL}/{TOKEN}/{COUNT}/{OFFSET}
 * {TOKEN} – индивидуальный ключ кандидата
 * {COUNT} – количество текстов для выдачи (необязательный параметр, значение по умолчанию = 10). Доступные значения [1;100000]. Для полной выборки используйте значение «-1».
 * {OFFSET} – сдвиг выборки текстов для выдачи (необязательный параметр, значение по умолчанию = 0). Доступные значения [0;99999].
 * Выходные данные метода представлены в формате JSON.
 */
class ApiDataSource implements DataSourceInterface
{

    const REQUEST_GET = 'GET';

    private $token = null;

    private $url;

    private $count = 10;

    private $offset = 0;
    
    private $text = '';

    public function setUrl($url)
    {
    }

    public function getUrl()
    {
        return 'http://' . $this->url;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function getOffset()
    {
        return $this->offset;
    }
    
    public function setText ( $text ) {
        $this->text = $text;
    }
    
    public function getText ( ) {
        return $this->text;
    }
    

    /**
     * Получение данных для проверки с внешнего исчтоника
     * @param bool $use_api
     * @return array|mixed
     * @todo В случае, если размер выборки более 10000 следует использовать цикл со смещением иначе получаем ошибку переполнения памяти
     */
    public function getData($use_api = false)
    {
            $text = $this->getText();

            $data = array();
            for ($i = 0; $i < 10; $i++) {
                $data[$i] = $text;
            }
        return $data;
    }

    /**
     * Отправка запроса
     * @return mixed ответ
     */
    private function sendRequest()
    {

    }
} 