{if is_array($city_tags_array)}

    <div class="btn-group">

        {section name=i loop=$city_tags_array}
            <a
                    class="btn btn-success
                    {if is_array($smarty.session.model_tags.data.tags_array.city_id)}
                      {if $smarty.session.model_tags.data.tags_array.city_id[0] == $city_tags_array[i]}
                          disabled
                      {/if}
                    {/if}
                    "
                    href="{formaturl path='account/data/all'}?subdo=set_tags&tag_name=city_id&tag_value={$city_tags_array[i]}"
            >
                {$city_tags_array[i]}
            </a>
        {/section}
    </div>
{/if}
