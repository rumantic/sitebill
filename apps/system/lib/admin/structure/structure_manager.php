<?php

/**
 * Structure manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

require_once __DIR__ . '/traits/StructureCrudTrait.php';
require_once __DIR__ . '/traits/StructureCategoryTreeTrait.php';
require_once __DIR__ . '/traits/StructureCategorySelectTrait.php';
require_once __DIR__ . '/traits/StructureVehicleTrait.php';
require_once __DIR__ . '/traits/StructureGridTrait.php';
require_once __DIR__ . '/traits/StructureLinkerTrait.php';
require_once __DIR__ . '/traits/StructureServiceTypeTrait.php';

class Structure_Manager extends SiteBill_Krascap
{
    use StructureCrudTrait;
    use StructureCategoryTreeTrait;
    use StructureCategorySelectTrait;
    use StructureVehicleTrait;
    use StructureGridTrait;
    use StructureLinkerTrait;
    use StructureServiceTypeTrait;

    private static $_category_structure = NULL;
    private static $_category_structure_published = NULL;
    private static $_category_urls = NULL;
    private $context_object = NULL;
    private $j = 0;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/version/version.php';
        $version = new Version();
        if (!$version->get_version_value('topic.url')) {
            $this->add_topic_url();
            $version->set_version_value('topic.url', 1);
        }
        $this->action = 'structure';
        $this->table_name = 'topic';
        $this->primary_key = 'id';
        $this->app_title = Multilanguage::_('L_ADMIN_MENU_STRUCTURE');
    }
    
    function add_topic_url()
    {
        $query = "alter table " . DB_PREFIX . "_topic add column url text";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
    }

    function get_content_drop_menu()
    {
        $ra = array();
        $DBC = DBC::getInstance();

        $query = "SELECT ms.*, m.tag, m.name as menu_title FROM " . DB_PREFIX . "_menu m, " . DB_PREFIX . "_menu_structure ms WHERE m.menu_id=ms.menu_id and m.tag like '%drop_menu%' ORDER BY ms.sort_order";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar['tag']][] = $ar;
            }
        }
        return $ra;
    }

    function upgrade()
    {
        $DBC = DBC::getInstance();

        $query = "alter table " . DB_PREFIX . "_topic add column meta_title text";
        $stmt = $DBC->query($query);

        $query = "alter table " . DB_PREFIX . "_topic add column meta_keywords text";
        $stmt = $DBC->query($query);

        $query = "alter table " . DB_PREFIX . "_topic add column meta_description text";
        $stmt = $DBC->query($query);
    }

    function list2Tree($list)
    {
        $r = array();
        $childs = array();
        $items = array();
        $currentDepth = 0;

        foreach ($list as $line) {
            $name = rtrim($line);
            $lDepth = strlen($line) - strlen(ltrim($name, " "));
            $name = trim($name);
            if ($lDepth == 0) {
                $items[0][] = $name;
            }
            echo $name . ' ' . $lDepth . '==';

        }
        print_r($items);
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {
        //return 'В разработке';
        $do = $this->getRequestValue('do');
        switch ($do) {
            case 'import': {
                return $this->_importAction();
            }
            case 'export': {
                return $this->_exportAction();
            }
            case 'loadlist':
            {
                if ('post' == strtolower($_SERVER['REQUEST_METHOD'])) {
                    $catlist = explode(PHP_EOL, trim($_POST['catlist']));
                    print_r($catlist);
                    $this->list2Tree($catlist);
                    $rs .= '<textarea name="catlist" rows="30">222</textarea>';
                } else {

                }
                $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . '/admin' . self::$_trslashes . '">'
                    . '<textarea name="catlist" rows="30">'
                    . '</textarea>'
                    . '<input type="hidden" name="action" value="structure">'
                    . '<input type="hidden" name="do" value="loadlist">'
                    . '<input type="submit">'
                    . '</form>';
                break;
            }
            case 'delete':
            {
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                $category_structure = $this->loadCategoryStructure();
                if (count($category_structure['childs'][$this->getRequestValue('id')]) > 0) {
                    $rs = Multilanguage::_('CATEGORY_HAS_CHILDS', 'system') . '<br>';
                    $rs .= '<a href="?action=structure">' . Multilanguage::_('BACK_TO_LIST', 'system') . '</a>';
                    return $rs;
                }

                $c = 0;
                $query = 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE topic_id=' . (int)$this->getRequestValue('id');
                $DBC = DBC::getInstance();
                $stmt = $DBC->query($query);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $c = $ar['rs'];
                }
                if ($c != 0) {
                    $rs = Multilanguage::_('NOT_EMPTY_CATEGORY', 'system') . '<br>';
                    $rs .= '<a href="?action=structure">' . Multilanguage::_('BACK_TO_LIST', 'system') . '</a>';
                    return $rs;
                }
                $this->deleteRecord($this->getRequestValue('id'));
                $rs = $this->getTopMenu();
                $rs .= $this->grid();
                break;
            }
            case 'edit':
            {
                $rs = '';

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();
                
                $model_itited = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                if ($model_itited) {
                    if (1 == $this->getConfigValue('apps.language.autotrans_enable')) {
                        $model_itited = $data_model->init_model_data_auto_translate($model_itited);
                    }

                    $rs = $this->get_form($model_itited, 'edit');
                } else {
                    $rs = '';
                }

                break;
            }
            case 'new': {
                $rs = '';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();
                $form_data[$this->table_name]['parent_id']['value'] = (int)$this->getRequestValue('parent_id');
                $rs = $this->get_form($form_data[$this->table_name]);
                break;
            }
            case 'linker':
            {
                //Структура таблицы для хранения линков
                /**
                  CREATE TABLE IF NOT EXISTS `re_topic_links` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `topic_id` int(11) NOT NULL,
                  `link_topic_id` int(11) NOT NULL,
                  `params` text,
                  PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                 * */
                //$rs = $this->getTopMenu();
                if (isset($_POST['submit'])) {

                    //echo '<pre>';
                    //print_r($_POST);
                    $this->saveLinkerAssociations($_POST['data']);
                    $rs .= $this->getCategoryTreeLinker(0);
                    //print_r($_POST);
                } else {
                    $rs .= $this->getCategoryTreeLinker(0);
                }

                break;
            }

            /* case 'chains':
              $this->loadCategoriesUrls();

              break; */

            case 'new_done':
            {
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

                if (isset($form_data[$this->table_name]['url']) && $form_data[$this->table_name]['url']['value'] == '') {
                    $form_data[$this->table_name]['url']['value'] = $this->transliteMe($form_data[$this->table_name]['name']['value']);
                    $this->setRequestValue('url', $this->transliteMe($form_data[$this->table_name]['name']['value']));
                }


                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                $OM = new Object_Manager();

                if (!$OM->check_data($form_data[$this->table_name]) || !$this->checkData()) {
                    if ($OM->GetErrorMessage() != '') {
                        $this->riseError($this->GetErrorMessage() . ' ' . $OM->GetErrorMessage());
                    }
                    $rs = $this->get_form($form_data[$this->table_name], 'new');
                } else {
                    $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                    if ($this->getError()) {
                        $rs = $this->get_form($form_data[$this->table_name], 'new');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                break;
            }
            case 'edit_done':
            {
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                $rs = '';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

                if (isset($form_data[$this->table_name]['url']) && $form_data[$this->table_name]['url']['value'] == '') {
                    $form_data[$this->table_name]['url']['value'] = $this->transliteMe($form_data[$this->table_name]['name']['value']);
                    $this->setRequestValue('url', $this->transliteMe($form_data[$this->table_name]['name']['value']));
                }




                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                $OM = new Object_Manager();

                if (!$OM->check_data($form_data[$this->table_name]) || !$this->checkData()) {
                    if ($OM->GetErrorMessage() != '') {
                        $this->riseError($this->GetErrorMessage() . ' ' . $OM->GetErrorMessage());
                    }
                    $rs = $this->get_form($form_data[$this->table_name], 'edit');
                } else {
                    $this->edit_data($form_data[$this->table_name]);
                    if ($this->getError()) {
                        $rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                break;
            }
            case 'reorder_topics':
            {
                $orderArray = $this->getRequestValue('order');
                $this->reorderTopics($orderArray);
                $rs = $this->getTopMenu();
                $rs .= $this->grid();
                break; 
            }
            default:
            {
                $rs .= $this->grid();
            }
        }

        $rs = $this->get_app_title_bar() . $this->getTopMenu() . $rs;
        return $rs;
    }

    function get_app_title_bar()
    {
        $breadcrumbs = array();
        $breadcrumbs[] = array('href' => '#', 'title' => Multilanguage::_('L_ADMIN_MENU_APPLICATIONS'));

        if (!empty($this->app_title)) {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->app_title);
        } else {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->action);
        }
        $this->template->assign('breadcrumbs_array', $breadcrumbs);
        return '';
    }

    // --- Methods moved to StructureCrudTrait: deleteRecord, deleteTopicItem, get_form, getForm, load, checkData ---

    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu()
    {



        $rs = '<a href="?action=structure&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        if ($this->getConfigValue('use_topic_linker')) {
            $rs .= '<a href="?action=structure&do=linker" class="btn btn-primary">' . Multilanguage::_('L_TOPIC_LINKER') . '</a> ';
        }
        //$rs .= '<a href="?action=structure&do=loadlist" class="btn btn-primary">Загрузить списком</a> ';
        //$rs = '<a href="?action=structure" class="btn btn-primary">'.Multilanguage::_('TOPIC_LIST','system').'</a>';
        //$rs .= '<a href="?action=structure&do=chains" class="btn btn-primary">Структурные цепочки</a>';
        /* $rs .= '
          <div class="navbar">
          <div class="navbar-inner">
          <div class="container">

          <a href="?action=structure&do=new" class="btn btn-primary">'.Multilanguage::_('ADD_TOPIC','system').'</a>
          </div>
          </div>
          </div>
          <p>Вид менеджера: '.((isset($_SESSION['structure_manager_grid_type']) && $_SESSION['structure_manager_grid_type']=='new') ? 'Новый <a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=old">Старый</a>' : '<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=new">Новый</a> Старый').'</p>
          '; */


        return $rs;
    }

    // --- Methods moved to StructureCategoryTreeTrait + StructureGridTrait ---
    // loadCategoriesUrls, createCategoriesUrls, appendParent, createCatalogChains, findParent,
    // convertToNestedArray, loadCategoryStructure, get_all_childs, set_context, get_context,
    // load_data_structure, load_data_structure_shop, load_data_structure_price

    // --- Extracted to traits: StructureCategoryTreeTrait, StructureGridTrait, ---
    // --- StructureCategorySelectTrait, StructureVehicleTrait ---

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid()
    {
        //return $this->getCategoryTree(0);
        return $this->getCategoryTreeModern(0);
    }

    // --- Extracted to traits: StructureLinkerTrait, StructureServiceTypeTrait ---

    protected function getStrModelFromDB()
    {
        $form_data = array();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model('topic', false);
        if (empty($form_data)) {
            $form_data = array();
        }
        return $form_data;
    }

    public function get_model()
    {
        return $this->getStrModel();
    }

    protected function getStrModel()
    {

        $form_data = $this->getStrModelFromDB();

        if (!empty($form_data)) {
            return $form_data;
        }


        $use_langs = false;
        if (1 == intval($this->getConfigValue('apps.language.use_langs'))) {
            $use_langs = true;
            $languages = Multilanguage::foreignLanguages();
        }


        $form_data['topic']['id']['name'] = 'id';
        $form_data['topic']['id']['title'] = Multilanguage::_('L_ID');
        $form_data['topic']['id']['value'] = 0;
        $form_data['topic']['id']['length'] = 40;
        $form_data['topic']['id']['type'] = 'primary_key';
        $form_data['topic']['id']['required'] = 'off';
        $form_data['topic']['id']['unique'] = 'off';

        $form_data['topic']['parent_id']['name'] = 'parent_id';
        $form_data['topic']['parent_id']['title'] = 'Родительская категория';
        $form_data['topic']['parent_id']['value_string'] = '';
        $form_data['topic']['parent_id']['value'] = 0;
        $form_data['topic']['parent_id']['length'] = 40;
        $form_data['topic']['parent_id']['type'] = 'select_box_structure';
        $form_data['topic']['parent_id']['required'] = 'off';
        $form_data['topic']['parent_id']['unique'] = 'off';

        $form_data['topic']['order']['name'] = 'order';
        $form_data['topic']['order']['title'] = 'Порядок сортировки';
        $form_data['topic']['order']['value'] = '';
        $form_data['topic']['order']['length'] = 40;
        $form_data['topic']['order']['type'] = 'safe_string';
        $form_data['topic']['order']['required'] = 'off';
        $form_data['topic']['order']['unique'] = 'off';

        if ($this->getConfigValue('use_topic_publish_status')) {
            $form_data['topic']['published']['name'] = 'published';
            $form_data['topic']['published']['title'] = 'Раздел активен';
            $form_data['topic']['published']['value'] = 1;
            $form_data['topic']['published']['length'] = 40;
            $form_data['topic']['published']['type'] = 'checkbox';
            $form_data['topic']['published']['required'] = 'off';
            $form_data['topic']['published']['unique'] = 'off';
        }

        $form_data['topic']['name']['name'] = 'name';
        $form_data['topic']['name']['title'] = 'Название';
        $form_data['topic']['name']['value'] = '';
        $form_data['topic']['name']['length'] = 40;
        $form_data['topic']['name']['type'] = 'safe_string';
        $form_data['topic']['name']['required'] = 'on';
        $form_data['topic']['name']['unique'] = 'off';


        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {

                $form_data['topic']['name_' . $language_id]['name'] = 'name_' . $language_id;
                $form_data['topic']['name_' . $language_id]['title'] = Multilanguage::_('TOPIC_NAME', 'system') . ' <b>' . $language_id . '</b>';
                $form_data['topic']['name_' . $language_id]['value'] = '';
                $form_data['topic']['name_' . $language_id]['length'] = 40;
                $form_data['topic']['name_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['name_' . $language_id]['required'] = 'off';
                $form_data['topic']['name_' . $language_id]['unique'] = 'off';
            }
        }


        $form_data['topic']['meta_title']['name'] = 'meta_title';
        $form_data['topic']['meta_title']['title'] = 'meta_title';
        $form_data['topic']['meta_title']['value'] = '';
        $form_data['topic']['meta_title']['length'] = 40;
        $form_data['topic']['meta_title']['type'] = 'safe_string';
        $form_data['topic']['meta_title']['required'] = 'off';
        $form_data['topic']['meta_title']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_title_' . $language_id]['name'] = 'meta_title_' . $language_id;
                $form_data['topic']['meta_title_' . $language_id]['title'] = 'meta_title' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_title_' . $language_id]['value'] = '';
                $form_data['topic']['meta_title_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_title_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_title_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_title_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['public_title']['name'] = 'public_title';
        $form_data['topic']['public_title']['title'] = 'public_title';
        $form_data['topic']['public_title']['value'] = '';
        $form_data['topic']['public_title']['length'] = 40;
        $form_data['topic']['public_title']['type'] = 'safe_string';
        $form_data['topic']['public_title']['required'] = 'off';
        $form_data['topic']['public_title']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['public_title_' . $language_id]['name'] = 'public_title_' . $language_id;
                $form_data['topic']['public_title_' . $language_id]['title'] = 'public_title' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['public_title_' . $language_id]['value'] = '';
                $form_data['topic']['public_title_' . $language_id]['length'] = 40;
                $form_data['topic']['public_title_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['public_title_' . $language_id]['required'] = 'off';
                $form_data['topic']['public_title_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['meta_keywords']['name'] = 'meta_keywords';
        $form_data['topic']['meta_keywords']['title'] = 'meta_keywords';
        $form_data['topic']['meta_keywords']['value'] = '';
        $form_data['topic']['meta_keywords']['length'] = 40;
        $form_data['topic']['meta_keywords']['type'] = 'safe_string';
        $form_data['topic']['meta_keywords']['required'] = 'off';
        $form_data['topic']['meta_keywords']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_keywords_' . $language_id]['name'] = 'meta_keywords_' . $language_id;
                $form_data['topic']['meta_keywords_' . $language_id]['title'] = 'meta_keywords' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_keywords_' . $language_id]['value'] = '';
                $form_data['topic']['meta_keywords_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_keywords_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_keywords_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_keywords_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['meta_description']['name'] = 'meta_description';
        $form_data['topic']['meta_description']['title'] = 'meta_description';
        $form_data['topic']['meta_description']['value'] = '';
        $form_data['topic']['meta_description']['length'] = 40;
        $form_data['topic']['meta_description']['type'] = 'safe_string';
        $form_data['topic']['meta_description']['required'] = 'off';
        $form_data['topic']['meta_description']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_description_' . $language_id]['name'] = 'meta_description_' . $language_id;
                $form_data['topic']['meta_description_' . $language_id]['title'] = 'meta_description' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_description_' . $language_id]['value'] = '';
                $form_data['topic']['meta_description_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_description_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_description_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_description_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['description']['name'] = 'description';
        $form_data['topic']['description']['title'] = 'Описание';
        $form_data['topic']['description']['value'] = '';
        $form_data['topic']['description']['length'] = 40;
        $form_data['topic']['description']['type'] = 'textarea_editor';
        $form_data['topic']['description']['required'] = 'off';
        $form_data['topic']['description']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['description_' . $language_id]['name'] = 'description_' . $language_id;
                $form_data['topic']['description_' . $language_id]['title'] = 'Описание' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['description_' . $language_id]['value'] = '';
                $form_data['topic']['description_' . $language_id]['length'] = 40;
                $form_data['topic']['description_' . $language_id]['type'] = 'textarea_editor';
                $form_data['topic']['description_' . $language_id]['required'] = 'off';
                $form_data['topic']['description_' . $language_id]['unique'] = 'off';
            }
        }



        $form_data['topic']['url']['name'] = 'url';
        $form_data['topic']['url']['title'] = 'ЧПУ, название раздела латинскими буквами, например, arenda. Без точек и без /';
        $form_data['topic']['url']['value'] = '';
        $form_data['topic']['url']['length'] = 40;
        $form_data['topic']['url']['type'] = 'safe_string';
        $form_data['topic']['url']['required'] = 'on';
        $form_data['topic']['url']['unique'] = 'off';

        if ($this->getConfigValue('use_topic_actual_days')) {
            $form_data['topic']['actual_days']['name'] = 'actual_days';
            $form_data['topic']['actual_days']['title'] = 'Актуальность (кол.во дней до подсветки)';
            $form_data['topic']['actual_days']['value'] = '';
            $form_data['topic']['actual_days']['length'] = 40;
            $form_data['topic']['actual_days']['type'] = 'safe_string';
            $form_data['topic']['actual_days']['required'] = 'off';
            $form_data['topic']['actual_days']['unique'] = 'off';
        }


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
        $TA = new table_admin();
        $TA->create_table_and_columns($form_data, 'topic');

        $ATH->create_table('topic');
        $ATH->update_table('topic');


        return $this->getStrModelFromDB();
    }

    /**
     * TODO move to sitebill
     * @param type $n
     * @return string
     */
    function num2alpha($n) {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n % 26 + 0x41) . $r;
        return $r;
    }

    protected function _importAction() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/dropzone_xls/dropzone.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $dropzone = new DropZone();
        $dropzone->set_context($this);
        $this->template->assign('uploads_item', $dropzone->compile_uploads_element($item_array));
        //$this->template->assign('dropzone', $dropzone->getDropzonePlugin($this->get_session_key()));

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/object/import_form.tpl');
    }

    protected function _exportAction($input_params = array()) {

        $form_data = array();

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');

        $data_model = new Data_Model();
        $ATH = new Admin_Table_Helper();
        $_model = $ATH->load_model($this->table_name, false);
        $_model = $_model[$this->table_name];

        $DBC = DBC::getInstance();

        $ids = array();

        $query = 'SELECT `'.$this->primary_key.'` FROM '.DB_PREFIX.'_'. $this->table_name;

        $stmt = $DBC->query($query);
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $ids[] = $ar[$this->primary_key];
            }
        }

        $items = $data_model->init_model_data_from_db_multi($this->table_name, $this->primary_key, $ids, $_model, true);


        $exported_fields = array_keys($_model);

        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'bottom' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '808080'
                    )
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'rotation' => 90,
                'color' => array(
                    'rgb' => 'c5c5c5',
                )
            ),
        );

        $last_letter = $this->num2alpha(@count($exported_fields) - 1);

        try {
            $objPHPExcel->getActiveSheet()->getStyle('A1:' . $last_letter . '1')->applyFromArray($styleArray);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }


        $column = 1;

        foreach ($exported_fields as $ef) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, SiteBill::iconv(SITE_ENCODING, 'utf-8', $_model[$ef]['title']));

            $objPHPExcel->getActiveSheet()->getColumnDimension($this->num2alpha($column))->setAutoSize(true);

            $column++;
        }
        $column = 1;
        $itemscount = 0;
        foreach ($items as $item_id => $data_item_a) {
            $row = $itemscount + 2;
            $itemscount += 1;
            $column = 1;
            foreach ($data_item_a as $key => $value) {
                if (is_array($value)) {
                    if ( $data_item_a[$key]['type'] == 'select_by_query_multi' && is_array($value['value_string']) ) {
                        $value = implode(',',$value['value_string']);
                    } else {
                        $value = $value['value_string'];
                    }
                }
                if ( !empty($value) and !is_scalar($value) ) {
                    $value = 'array!';
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $value));
                $column++;
            }
        }
        $current_page = 1;
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
        $xlsx_file_name = $this->action . "_page" . $current_page . ".xlsx";
        $xlsx_output_file = SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $xlsx_file_name;
        $objWriter->save($xlsx_output_file);

        $handle = fopen($xlsx_output_file, "r");
        $contents = fread($handle, filesize($xlsx_output_file));
        fclose($handle);
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=" . $xlsx_file_name . "");
        echo $contents;
        exit;
    }

}
