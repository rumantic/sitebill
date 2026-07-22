<?php
/**
 * ViewDataTrait — Data loading, view stats, and auto-output for Kvartira_View.
 *
 * Manages: init, setLastViewed, isAccessibleObject, addMessageToOwner,
 *          collectViewStat, load_form_data_shared, makeUserOperatios,
 *          getPublicAutoOutputData, getAutoOutputData.
 */
trait ViewDataTrait
{
    function init($realty_id)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $realty_id, $form_data_shared['data'], true);
        $form_data_shared = $data_model->init_language_values($form_data_shared, $form_data_shared);
    }

    function setLastViewed($realty_id)
    {
        $length = 10;
        if (!isset($_COOKIE['last_viewed_data'])) {
            $last_viewed = array();
        } else {
            $last_viewed = json_decode($_COOKIE['last_viewed_data'], true);
        }
        if (count($last_viewed) >= $length) {
            array_shift($last_viewed);
        }
        if (!in_array($realty_id, $last_viewed)) {
            $last_viewed[] = $realty_id;
        }
        @setcookie('last_viewed_data', json_encode($last_viewed), time() + 60 * 60 * 24 * 30, SITEBILL_MAIN_URL . '/', self::$_cookiedomain);
    }

    function isAccessibleObject($data_shared)
    {
        return true;
    }

    function addMessageToOwner()
    {

    }

    function collectViewStat()
    {
        $get_stat = false;
        if (1 == $this->getConfigValue('apps.statoid.enable')) {
            $get_stat = true;
        }

        $user_id = intval($_SESSION['user_id']);
        if ($user_id == $this->realty['user_id']['value']) {
            $get_stat = false;
        }

        if ($get_stat) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php';
            $ST = new statoid_admin();
            $ST->collectEventStat('view', 'data', 'id', $this->realty['id']['value']);
        }
    }

    function load_form_data_shared ($realty_id)
    {

        $result = false;


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');

        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        $user_object_manager = new User_Object_Manager();

        $data_model = new Data_Model();


        //load Data model with rules
        $form_data = $data_model->get_kvartira_model(false, false);

        //load Data model full without rules
        $form_data_shared = $data_model->get_kvartira_model(false, true);

        //load User model with rules
        $form_user = $user_object_manager->get_user_model(true);

        //init Data model with rules
        $form_data = $data_model->init_model_data_from_db('data', 'id', $realty_id, $form_data['data'], true);


        $topic_id = 0;
        if (isset($form_data['topic_id'])) {
            $topic_id = intval($form_data['topic_id']['value']);
            $this->topic_id = $topic_id;
        }

        if (isset($form_data['city_id']) && intval($form_data['city_id']['value']) > 0) {
            $this->city_id = $form_data['city_id']['value'];
        }

        //init Data model full without rules
        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $realty_id, $form_data_shared['data'], true);
        return $form_data_shared;
    }

    protected function makeUserOperatios($form_data_shared)
    {
        /* $user_identity=md5($_SERVER['HTTP_USER_AGENT'].'_'.$_SERVER['REMOTE_ADDR']);
          $realty_id=(int)$form_data_shared['id']['value'];

          $DBC=DBC::getInstance();
          $query='SELECT COUNT(*) AS _cnt FROM '.DB_PREFIX.'_likevoter WHERE user_identity=? AND realty_id=?';
          $DBC=DBC::getInstance();
          $stmt=$DBC->query($query, array($user_identity, $realty_id));

          $likevoter=array();
          $likevoter['yes']=0;
          $likevoter['no']=0;
          $likevoter['allow']=0;


          if($stmt){
          $ar=$DBC->fetch($stmt);
          if($ar['_cnt']==0){
          $likevoter['allow']=1;
          }else{
          $likevoter['allow']=0;
          }
          }

          $query='SELECT COUNT(*) AS _cnt, resultcode FROM '.DB_PREFIX.'_likevoter WHERE realty_id=? GROUP BY resultcode';
          $stmt=$DBC->query($query, array($realty_id));
          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          //print_r($ar);
          if($ar['resultcode']==1){
          $likevoter['yes']=(int)$ar['_cnt'];
          }elseif($ar['resultcode']==0){
          $likevoter['no']=(int)$ar['_cnt'];
          }
          }
          }
          $this->template->assign('likevoter', $likevoter); */
    }

    public function getPublicAutoOutputData($form_data)
    {
        return $this->getAutoOutputData($form_data);
    }

    protected function getAutoOutputData($form_data)
    {
        $hvd_tabbed = array();
        foreach ($form_data as $hvd) {
            if ($hvd['tab'] == '') {
                $hvd_tabbed[$this->getConfigValue('default_tab_name')][] = $hvd;
            } else {
                $hvd_tabbed[$hvd['tab']][] = $hvd;
            }
        }
        return $hvd_tabbed;
    }
}
