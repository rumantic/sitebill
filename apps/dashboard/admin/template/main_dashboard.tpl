<link rel="stylesheet" href="{$estate_folder}/apps/dashboard/bootstrap/css/bootstrap.min.css">
<script src="{$estate_folder}/apps/dashboard/bootstrap/js/bootstrap.min.js"></script>

<script type="text/javascript">
    var estate_folder = '{$estate_folder}';
</script>
<script type="text/javascript" src="{$estate_folder}/apps/system/js/jquery/jquery.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/dashboard/js/dashboard.js"></script>
<script type="text/javascript" src="{$estate_folder}/apps/dashboard/js/editor.js"></script>
<link rel="stylesheet" href="{$estate_folder}/apps/dashboard/css/style.css" type="text/css">

<div id="config_form">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">



                <div class="form-inline">
                    <div class="form-group mb-2">
                        <label>Тема оформления</label>
                    </div>
                    <div class="form-group mx-sm-3 mb-2">
                        {$theme_select}
                    </div>
                    <p></p>
                    <input type="submit" name="save" id="save" value="Сохранить" style="margin-top: -8px;" class="btn btn-success">    
                </div>                
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <p><a href="https://www.sitebill.ru/s/" target="_blank">Форум тех.поддержки</a></p>
                <p><a href="https://www.youtube.com/user/DMn1c" target="_blank">Видео-уроки</a></p>
                <p><a href="http://wiki.sitebill.ru/" target="_blank">Техническая документация</a></p>
                <p>Телефон тех.поддержки: <a href="tel:88002509931">8 800 250-99-31</a> или пишите в любой мессенджер <a href="tel:+79138317494">+79138317494</a></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <p style="float: left;"><img src="https://www.sitebill.ru/storage/lessons/start1.gif" width="600" height="286" class="img-fluid"/></p>
            </div>
            <p></p>
            <div class="col-md-6">
                <div class="alert alert-danger">Выключить режим редактирования можно в <a href="/admin/?action=config" target="_parent">панели управления</a> в пункте Настройки - Помощник</div>    
            </div>
        </div>
                    
        <div class="row">
            <div class="col-md-12">
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
            </div>
        </div>
                    
    </div>
</div>

