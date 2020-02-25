<script src="http://mourner.github.io/simplify-js/simplify.js"></script>
<script src="{$estate_folder}/apps/system/js/activemap.js"></script>
<div id="container" class="ActiveMapContainer" style="position: relative;">    
	<div class="e-map-draw-search ActiveMapControls">
        <div id="ActiveMapControls-Draw" class="b-btn m-green pull-left hidden-print ">
            <i class="fa fa-pencil"></i>
                <span id="DrawModeButtonTextDrawSearch">Обвести</span>
        </div>
        {if 1==0}
        <div id="DrawModeCancelButton" class="b-btn m-red pull-left hide hidden-print ActiveMapControls-Cancel">
            <i class="fa fa-times"></i>
                <span id="DrawModeButtonTextCancel">Отменить</span>
        </div>
        {/if}
        <div id="ActiveMapControls-Clear" class="b-btn m-red pull-left hidden-print ">
            <i class="fa fa-times"></i>
                <span id="DrawModeButtonTextClear">Очистить</span>
        </div>
    </div>
    <div id="ActiveMap" class="" data-center-lat="55.757175" data-center-lng="37.628815" data-zoom="14"></div>
    <canvas id="ActiveMapCanvas" class="" style="position: absolute; left: 0; top: 0; display: none;"></canvas>
</div>
{literal}

<style>
#ActiveMap {
    width: 100%;
    height: 100%;
    min-height: 500px;
}
.e-map-draw-search {
    position: absolute;
    z-index: 3;
    top: 20px;
    right: 20px;
}
.e-map-draw-search .b-btn {
    width: 121px;
    border-radius: 20px;
    -webkit-box-shadow: 0 5px 15px -7px rgba(0,0,0,.5);
    box-shadow: 0 5px 15px -7px rgba(0,0,0,.5);
}
.b-btn {
    position: relative;
    display: inline-block;
    padding: 8px 22px;
    cursor: pointer;
    text-align: center;
    border-radius: 2px;
    background: white;
}
</style>
{/literal}
{literal}
<script>





</script>
{/literal}

{if $map_type=='google'}
    <script>
    $(document).ready(function(){
        if (typeof google == 'object') {
            ActiveMap.init('ActiveMapContainer', 'google');
        }
    });
    </script>
{elseif $map_type=='yandex'}
    <script>
    $(document).ready(function(){
        ymaps.ready(['Map', 'Polygon']).then(function() {
            ActiveMap.init('ActiveMapContainer', 'yandex');
        });
    });
    </script>
{/if}