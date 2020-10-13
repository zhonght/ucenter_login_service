<div class="login-box" id="grid-modal-{{$key}}-scan_bind_code">
    <div class="login-box-body id_scan_login">
        <div class="scan-qr-code">
            <div class="scan-mark">
                <div class="id_scan_mark" style="display:none;"></div>
                <div class="id_scan_time_mark" style="display:none;"></div>
                <img src="" class="id_scan_qr_code">
            </div>
            <span class="id_tip1">微信扫一扫，即可绑定为账号{{$adminUser->name}}({{$adminUser->username}})的管理员</span>
            <span class="id_tip2" style="display:none;">已扫码<br><br><a class="refresh_qrcode"
                                                                      onclick="getLoginQrCode('{{$key}}')">重新扫描</a></span>
            <span class="id_tip3" style="display:none;">二维码已过期<br><br><a class="refresh_qrcode"
                                                                         onclick="getLoginQrCode('{{$key}}')">重新扫描</a></span>
        </div>
    </div>
</div>

<style>
    td.column-scan_bind_code span.grid-expand i {
        display: none;
    }
</style>
<script>
    var csrfToken = "{{csrf_token()}}";
    var qrCodeLoading = "{{admin_asset("vendor/weigather/wj_ucenter_login_service/img/qr_code_loading.gif")}}";
</script>
@if(in_array(get_wj_ucenter_login_service_version(),[0,1,3]))
    <script>
        $(function () {
            getLoginQrCode('{{$key}}');
        });
    </script>
@endif
