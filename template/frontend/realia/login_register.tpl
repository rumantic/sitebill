<div class="modal fade" id="prettyRegisterOk" tabindex="-1" role="dialog" aria-labelledby="prettyRegisterOk" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel">{$L_AUTH_REGISTER_COMPLETE}</h3>
    </div>
    <div class="modal-body">
        {$L_AUTH_REGISTER_COMPLETE}
    </div>
    <div class="modal-footer">
        <button class="btn let_me_login">{$L_LOGIN_BUTTON}</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
    </div>
</div>

<div class="modal fade" id="prettyLogin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel">{$L_AUTH_WELLCOME}</h3>
    </div>
    <div class="modal-body">
        <ul class="nav nav-tabs">
            {if $allow_register_account==1}<li><a href="#register" data-toggle="tab">{$L_AUTH_REGISTRATION}</a></li>{/if}
            <li class="active"><a href="#profile" data-toggle="tab">{$L_AUTH_TITLE}</a></li>
        </ul>
        <div class="tab-content">
            {if $allow_register_account==1}
                <div class="tab-pane" id="register">
                    <div id="register_1">
                        <form action="#" class="form-horizontal">

                            <div class="row error">

                            </div>
                            {foreach from=$register_form_elements item=elt}
                                <div class="control-group el">
                                    <label class="control-label">{$elt.title}{if $elt.required==1} <span class="required">*</span>{/if}</label>
                                    <div class="controls">
                                        {$elt.html} <a class="btn btn-danger error_mark"><i class="icon-exclamation-sign icon-white"></i></a>
                                    </div>
                                </div>

                            {/foreach}




                            <div class="row">
                                <input type="submit" id="register_button" class="btn btn-primary" value="{$L_AUTH_REGISTRATION}" />
                            </div>
                        </form>
                    </div>
                    <div id="confirm_sms_code_block" style="display: none;">
                        <form action="#" class="form-horizontal">
                            <div class="row error">

                            </div>
                            <div class="control-group el"  >
                                <label class="control-label">{_e t="Введите SMS код"}</label>
                                <div class="controls">
                                    <input type="text" name="sms_code">
                                </div>
                            </div>
                            <div class="row">
                                <input type="submit" id="confirm_code" class="btn btn-primary" value="{_e t="Подтвердить"}" />
                            </div>
                        </form>    
                    </div>     
                </div>
            {/if}
            <div class="tab-pane active" id="profile">
                <form action="#" class="form-horizontal">
                    <div class="row">
                        <h3>{$L_HAVE_LOGIN_YET}</h3>
                        <p>{$L_AUTH_PLEASE}</p>
                    </div>
                    {if isset($vk_url) && $vk_url!=''}
                        <div class="row">            

                        </div>
                    {/if}
                    <div class="row error">
                        {$L_AUTH_WRONG_LOGIN_PASSWORD}
                    </div>
                    <div class="control-group">
                        <label class="control-label">{$L_LOGIN} <span class="required">*</span></label>
                        <div class="controls">
                            <input name="login" type="text" value="" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">{$L_PASSWORD} <span class="required">*</span></label>
                        <div class="controls">
                            <input name="password" type="password" value="" />
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input type="checkbox" name="rememberme"> {$L_AUTH_REMEMBERME}
                            </label>
                        </div>
                    </div>


                    <div class="control-group">
                        <div class="controls">
                            <input type="submit" id="login_button" class="btn btn-primary" value="{$L_LOGIN_BUTTON}" />
                            {if $vk_url != ''} 
                                <a href="{$vk_url}" class="btn btn-info" ><img src="{$estate_folder}/apps/socialauth/img/vk.png" border="0"/> {$L_AUTH_VKONTAKTE}</a>
                                {/if} 

                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <a href="{$estate_folder}/remind/">{$L_AUTH_FORGOT_PASS}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">{$L_CLOSE}</button>
    </div>
</div>

