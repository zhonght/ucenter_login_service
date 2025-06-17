<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.title')}} | ‌登录 </title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if( get_wj_ucenter_login_service_version() >=1 && !is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset("{$assetUrl}/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("{$assetUrl}/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("{$assetUrl}/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset("{$assetUrl}/AdminLTE/plugins/iCheck/square/blue.css") }}">

    <link rel="stylesheet"
          href="{{ admin_asset("vendor/weigather/wj_ucenter_login_service/template/default/css/scan_login.css") }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page" id="scan-page"
      style="background: url({{ config('wj_ucenter_login_service.login_bg_img')?:admin_asset("vendor/weigather/wj_ucenter_login_service/template/default/img/login_bg.jpg") }}) center center no-repeat;background-size: cover;">


<div class="login-box" id="scan-box">
    <div class="login-logo">
        <a href="{{ admin_url('/') }}"><b>{{config('admin.name')}}234</b></a>
    </div>
    <!-- ‌登录页面 -->
    <div class="login-box-body" id="account_login" style="display: none">
        <a href="javascript:;" class="scan-check"></a>
        <p class="login-box-msg">账号‌登录</p>
        <form action="{{ admin_url('auth/login') }}" method="post" id="password_login_form">
            <div class="form-group has-feedback">
                <input type="text" id="password_login_username" class="form-control"
                       placeholder="账号" name="username">
            </div>
            <div class="form-group has-feedback">
                <input type="password" id="password_login_password" class="form-control"
                       placeholder="密码" name="password">
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="row">
                <div class="col-xs-12">
                    <button type="button" class="scan-password-btn btn btn-primary btn-block btn-flat">‌登录</button>
                </div>
            </div>
        </form>
    </div>
    <!-- 验证页面 -->
    <div class="login-box-body" id="scan_verify" style="display: none">
        <a href="javascript:;" class="login-check">重新‌登录</a>
        <p class="login-box-msg">扫码验证</p>
        <div class="scan-qr-code">
            <span id="scan_verify_tip_is_verify" style="display:none;">为保障帐号(<b class="scan_verify_username"></b>)安全，请用享脉企业版扫码验证身份</span>
            <span id="scan_verify_tip_is_bind" style="display:none;">为保障帐号(<b class="scan_verify_username"></b>)安全，请先用享脉企业版扫码绑定成为管理员</span>

            <div class="scan-mark">
                <div id="scan_verify_mark" style="display:none;"></div>
                <div id="scan_verify_time_mark" style="display:none;"></div>
                <img src="" id="scan_verify_qr_code">
            </div>
            <span id="scan_verify_tip1">请联系账号管理员扫码验证登录</span>
            <span id="scan_verify_tip2" style="display:none;">已扫码<br><br><a
                    class="refresh_verify_qrcode">重新扫描</a></span>
            <span id="scan_verify_tip3" style="display:none;">二维码已过期<br><br><a
                    class="refresh_verify_qrcode">重新扫描</a></span>
        </div>
    </div>
    <!-- 扫码页面 -->
    <div class="login-box-body" id="scan_login" style="display: none">
        <a href="javascript:;" class="account-check">使用帐号登录</a>
        <p class="login-box-msg">扫码‌登录</p>
        <div class="scan-qr-code">
            <div class="scan-mark">
                <div id="scan_mark" style="display:none;"></div>
                <div id="scan_time_mark" style="display:none;"></div>
                <img src="" id="scan_qr_code">
            </div>
            <span id="tip1">享脉企业版扫一扫，选择该享脉企业版下的帐号登录</span>
            <span id="tip2" style="display:none;">请在享脉企业版中选择帐号登录<br><br><a
                    class="refresh_qrcode">重新扫描</a></span>
            <span id="tip3" style="display:none;">二维码已过期<br><br><a class="refresh_qrcode">重新扫描</a></span>
        </div>
    </div>

    <!-- 全部使用admin_token‌登录 -->
    <form action="" id="to_login_form" method="post" style="display: none">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="admin_token" id="admin_token">
    </form>

</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="{{ admin_asset("{$assetUrl}/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ admin_asset("{$assetUrl}/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ admin_asset("{$assetUrl}/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
<script>
    var qrCodeLoading = "{{ admin_asset("vendor/weigather/wj_ucenter_login_service/template/default/img/qr_code_loading.gif")}}";
    var passwordLoginUrl = "{{ admin_url('auth/login') }}";
    var scanLoginUrl = "{{ admin_url('api/scan/get_login') }}";
    var csrfToken = "{{ csrf_token() }}";

</script>
<script src="{{ admin_asset("vendor/weigather/wj_ucenter_login_service/template/default/js/scan_login.js")}}"></script>
<script>

    $(function () {
        $('.scan-check').click();
        toast('正在生成二维码,请稍后');
        toast('正在生成二维码,请稍后');
        toast('正在生成二维码,请稍后');
    });
    document.onkeydown = function (e) {
        var theEvent = window.event || e;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
        if (code == 13) {
            if (!$('#account_login').is(':hidden')) {
                $('.scan-password-btn').click();
            }
        }
    }
</script>
</body>
</html>
