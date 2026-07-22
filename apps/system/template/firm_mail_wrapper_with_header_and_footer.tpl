{literal}
<html lang="ru">
<body>
<!--begin::Email template-->
<style>html,body { padding:0; margin:0; font-family: Inter, Helvetica, "sans-serif"; } a:hover { color: #009ef7; }</style>
<div style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:40px 0; width:100%;">
    <div style="background-color:#ffffff; padding: 45px 0 34px 0; border-radius: 24px; margin:0 auto; max-width: 600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
            <tbody>
            <tr>
                <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                    <!--begin:Email content-->
                    <div style="text-align:center; margin:0 60px 34px 60px">
                        <!--begin:Content-->
                        <div style="margin-bottom: 15px;text-align:left;">
{/literal}
                            {$letter_content}
{literal}

                        </div>
                        <!--end:Content-->
                    </div>
                    <!--end:Email content-->
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<!--end::Email template-->
</body>

</html>
{/literal}
