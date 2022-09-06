<script type="text/javascript" src="{$estate_folder}/apps/system/js/sitebillcore.js"></script>
{literal}
    <style>

        #contact_with_author_window, #send_friend_window {
            background-color: White;
            position:absolute;
            z-index:10000;
            width:315px;
            border: 1px solid Silver;
            display: none;
            border-radius: 5px;
        }

        #contact_with_author_window .closer, #send_friend_window .closer {
            height: 16px;
            width: 16px;
            float: right;
            cursor: pointer;
            background-image: url('{/literal}{$estate_folder}{literal}/img/publish_x.png');
        }

        #contact_with_author_window label {
            display: block;
        }

        #contact_with_author_window div.inner div, #send_friend_window div.inner div {
            margin: 10px;
        }

        #contact_with_author_window div.inner input, #contact_with_author_window div.inner textarea {
            margin: 2px 0;
        }

        #contact_with_author_window div.inner textarea {
            /*width: 290px;*/
        }
        .mailbox-options .row {
            padding: 3px;
        }
        .mailbox-options .row-fluid {
            padding: 3px;
            text-align: center;
        }

        .mailbox-options a {
            width: 74%;
        }
        .required {
            color: red;
        }
    </style>
    <script>
        function hideErrors() {
            $('#contact_with_author_window form #error_block').hide();
            $('#contact_with_author_window form #error_block_nouser').hide();
        }
        $(document).ready(function () {
            $('a#contact_with_author').click(function (e) {
                var dialog = $('#contact_with_author_window');
                dialog.appendTo($('body'));
                var dialog = $('#contact_with_author_window');
                var form = $('#contact_with_author_window form');
                var offset = $(this).offset();
                hideErrors();
                $.ajax({
                    url: estate_folder+'/js/ajax.php',
                    data: '_app=mailbox&action=get_logged_user_data',
                    dataType: 'json',
                    success: function (json) {
                        if (json.res !== 'no_user') {
                            form.find('[name=name]').val(json.fio);
                            form.find('[name=phone]').val(json.phone);
                            form.find('[name=email]').val(json.email);
                        }

                    }
                });
                var pos = SitebillCore.getDialogPositionCoords(dialog.width(), dialog.height());
                dialog.css({'top': pos[1] + 'px', 'left': pos[0] + 'px'});
                dialog.fadeIn();
                return false;
            });

            $('a#send_friend').click(function () {
                var dialog = $('#send_friend_window');
                dialog.appendTo($('body'));
                var dialog = $('#send_friend_window');
                var pos = SitebillCore.getDialogPositionCoords(dialog.width(), dialog.height());
                dialog.css({'top': pos[1] + 'px', 'left': pos[0] + 'px'});
                dialog.fadeIn();
                return false;
            });

            $('#contact_with_author_window form').submit(function () {

                var form = $(this);
                hideErrors();
                var name = form.find('[name=name]').val();
                var phone = form.find('[name=phone]').val();
                var email = form.find('[name=email]').val();
                var message = form.find('[name=message]').val();
                var theme = form.find('[name=theme]').val();
                var to = form.find('[name=to]').val();
                var realty_id = form.find('[name=realty_id]').val();
                {/literal}
                        {if $post_form_agreement_enable}
                            {literal}
                                var check_post_form = true;
                                var post_form_agree = form.find('[name=post_form_agree]').prop( "checked" );
                            {/literal}
                            {else}
                            {literal}
                                var post_form_agree = true;
                                var check_post_form = false;
                            {/literal}
                        {/if}
                {literal}
                //console.log('name = ' + name+', '+ 'phone = ' +phone+', '+'email = ' +email+',  '+ 'message = ' +message+', '+ 'theme = ' +theme+', '+ 'check_post_form = ' +check_post_form+', '+ 'post_form_agree = ' +post_form_agree);
                if (name == '' || message == '' || phone == '' || theme == '' || (check_post_form && !post_form_agree)) {
                    form.find('#error_block').show();

                } else {
                    $.ajax({
                        type: 'post',
                        url: estate_folder+'/js/ajax.php',
                        data: {_app: 'mailbox', action:'send_message', name: name, message: message, theme: theme, email: email, phone: phone, reciever_id: to, realty_id: realty_id},
                        dataType: 'json',
                        success: function (json) {
                            if (json.answer == 'fields_not_specified') {
                                form.find('#error_block').show();
                            } else if (json.answer == 'no_reciever') {
                                form.find('#error_block_nouser').show();
                            } else {
                                form.find('[name=name]').val('');
                                form.find('[name=phone]').val('');
                                form.find('[name=email]').val('');
                                form.find('[name=message]').val('');

                                $('#contact_with_author_window').hide();
                            }
                        }
                    });
                }
                return false;
            });

            $('#contact_with_author_window').find('.closer').click(function () {
                $('#contact_with_author_window').fadeOut();
            });

            $('#send_friend_window').find('.closer').click(function () {
                $('#send_friend_window').fadeOut();
            });
        });
    </script>
{/literal}
<div class="mailbox-options">
    <div class="row-fluid">
        <div class="span12 col-sm-12">
            <span><a href="#" id="contact_with_author" class="{if $mailbox_btn_classes!=''}{$mailbox_btn_classes}{else}btn btn-info{/if}"><i class="icon-white icon-envelope"></i> {if $message_to_author_title != ''}{$message_to_author_title}{else}{$apps_words.mailbox.MAILBOX_ORDER}{/if}</a></span>
        </div>
    </div>
    {if isset($send_friend_window_hide) && $send_friend_window_hide eq '1'}
    {else}
    <div class="row-fluid">
        <div class="span12 col-sm-12">
            <span><a href="#" id="send_friend" class="{if $mailbox_btn_classes!=''}{$mailbox_btn_classes}{else}btn btn-info{/if}"><i class="icon-white icon-thumbs-up"></i> {if $message_to_friend_title != ''}{$message_to_friend_title}{else}{$apps_words.mailbox.MAILBOX_SHARE}{/if}</a></span>
        </div>
    </div>
    {/if}
</div>
{if $apps_mailbox_use_complaint_mode==1}
    <div class="mailbox-options mailbox-complaint">
        <div class="row-fluid">
            <div class="span12 col-sm-12">
                <span><a href="#" id="complaint_this_adv" class="{$mailbox_btn_classes_complaint}{if $mailbox_btn_classes!=''}{$mailbox_btn_classes}{else}btn btn-info{/if}"><i class="icon-white icon-warning-sign"></i> {$apps_words.mailbox.MAILBOX_COMPLAINT}</a></span>
            </div>
        </div>
        <div class="complaint_this_adv_form" style="display: none;">
            <form>
            <div class="msg" style="display: none;"></div>
            <ul>
                {foreach from=$apps_mailbox_complaint_mode_variants key=key item=apps_mailbox_complaint_mode_variant}
                    <li><input name="complaint_this_adv_form_opt" value="{$key}" type="radio"> {$apps_mailbox_complaint_mode_variant}</li>
                {/foreach}
            </ul>
            {$apps_mailbox_complaint_mode_captcha}
            <button{if isset($data_shared.id.value)} data-id="{$data_shared.id.value}"{/if}>{$apps_words.mailbox.MAILBOX_COMPLAINT}</button>
            </form>
        </div>
    </div>
    {literal}
    <script type="text/javascript">
            $(document).ready(function () {
                $('#complaint_this_adv').click(function (e) {
                e.preventDefault();
                $(this).parents('.mailbox-complaint').eq(0).find('.complaint_this_adv_form').fadeToggle();
            });
                $('.complaint_this_adv_form button').click(function (e) {
                e.preventDefault();
                    var form = $(this).parents('form');
                form.find('.msg').hide();
                    var captcha_session_key = form.find('[name=captcha_session_key]').val();
                    var captcha = form.find('[name=captcha]').val();
                    var variant_id = form.find('[name=complaint_this_adv_form_opt]:checked').val();
                    if (isNaN(variant_id)) {
                        variant_id = 0;
                }
                    var id = $(this).data('id');
                    if (variant_id != 0) {
                    $.ajax({
                        type: 'post',
                        url: estate_folder+'/js/ajax.php',
                        data: {_app: 'mailbox', action: 'send_complaint', complaint_id: variant_id, id: id, captcha_session_key: captcha_session_key, captcha: captcha},
                        dataType: 'json',
                            success: function (json) {
                                if (json.status == 1) {
                                form.parents('.mailbox-complaint').eq(0).remove();
                                } else {
                                form.find('[name=captcha]').val('');
                                form.find('.captcha_refresh').trigger('click');
                                form.find('.msg').text(json.msg).show();
                            }
                        }
                    });
                }
            });
        });
    </script>
    {/literal}
{/if}
<div id="contact_with_author_window" style="display: none;">
    <div class="closer"></div>
    <div class="inner">
        <form>

            <div id="error_block" class="required">{$apps_words.mailbox.FIELDS_EMPTY}</div>
            <div id="error_block_nouser" class="required">{$apps_words.mailbox.CANT_SEND_MESSAGE}</div>
            <input type="hidden" name="realty_id" value="{$data.id.value}" />
            <input type="hidden" name="to" value="{$to}" />
            <div><label>{$apps_words.mailbox.SUBJECT} <span class="required">*</span></label><input type="text" name="theme" value="{if isset($theme)}{$theme}{else}{', '|implode:$title_data} ID:{$data.id.value}{/if}" /></div>
            <div><label>{$apps_words.mailbox.MESSAGE} <span class="required">*</span></label><textarea name="message"></textarea></div>
            <div><label>{$apps_words.mailbox.NAME} <span class="required">*</span></label><input type="text" name="name" /></div>
            <div><label>{$apps_words.mailbox.PHONE} <span class="required">*</span></label><input type="text" name="phone" /></div>
            <div><label>{$apps_words.mailbox.EMAIL} <span class="required">*</span> </label><input type="text" name="email" /></div>
            {if $post_form_agreement_enable}
            <div><input type="checkbox" value="1" name="post_form_agree" /> <label>{$post_form_agreement_text_add}</label></div>
            {/if}
            <div><span class="required">*</span> - обязательные поля</div>
            <div><input type="submit" value="{$apps_words.mailbox.SEND}" /></div>
        </form>
    </div>
</div>
{if isset($send_friend_window_hide) && $send_friend_window_hide eq '1'}
{else}
<div id="send_friend_window" style="display: none;">
    <div class="closer"></div>
    <div class="inner">
        <script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
        <script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>
        <div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus,twitter,linkedin,whatsapp,viber"></div>
    </div>
</div>
{/if}
