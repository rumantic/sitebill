{literal}
g=document.createElement('div');
g.setAttribute('id', 'sitebill_dashboard');
g.setAttribute('style', 'visibility: visible; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.5); border-radius: 5px; padding: 5px; background-color: white; width: 237px; height: 40px; display: block; left: 40px; bottom: 0px; position: fixed!important;z-index: 9999999;animation-fill-mode: forwards!important;');
document.body.appendChild(g);
g.innerHTML = '<link rel="stylesheet" href="{/literal}{$estate_folder}{literal}/apps/dashboard/css/style.css" type="text/css"><div id="dashboard_panel">Настройки</div><iframe src=\"/apps/dashboard/js/ajax.php?action=iframe&widget_id=\" border=\"0\" width=\"100%\" height=\"100%\"></iframe>';
var dashboard_panel = document.getElementById("dashboard_panel");
dashboard_panel.onclick = function (e) {
  var e = e || window.event;
  var target = e.target || e.srcElement;
  if (this == target) {
    if ( g.style.height == "40px" ) {
        g.style.height = "538px";
        g.style.width = "90%";
    } else {
        g.style.height = "40px";
        g.style.width = "237px";
    }
  }
}

{/literal}
