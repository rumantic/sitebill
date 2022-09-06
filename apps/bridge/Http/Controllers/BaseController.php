<?php
namespace bridge\Http\Controllers;

class BaseController
{
    use \system\traits\blade\BladeTrait;

    /**
     * @var \SiteBill
     */
    protected $sitebill;

    protected $frontend = null;

    public function __construct()
    {
        $this->sitebill = new \SiteBill();
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->sitebill->getConfigValue('theme').'/resources/views');
        $this->add_resource_path(SITEBILL_DOCUMENT_ROOT);

        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->sitebill->getConfigValue('theme').'/main/main.php')){
            require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->sitebill->getConfigValue('theme') . '/main/main.php');
            $this->frontend = new \frontend_main();
        }

    }

    function buildLangSwitcher($REQUESTURIPATH = ''){

        $links = array();

        if(1 == intval($this->sitebill->getConfigValue('apps.language.use_langs'))){
            $al = \Multilanguage::availableLanguages();

            $prefix_list = array();
            $prefixlistconf = trim($this->sitebill->getConfigValue('apps.language.language_prefix_list'));
            if ($prefixlistconf !== '') {
                $prefix_pairs = explode('|', $prefixlistconf);
                if (count($prefix_pairs) > 0) {
                    foreach ($prefix_pairs as $lp) {
                        list($pr, $lo) = explode('=', $lp);
                        $prefix_list[$lo] = $pr;
                    }
                }
            }

            $currentlang = $this->sitebill->getCurrentLang();

            foreach ($al as $l){
                $urlp = array();
                if($prefix_list[$l] != ''){
                    $urlp[] = $prefix_list[$l];
                }
                if($REQUESTURIPATH != ''){
                    $urlp[] = $REQUESTURIPATH;
                }
                $urlstr = '';
                if(!empty($urlp)){
                    $urlstr = implode('/', $urlp);
                }

                $links[] = array(
                    'href' => $this->sitebill->createUrlTpl($urlstr, false, true),
                    'name' => mb_strtoupper($l),
                    'current' => ($currentlang == $l ? 1 : 0)
                );

            }
        }

        return $links;
    }

    function return_pageview($view, $params = array()){
        $viewparams = [];
        $viewparams['sitebill'] = $this->sitebill;
        $viewparams['sessiondata'] = $_SESSION;
        $viewparams['tpldata'] = @$this->getCommonTplData();
        $viewparams['config'] = \SConfig::getInstance();
        $viewparams['LangSwitcher'] = $this->buildLangSwitcher($this->sitebill->getClearRequestURI());


        if(!empty($params)){
            $viewparams = array_merge($viewparams, $params);
        }
        return $this->view($view, $viewparams);
    }

    function getCommonTplData(){

        $data = array();

        if(intval($_SESSION['user_id']) == 0){
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
            $Register = new \Register_Using_Model();
            $data['register_form_elements'] = $Register->getRegisterFormElements();
        }

        //@todo: нужно связать этот с apps.contact
        $data['contacts'] = array(
            'phone' => '+123 123 123 123',
            'address' => 'Krasnoyarsk',
            'email' => 'report@etown.ru',
            'skype' => 'skype',
            'whatsapp' => '',
            'telegram' => 'telegram'
        );

        $data['sociallinks'] = array();



        if(!is_null($this->frontend) && method_exists($this->frontend, 'getCommonTplData')){
            $templatedata = $this->frontend->getCommonTplData();
            if(!empty($templatedata)){
                $data = array_merge($data, $templatedata);
            }
        }


        return $data;
    }

}
