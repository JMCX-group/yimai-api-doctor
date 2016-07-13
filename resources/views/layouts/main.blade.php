<?php
/**
 * Created by PhpStorm.
 * User: mvp_xuan
 * Date: 2016-4-4
 * Time: 19:22
 */
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <title>{{ $data['name'] or "YiMai" }}</title>
    {{--<link rel="stylesheet" href="{{asset('/assets/css/app.css')}}">--}}
</head>
<body>
<div>
    @yield('content')
</div>
<script src="{{ asset ("/assets/js/app.js") }}" type="text/javascript"></script>
@yield('script')
</body>
</html>
