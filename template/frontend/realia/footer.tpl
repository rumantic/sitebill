<div id="footer-wrapper">
    <div id="footer-top">
        <div id="footer-top-inner" class="container">
            <div class="row">
                <div class="widget properties span3">
                    <div class="title">
                        <h2>{$LT_NEW_OBJECTS}</h2>
                    </div><!-- /.title -->

                    <div class="content">
                        {foreach from=$new_grid_items item=new_grid_item}

                        <div class="property">
                            <div class="image">
                                <a href="{$new_grid_item.href}"></a>
                                {if $new_grid_item.img != '' }
                                <img src="{$estate_folder}/img/data/{$new_grid_item.img[0].preview}" class="previewi">
                                {else}
                                <img src="{$estate_folder}/template/frontend/realia/img/no_foto_100x74.png" class="previewi">
                                {/if}

                            </div><!-- /.image -->
                            <div class="wrapper">
                                <div class="title">
                                    <h3>
                                        <a href="{$new_grid_item.href}">
                                            {if $new_grid_item.city ne ''} {$new_grid_item.city}{if
                    $new_grid_item.street ne ''}, {$new_grid_item.street}{if
                    $new_grid_item.number ne ''}, {$new_grid_item.number}{/if}{/if}
                    {else} {if $new_grid_item.street ne ''} {$new_grid_item.street}{if
                    $new_grid_item.number ne ''}, {$new_grid_item.number}{/if} {/if}
                    {/if}
                                        </a>
                                    </h3>
                                </div><!-- /.title -->
                                <div class="location">{$new_grid_item.path}</div><!-- /.location -->
                                {if $new_grid_item.price_discount > 0}
                                <div class="price">
                                {$new_grid_item.price_discount|number_format:0:",":" "} {if $new_grid_item.currency_name != ''}{$new_grid_item.currency_name}{/if}
                                <div class="price_discount_footer">{$new_grid_item.price|number_format:0:",":" "} {if $new_grid_item.currency_name != ''}{$new_grid_item.currency_name}{/if}</div><!-- /.price -->
                                </div>
                                {else}
                                <div class="price">{$new_grid_item.price|number_format:0:",":" "} {if $new_grid_item.currency_name != ''}{$new_grid_item.currency_name}{/if}</div>
                                {/if}
                            </div><!-- /.wrapper -->
                        </div><!-- /.property -->
                        {/foreach}

                    </div><!-- /.content -->
                </div><!-- /.properties-small -->

                <div class="widget span3">
                    <div class="title">
                        <h2>{$LT_ABOUT}</h2>
                    </div><!-- /.title -->

                    <div class="content">
                        <table class="contact">
                            <tbody>
                            <tr>
                                <th class="address">{$L_ADDRESS}:</th>
                                {if $apps_contact_address != ''}
                                    <td>{$apps_contact_address}</td>
                                {else}
                                    <td>Россия<br>Красноярск<br>Батурина, 19<br></td>
                                {/if}
                            </tr>
                            <tr>
                                <th class="phone">{$L_PHONE}:</th>

                                {if $apps_contact_phone != ''}
                                    <td><a href="tel:{$apps_contact_phone}">{$apps_contact_phone}</a></td>
                                {else}
                                    <td><a href="tel:8 800 250-99-31">8 800 250-99-31</a></td>
                                {/if}
                                
                            </tr>
                            <tr>
                                <th class="email">E-mail:</th>
                                {if $apps_contact_email != ''}
                                    <td><a href="mailto:{$apps_contact_email}">{$apps_contact_email}</a></td>
                                {else}
                                    <td><a href="mailto:dkondin@gmail.com">dkondin@gmail.com</a></td>
                                {/if}
                                
                            </tr>
                            <tr>
                                <th class="skype">Skype:</th>
                                {if $apps_contact_skype != ''}
                                <td><a href="skype:{$apps_contact_skype}">{$apps_contact_skype}</a></td>
                                {else}
                                    <td><a href="skype:kondin.dmitry">kondin.dmitry</a></td>
                                {/if}
                            </tr>
                            </tbody>
                        </table>
                    </div><!-- /.content -->
                </div><!-- /.widget -->

                <div class="widget span3">
                    <div class="title">
                        <h2 class="block-title">{$LT_FOR_USER}</h2>
                    </div><!-- /.title -->

                    <div class="content">
                        <ul class="menu nav">
                            {section name=i loop=$for_user_menu}
                                <li><a href="{$for_user_menu[i].url}">{$for_user_menu[i].name}</a></li>
                            {/section}
                        </ul>
                    </div><!-- /.content -->
                </div><!-- /.widget -->

                <div class="widget span3">
                    <div class="title">
                        <h2 class="block-title">{$LT_CONTACTUS}</h2>
                    </div><!-- /.title -->
    {literal}
    <script>
    $(document).ready(function(){
        ClientOrder.init_form('order_form1', 'contactus');
    });
    </script>
    {/literal}
                    <div class="content">

                        <div id="order_form1"></div>

                    </div><!-- /.content -->
                </div><!-- /.widget -->
            </div><!-- /.row -->
        </div><!-- /#footer-top-inner -->
    </div><!-- /#footer-top -->

    <div id="footer" class="footer container">
        <div id="footer-inner">
            <div class="row">
                <div class="span6 copyright">
                    <p>Сделано на <a href="http://www.sitebill.ru" target="_blank">CMS SiteBill</a></p>
                </div><!-- /.copyright -->

                <div class="span6 share">
                    <div class="content">
                        <ul class="menu nav">
                            <li class="first leaf"><a href="http://www.facebook.com" class="facebook">Facebook</a></li>
                            <li class="leaf"><a href="http://flickr.net" class="flickr">Flickr</a></li>
                            <li class="leaf"><a href="http://plus.google.com" class="google">Google+</a></li>
                            <li class="leaf"><a target="_blank"href="http://www.linkedin.com" class="linkedin">LinkedIn</a></li>
                            <li class="leaf"><a href="http://www.twitter.com" class="twitter">Twitter</a></li>
                            <li class="last leaf"><a href="http://www.vimeo.com" class="vimeo">Vimeo</a></li>
                        </ul>
                    </div><!-- /.content -->
                </div><!-- /.span6 -->
            </div><!-- /.row -->
        </div><!-- /#footer-inner -->
    </div><!-- /#footer -->
</div><!-- /#footer-wrapper -->