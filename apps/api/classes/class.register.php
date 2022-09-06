<?php

use Illuminate\Database\Capsule\Manager as Capsule;

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Register API class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_register extends API_Common
{
    /**
     * @var Permission
     */
    protected $permission;

    /**
     * @var Register_Using_Model
     */
    private $register_manager;

    /**
     * @var sms_admin
     */
    private $sms_admin;

    /**
     * @var User_Object_Manager
     */
    private $user_object_manager;

    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
        $this->permission = new Permission();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php');
            $this->register_manager = new Local_Register_Using_Model();
        } else {
            $this->register_manager = new Register_Using_Model();
        }

        if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php') ) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php';
            $this->sms_admin = new sms_admin();
        }

        $api_common = new \api\aliases\API_common_alias();
        $this->user_object_manager = $api_common->init_custom_model_object('user');


    }

    function error_response ($error_message, $data = array()) {
        $response = new API_Response('error', $error_message, $data);
        return $this->json_string($response->get());
    }

    function activate_user ( $user_id ) {
        $query_result = Capsule::table('user')
            ->where('user_id', '=', $user_id)
            ->update(['active' => 1]);
        if (1 == $this->getConfigValue('notify_admin_about_register')) {
            $this->register_manager->notify_admin_about_register($user_id);
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $Login = new Login();
        $Login->makeUserLogged($user_id, 1);
    }

    function _check_sms_confirm_code () {
        $user_id = $this->request()->get('user_id');
        $confirm_code = $this->request()->get('confirm_code');
        $query_result = Capsule::table('user')
            ->selectRaw(
                'user_id, pass'
            )
            ->where('user_id', '=', $user_id)
            ->where('pass', '=', $confirm_code)
            ->first();
        if ( $query_result->user_id > 0 and $query_result->user_id == $user_id ) {
            $this->activate_user($query_result->user_id);
            $response = new API_Response('success', 'confirm success', $user_id);
        } else {
            $response = new API_Response('error', 'confirm failed', $user_id);
        }
        return $this->json_string($response->get());
    }

    function check_uniq_phone_number ( $number ) {
        $query_result = Capsule::table('user')
            ->selectRaw(
                'user_id'
            )
            ->where('mobile', '=', $number)
            ->first();
        if ( $query_result->user_id > 0 ) {
            return false;
        }
        return true;
    }

    function _register_phone_number () {
        $phone_number = $this->request()->get('phone_number');
        $password = $this->request()->get('password');
        $fio = $this->request()->get('fio');

        if ( $phone_number == '' ) {
            return $this->error_response(_e('Пожалуйста, укажите номер телефона'));
        }
        if ( !$this->check_uniq_phone_number($phone_number) ) {
            return $this->error_response(_e('Такой номер телефона уже зарегистрирован'));
        }

        if ( $password == '' ) {
            return $this->error_response(_e('Пожалуйста, укажите пароль'));
        }

        $user_model = $this->register_manager->data_model;

        $user_model['user']['fio']['value'] = $fio;
        $user_model['user']['login']['value'] = $phone_number;
        $user_model['user']['mobile']['value'] = $phone_number;
        $user_model['user']['email']['value'] = $phone_number.'@'.$this->request()->getHost();
        $user_model['user']['newpass']['value'] = $password;
        $user_model['user']['newpass_retype']['value'] = $password;
        $user_model['user']['reg_date']['value'] = date('Y-m-d H:i:s');


        if (0 != (int)$this->getConfigValue('newuser_registration_groupid')) {
            $user_model['user']['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
        } else {
            $user_model['user']['group_id']['value'] = $this->register_manager->getGroupIdByName('realtor');
        }
        $activation_code = $this->generate_activation_code();
        $user_model['user']['pass']['value'] = $activation_code;

        if ( !$this->register_manager->check_data($user_model['user']) ) {
            return $this->error_response($this->register_manager->getError(), $user_model['user']);
        }

        if ( $this->sms_admin ) {
            $sms_result = $this->sms_admin->send($phone_number, 'Verification code ' . $activation_code);
        } else {
            $sms_result = false;
        }
        if ( !$sms_result ) {
            return $this->error_response(_e('Невозможно отправить SMS-сообщение'));
        }

        $new_user_id = $this->register_manager->add_data($user_model['user']);
        if ( $this->user_object_manager->getError() ) {
            if ( preg_match('/duplicate/i', $this->user_object_manager->getError()) ) {
                $error = _e('Такой номер телефона уже зарегистрирован');
            } else {
                $error = $this->user_object_manager->getError();
            }
            $response = new API_Response('error', $error);
            return $this->json_string($response->get());
        }



        if ($new_user_id > 0) {
            $response = new API_Response('success', 'phone_number_added success', ['user_id' => $new_user_id]);
        } else {
            $response = new API_Response('error', _e('Неизвестная ошибка при регистрации'));
        }

        return $this->json_string($response->get());
    }

    function generate_activation_code () {
        return rand ( 1000 , 9999 );
    }

    function _simple_validate_confirm_code () {
        $phone_number = $this->request()->get('phone_number');
        $code = $this->request()->get('code');
        $exists_code = \system\lib\model\eloquent\Cache::where([
            ['parameter', '=', $phone_number],
            ['value', '=', $code]
        ])->first();
        if ( $exists_code->parameter != $phone_number ) {
            return $this->error_response(_e('Неверный код'));
        }
        \system\lib\model\eloquent\Cache::where([
            ['parameter', '=', $phone_number]
        ])->delete();

        $response = new API_Response('success', 'confirm_success', ['phone_number' => $phone_number]);
        return $this->json_string($response->get());
    }

    function _simple_send_confirm_code () {
        $phone_number = $this->request()->get('phone_number');
        $activation_code = $this->generate_activation_code();
        $cache = new \system\lib\model\eloquent\Cache;
        try {
            \system\lib\model\eloquent\Cache::where([
                ['parameter', '=', $phone_number],
                ['valid_for', '<', time()]
            ])->delete();

            $exists_code = \system\lib\model\eloquent\Cache::where([
                ['parameter', '=', $phone_number]
            ])->first();
            if ( $exists_code->parameter != $phone_number ) {
                $cache->parameter = $phone_number;
                $cache->value =  $activation_code;
                $cache->created_at = time();
                $cache->valid_for = time() + 60; // + 1 minute
                $cache->save();
                if ( $this->sms_admin ) {
                    $sms_result = $this->sms_admin->send($phone_number, 'Verification code ' . $activation_code);
                } else {
                    $sms_result = false;
                }
                if ( !$sms_result ) {
                    return $this->error_response(_e('Невозможно отправить SMS-сообщение'));
                }
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage());
        }
        $response = new API_Response('success', 'send_success', ['sms_send' => ($sms_result?true:false)]);
        return $this->json_string($response->get());
    }
}
