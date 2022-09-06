<div class="page-header" align="center">
    <h1>Укажите свой лицензионный ключ. Либо получите демо-ключ бесплатно.</h1>
</div>


<form method="post" class="form-horizontal" role="form">

    <div class="form-group">
        <label for="inputError" class="col-xs-12 col-sm-3 col-md-3 control-label no-padding-right"></label>

        <div class="col-sm-12  col-sm-6">
            <a href="http://www.sitebill.ru/client/cart.php?a=add&pid=6" target="_blank" class="btn">демо-ключ
                бесплатно</a>
            <a href="http://www.sitebill.ru/price-cms-sitebill/" target="_blank" class="btn btn-warning pull-right">
                <i class="menu-icon  ace-icon fa fa-key "></i>
                купить лицензионный ключ
            </a>
        </div>
    </div>
    <div class="space-4"></div>

    <?php if ($wizard->get_error_state()) { ?>
        <div class="form-group has-error">
            <label for="inputError" class="col-xs-12 col-sm-3 col-md-3 control-label no-padding-right"></label>
            <div class="help-block col-xs-12 col-sm-6 inline"> <?php echo $wizard->get_error_message(); ?> </div>
        </div>
        <div class="space-4"></div>

    <?php } ?>

    <div class="form-group <?php if ($wizard->get_error_state()) { ?>has-error<?php } ?>">
        <label for="inputError" class="col-xs-12 col-sm-3 col-md-3 control-label no-padding-right"></label>
        <div class="col-xs-12 col-sm-6">
            <span class="block input-icon input-icon-right">
                <input type="text" class="col-xs-12" id="inputError" name="license_key" placeholder="Лицензионный ключ"
                       value="<?php if (isset($_POST['license_key']) and $_POST['license_key'] != '') {
                           echo $_POST['license_key'];
                       } else {
                           echo @$_SESSION['license_key'];
                       }; ?>"/>
<?php if ($wizard->get_error_state()) { ?>
    <i class="ace-icon fa fa-times-circle"></i>
<?php } ?>
            </span>
        </div>
    </div>

    <div class="wizard-actions">

        <input type="hidden" name="step" id="step" value="2"/>
        <button class="btn btn-prev" id="prev">
            <i class="ace-icon fa fa-arrow-left"></i>
            Назад
        </button>

        <button class="btn btn-success btn-next"/>
        Далее <i class="ace-icon fa fa-arrow-right icon-on-right"></i>
        </button>

    </div>


</form>

<p align="center">Если возникли вопросы по установке, то посмотрите это видео</p>
<p align="center">
    <iframe width="560" height="315" src="http://www.youtube.com/embed/lJqq2z6nAJs" frameborder="0"
            allowfullscreen></iframe>
</p>
<img src="https://www.sitebill.ru/logo_install?source=step2" width="1" height="1">


