<?php
namespace system\traits;

use api\aliases\API_common_alias;

trait MessengerTrait
{
    /**
     * @var \Data_Manager
     */
    private $data_manager_messenger;

    function register_messenger_smarty_function () {
        global $smarty;
        if (!isset($smarty->registered_plugins['function']['messenger_hello_text'])) {
            $smarty->registerPlugin('function', 'messenger_hello_text', array(&$this, 'messenger_hello_text'));
        }
    }

    /**
     * You can ovveride this method
     * {messenger_hello_text realty_id="58"}
     * @param $params
     * @return string
     */
    public function messenger_hello_text ($params) {
        if ( !isset($this->data_manager_messenger) ) {
            $api_common = new API_common_alias();
            $this->data_manager_messenger = $api_common->init_custom_model_object('data');
        }
        $data_info = $this->data_manager_messenger->load_by_id($params['realty_id']);
        if ( $data_info['id']['value'] > 0 ) {
            return urlencode('Hello,
I would like to get more information about this property you posted on '.$this->request()->getHost().':            
            ID: '.$data_info['id']['value'].'
Type: Apartment
Price: 90000 AED/year
Location: The Links Golf Apartments

');
        }
        return '';
    }

}
