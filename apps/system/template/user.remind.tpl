<div class="system-user-remind">
    {if $error_msg!=''}
    <div class="system-error-msg">{$error_msg}</div>
    {/if}
    {if $success_msg!=''}
    <div class="system-success-msg">{$success_msg}</div>
    {/if}
    {if isset($remind_form)}
    {include file=$remind_form}
    {/if}
    {if isset($recovery_form)}
    {include file=$recovery_form}
    {/if}
</div>  