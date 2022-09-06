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
            {if {getConfig key='apps.sms.allow_sms_register'} eq 1}<li><a href="#register_sms" data-toggle="tab">{_e t="Регистрация по SMS"}</a></li>{/if}
            <li class="active"><a href="#profile" data-toggle="tab">{$L_AUTH_TITLE}</a></li>
        </ul>
        <div class="tab-content">
            {if $allow_register_account==1}
                <div class="tab-pane" id="register">
                    <div id="register_1">
                        <form action="#" id="simple_register" class="form-horizontal">

                            {foreach from=$register_form_elements item=elt}
                                <div class="control-group el">
                                    <label class="control-label">{$elt.title}{if $elt.required==1} <span class="required">*</span>{/if}</label>
                                    <div class="controls">
                                        {if $elt.name == 'mobile'}
                                            <input type="tel" id="mobile_phone" name="mobile">
                                            <input type="hidden" id="mobile_phone_with_code" name="mobile_phone_with_code">

                                            <span id="mobile_valid-msg" class="hide-intl">✓ Valid</span>
                                            <span id="mobile_error-msg" class="hide-intl"></span>
                                            <a class="btn btn-danger error_mark"><i class="icon-exclamation-sign icon-white"></i></a>
                                        {else}
                                            {$elt.html} <a class="btn btn-danger error_mark"><i class="icon-exclamation-sign icon-white"></i></a>
                                        {/if}
                                    </div>
                                </div>

                            {/foreach}


                            <div class="row error" style="color: red;">

                            </div>


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
            {if {getConfig key='apps.sms.allow_sms_register'} eq 1}
                <div class="tab-pane" id="register_sms">
                    <div id="register_2">
                        <form action="#" class="form-horizontal">
                            <div class="row error">

                            </div>
                            <step_one id="step_one">
                                <div class="control-group el"  >
                                    <label class="control-label">{_e t="Полное имя"} <span class="required">*</span></label>
                                    <div class="controls">
                                        <input type="text" name="fio">
                                    </div>
                                </div>

                                <div class="control-group el"  >
                                    <label class="control-label">{_e t="Ваш телефон"}<br> {_e t="Только цифры"}: (971...) <span class="required">*</span>
                                    </label>
                                    <div class="controls">
                                        <input type="tel" id="phone" name="phone_number">
                                        <input type="hidden" id="phone_with_code" name="phone_with_code">
                                        <span id="valid-msg" class="hide-intl">✓ Valid</span>
                                        <span id="error-msg" class="hide-intl"></span>
                                    </div>
                                </div>

                                <div class="control-group el"  >
                                    <label class="control-label">{_e t="Пароль"} <span class="required">*</span></label>
                                    <div class="controls">
                                        <input type="password" name="password">
                                    </div>
                                </div>

                                <div class="row">
                                    <input type="button" id="send_sms" class="btn btn-primary" value="{_e t="Получить SMS"}" />
                                </div>
                            </step_one>

                            <step_two id="step_two" style="display: none;">
                                <div class="control-group el"  >
                                    <label class="control-label">{_e t="Код подтверждения"}</label>
                                    <div class="controls">
                                        <input type="text" name="confirm_code">
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" name="user_id" value="0">
                                    <input type="button" id="send_sms_confirm_code" class="btn btn-primary" value="{_e t="Отправить код"}" />
                                </div>
                            </step_two>
                            <step_three id="step_three" style="display: none;">
                                <div class="control-group el"  >
                                    {_e t="Регистрация успешна!"}
                                </div>
                            </step_three>

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
