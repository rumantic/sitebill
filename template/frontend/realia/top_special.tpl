{if $special_items|count>0}
<div class="carousel specialitems">
    <h2 class="page-header">{$L_SPECIAL_OFFERS}</h2>

    <div class="content">
        <a class="carousel-prev" href="detail.html">Previous</a>
        <a class="carousel-next" href="detail.html">Next</a>
        <ul>
        	{section name=i loop=$special_items}
            <li>
                <div class="image">
                    <a href="{$special_items[i].href}"></a>
                    {if $special_items[i].img[0].preview != ''}
                    <img src="{$estate_folder}/img/data/{$special_items[i].img[0].preview}">
                    {else}
					<img src="{$estate_folder}/img/no_foto.png" class="previewi">
					{/if}

                </div><!-- /.image -->
                <div class="title">
                    <h3><a href="{$special_items[i].href}">27523 Pacific Coast</a></h3>
                </div><!-- /.title -->
                <div class="location">{$special_items[i].type_sh}</div><!-- /.location-->
                <div class="price">{$special_items[i].price|number_format:0:",":" "} {if $special_items[i].currency_name != ''}{$special_items[i].currency_name}{/if}</div><!-- .price -->
                {if (int)$special_items[i].square_all!=0}
                <div class="area">
                    <span class="key">{$L_SQUARE}:</span>
                    <span class="value">{$special_items[i].square_all} m<sup>2</sup></span>
                </div><!-- /.area -->
                {/if}
            </li>
            {/section}
        </ul>
    </div><!-- /.content -->
</div><!-- /.carousel -->
{/if}

{if $special_items2|count>0}
<div class="carousel topspecial">
    <h2 class="page-header">{$L_SPECIAL_OFFERS}</h2>

    <div class="content">
        <a class="carousel-prev" href="#">Previous</a>
        <a class="carousel-next" href="#">Next</a>
        <ul>
        	{section name=i loop=$special_items2}
            <li>
                <div class="image">
                    <a href="{$special_items2[i].href}"></a>
                    {if $special_items2[i].img[0].preview != ''}
                    <img src="{$estate_folder}/img/data/{$special_items2[i].img[0].preview}">
                    {else}
					<img src="{$estate_folder}/template/frontend/realia/img/no_foto_270x200.png" class="previewi">
					{/if}

                </div><!-- /.image -->
                <div class="title">
                    <h3><a href="{$special_items2[i].href}">
					{if	$special_items2[i].city ne ''} {$special_items2[i].city}{if
					$special_items2[i].street ne ''}, {$special_items2[i].street}{if
					$special_items2[i].number ne ''}, {$special_items2[i].number}{/if}{/if}
					{else} {if $special_items2[i].street ne ''} {$special_items2[i].street}{if
					$special_items2[i].number ne ''}, {$special_items2[i].number}{/if} {/if}
					{/if}
					</a></h3>
                </div><!-- /.title -->
                <div class="location">{if $special_items2[i].topic_info.$lang_topic_name != ''}{$special_items2[i].topic_info.$lang_topic_name}{else}{$special_items2[i].type_sh}{/if}</div><!-- /.location-->

                 {if $special_items2[i].price_discount > 0}
				<div class="price">
				{$special_items2[i].price_discount|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}
				<div class="price_discount_top">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
				</div>
                {else}
                <div class="price">{$special_items2[i].price|number_format:0:",":" "} {if $special_items2[i].currency_name != ''}{$special_items2[i].currency_name}{/if}</div>
                {/if}

                {if (int)$special_items2[i].square_all!=0}
                <div class="area">
                    <span class="key">{$L_SQUARE}:</span>
                    <span class="value">{$special_items2[i].square_all} m<sup>2</sup></span>
                </div><!-- /.area -->
                {/if}
            </li>
            {/section}
        </ul>
    </div><!-- /.content -->
</div><!-- /.carousel -->
{/if}

{literal}
<style>
.carousel.topspecial ul li .image {
	height: 180px;
	overflow-y: hidden;
}

</style>
<script>
$(document).ready(function(){
	if($('.carousel.topspecial .content ul').length>0){
		$('.carousel.topspecial .content ul').carouFredSel({
			scroll: {
				items: 1
			},
			auto: false,
			next: {
	    		button: '.carousel.topspecial .content .carousel-next',
	    		key: 'right'
			},
			prev: {
	    		button: '.carousel.topspecial .content .carousel-prev',
	    		key: 'left'
			}
		});
	}

	if($('.carousel.specialitems .content ul').length>0){
		$('.carousel.specialitems .content ul').carouFredSel({
			scroll: {
				items: 1
			},
			auto: false,
			next: {
	    		button: '.carousel.specialitems .content .carousel-next',
	    		key: 'right'
			},
			prev: {
	    		button: '.carousel.specialitems .content .carousel-prev',
	    		key: 'left'
			}
		});
	}
});

</script>
{/literal}