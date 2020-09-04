<div class="login-box">
    <div class="login-box-body id_scan_login">
        <div class="scan-qr-code">
            <div class="scan-mark">
                <div class="id_scan_mark" style="display:none;"></div>
                <div class="id_scan_time_mark" style="display:none;"></div>
                <img src="" class="id_scan_qr_code">
            </div>
            <span class="id_tip1">微信扫一扫，即可绑定为账号{{$adminUser->name}}({{$adminUser->username}})的管理员</span>
            <span class="id_tip2" style="display:none;">已扫码<br><br><a class="refresh_qrcode" onclick="getLoginQrCode('{{$key}}')">重新扫描</a></span>
            <span class="id_tip3" style="display:none;">二维码已过期<br><br><a class="refresh_qrcode" onclick="getLoginQrCode('{{$key}}')">重新扫描</a></span>
        </div>
    </div>
</div>

<script>
    var qrCodeLoading = '{{$qrCodeLoading}}';
    var csrfToken = '{{$csrfToken}}';
    $(function(){
        $('td.column-scan_bind_code').each(function(){
            if($(this).find('span:first').data('key') == '{{$key}}'){
                $(this).find('span:first').click(function(){
                    getLoginQrCode('{{$key}}');
                });
            }
        });
    });
</script>
