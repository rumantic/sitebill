{literal}
    <link type="text/css" rel="stylesheet" media="screen" href="http://converse.sitebill.ru/components/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" media="screen" href="{/literal}{$estate_folder}{literal}/apps/messenger/components/fontawesome/css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" media="screen" href="http://converse.sitebill.ru/css/converse.css" />

    <!-- BEGIN JQUERY -->
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/jquery/dist/jquery.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/jquery.browser/dist/jquery.browser.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/typeahead.js/index.js"></script>
    <!-- END JQUERY -->

    <!-- BEGIN OTR: Off-the-record encryption stuff. Can be omitted if OTR is not used. -->
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/build/dep/salsa20.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/src/bigint.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/core.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/enc-base64.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/crypto-js-evanvosberg/src/md5.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/crypto-js-evanvosberg/src/evpkdf.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/cipher-core.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/aes.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/sha1.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/sha256.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/hmac.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/pad-nopadding.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/vendor/cryptojs/mode-ctr.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/otr/build/dep/eventemitter.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/src/otr.js"></script>
    <!-- END OTR -->

    <!-- BEGIN STROPHE -->
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/strophe.js/strophe.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/strophejs-plugins/vcard/strophe.vcard.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/strophejs-plugins/disco/strophe.disco.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/components/strophejs-plugins/rsm/strophe.rsm.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/src/strophe.ping.js"></script>
    <!-- END STROPHE -->

    <!-- BEGIN BACKBONE -->
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/underscore/underscore.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/backbone//backbone.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/backbone.browserStorage/backbone.browserStorage.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/backbone.overview/backbone.overview.js"></script>
    <!-- END BACKBONE -->

    <!-- BEGIN I18N -->
    <!-- These files can be removed if you don't want to include any
        translations for converse.js.
        If you want to modify which translations are included, you can modify
        src/locales.js to remove those you don't need, and then run `make
        build` to generates a new dist/locales.js file.
    -->
    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/jed/jed.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/dist/locales.js"></script>
    <!-- END I18N -->

    <script type="text/javascript" src="http://converse.sitebill.ru/node_modules/moment/min/moment-with-locales.js"></script>
    <script type="text/javascript" src="http://converse.sitebill.ru/dist/converse-no-dependencies.js"></script>
    <div id="conversejs_toolbar"></div>
<script type='text/javascript'>
    var messenger_widget = {/literal}{if $messenger_widget eq 'true'}true{else}false{/if}{literal};
    var jabber_id = '{/literal}{$user_info.jabber_id}{literal}';
    var jabber_password = '{/literal}{$user_info.jabber_password}{literal}';
    var user_email = '{/literal}{$user_info.email}{literal}';
    var user_fio = '{/literal}{$user_info.fio}{literal}';
    var server_name = '{/literal}{$user_info.server_name}{literal}';
    var channel = '{/literal}{if $messenger_frontend eq 'true'}local{else}{if $smarty.request.channel!= ''}{$smarty.request.channel}{else}local{/if}{/if}{literal}';
    
    var login_val = jabber_id;
    var password_val = jabber_password;
    
    function join_public_conference () {
	    converse.rooms.open('public@conference.sitebill.ru', '{/literal}{$user_info.fio}{literal}');
    }
    
    
	        jQuery.ajax({
	        	url: 'https://www.sitebill.ru/apps/jabber/js/ajax.php',
	        	dataType: 'jsonp',
	        	data: {login: login_val, password: password_val, full_name: user_fio, email: user_email, group_name: server_name},
	        	success: function(json){
			    
    require(['converse'], function (converse) {
        converse.initialize({
            bosh_service_url: 'https://sitebill.ru:7443/http-bind/', // Please use this connection manager only for testing purposes
	    keepalive: true,
            i18n: locales.ru, // Refer to ./locale/locales.js to see which locales are supported
	    jid: '{/literal}{$user_info.jabber_id}{literal}@sitebill.ru',
            auto_login: true,
            prebind: true,
	    prebind_url: '{/literal}{$estate_folder}{literal}/apps/messenger/js/ajax.php?action=prebind',
	    allow_logout: false,
            show_controlbox_by_default: false,
	    hide_offline_users: true,
            debug: false,
	    hide_muc_server: true,
	    {/literal}{if $smarty.request.params ne 'admin_backend'}allow_muc: false,{/if}{literal}
	    allow_registration: false,
	    auto_list_rooms: true,
	    //auto_join_rooms: [{'jid': 'public@sitebill.ru'}],
	    play_sounds: true,
	    sounds_path: '{/literal}{$estate_folder}{literal}/apps/messenger/sounds/',
            roster_groups: false
        });
/*	
        converse.initialize({
            bosh_service_url: 'https://sitebill.ru:7443/http-bind/', // Please use this connection manager only for testing purposes
            i18n: locales.ru, // Refer to ./locale/locales.js to see which locales are supported
	    jid: '{/literal}{$user_info.jabber_id}{literal}@sitebill.ru',
	    password: '{/literal}{$user_info.jabber_password}{literal}',
            auto_login: true,
            prebind: false,
            show_controlbox_by_default: true,
            debug: false,
	    hide_muc_server: true,
	    {/literal}{if $smarty.request.params ne 'admin_backend'}allow_muc: false,{/if}{literal}
	    allow_registration: false,
	    auto_list_rooms: true,
	    //auto_join_rooms: [{'jid': 'public@sitebill.ru'}],
	    play_sounds: true,
	    sounds_path: '{/literal}{$estate_folder}{literal}/apps/messenger/sounds/',
            roster_groups: true
        });
*/	
	converse.listen.on('initialized', function (event) {
            console.log('initialized');
	});

        converse.listen.on('reconnect', function (event) { 
            console.log('reconnect');
	});

        converse.listen.on('ready', function (event) {
            console.log('ready');
            //converse.chats.open('public@conference.sitebill.ru');
	    converse.rooms.open('{/literal}{$user_info.server_name}{literal}@conference.sitebill.ru', '{/literal}{$user_info.fio}{literal}');
	    //converse.chats.open('exo.estateutf81@sitebill.ru');
	    //converse.chats.open('admin@sitebill.ru');
	    
	    
	    {/literal}{if $smarty.request.params eq 'admin_backend'}
	    document.getElementById("conversejs_toolbar").innerHTML = '<button id="join_public" class="btn">Подключиться к общему чату</button>';
	    var join_public = document.getElementById("join_public");
	    join_public.onclick = join_public_conference;
	    {/if}{literal}
            //converse.chats.open('admin@sitebill.ru');
            console.log('done ready');
        });
	/*
	converse.listen.on('message', function (event, xml) {
	    var $ = converse.env.jQuery;
	    var from = xml.getAttribute('from').split('/')[1];
	    var body = '';

	    $(xml).find('body').each(function() {
		if ($(this).text() != '') {
		    body += $(this).text() + "\n";
		}
	    });

	    if (body != '') {
		window.parent.document.getElementById("messenger_panel").innerHTML = "Сообщения (11)";
	    }
	});	
	*/
    });
			    
			    
	        	},
			error: function(json){
			    //console.log('error = ' + json);
			},
			complete: function(json){
			    //console.log('complete = ' + json);
			}
			
	        });
    
</script>
{/literal}


