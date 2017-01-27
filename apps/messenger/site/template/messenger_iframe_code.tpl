{literal}
g=document.createElement('div');
g.setAttribute('id', 'sitebill_messenger');
g.setAttribute('style', 'visibility: visible; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.5); border-radius: 5px; padding: 5px; background-color: white; width: 237px; height: 35px; display: block; right: 40px; bottom: 0px; position: fixed!important;z-index: 9999999;animation-fill-mode: forwards!important;');
document.body.appendChild(g);
g.innerHTML = '<div id="messenger_panel" style="height: 25px; margin-bottom: 5px;font-size: 14px;padding: 5px;background-color: #3b5998;color: white;cursor: pointer;" >Сообщения <div style="float: right; padding-top: 3px;" id="icon_open_close" ><i class="icon-white icon-resize-full" title="Раскрыть"></i></div></div><iframe src=\"{/literal}{$estate_folder}{literal}/apps/messenger/js/ajax.php?action=iframe&params={/literal}{$smarty.request.params}{literal}&widget_id=\" border=\"0\" width=\"100%\" height=\"90%\" style=\"border: 0px; overflow: hidden;\"></iframe>';
var messenger_panel = document.getElementById("messenger_panel");

var icon_open_close = document.getElementById("icon_open_close");

function resizePanel(){
    if ( g.style.height == "35px" ) {
        g.style.height = "538px";
        g.style.width = "90%";
	document.getElementById("icon_open_close").innerHTML = '';
	document.getElementById("icon_open_close").innerHTML = '<i class="icon-white icon-resize-small" title="Свернуть"></i>';
    } else {
        g.style.height = "35px";
        g.style.width = "237px";
	document.getElementById("icon_open_close").innerHTML = '';
	document.getElementById("icon_open_close").innerHTML = '<i class="icon-white icon-resize-full" title="Раскрыть"></i>';
    }
}
messenger_panel.onclick = resizePanel;
{/literal}

