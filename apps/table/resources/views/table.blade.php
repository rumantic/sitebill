
<link href="https://api.sitebill.ru/api/apps/cloudprovider/assets/icons/meteocons/style.css" rel="stylesheet">
<link href="https://api.sitebill.ru/api/apps/cloudprovider/assets/icons/material-icons/outline/style.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700" rel="stylesheet">

<link rel="stylesheet" href="{{\bridge\Helpers\Helpers::get_angular_file('styles')}}"></head>
<script src="{{\bridge\Helpers\Helpers::get_angular_file('runtime')}}" defer></script>
<script src="{{\bridge\Helpers\Helpers::get_angular_file('polyfills-es5')}}" nomodule defer></script>
<script src="{{\bridge\Helpers\Helpers::get_angular_file('polyfills')}}" defer></script>
<script src="{{\bridge\Helpers\Helpers::get_angular_file('main')}}" defer></script>

<app
        id="app_root"
        class="angular"
        standalone_mode="true"
        component="{{$component}}"
        table_name="{{$table_name}}"
        primary_key="{{$primary_key}}"
></app>
