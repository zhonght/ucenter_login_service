var timer;
var codeId;
var codeType;
var verifyCodeToken ;


$(function () {

    $('.account-check').click(function () {
        $("#scan_verify_qr_code").attr('src',qrCodeLoading);
        $("#scan_qr_code").attr('src',qrCodeLoading);
        $("#account_login").show();
        $("#scan_login").hide();
        $("#scan_verify").hide();

        $(".scan-check").show();
        $(".account-check").hide();
        $(".login-check").hide();

        clearInterval(timer);
    });
    $('.scan-check').click(function () {
        $("#scan_verify_qr_code").attr('src',qrCodeLoading);
        $("#scan_qr_code").attr('src',qrCodeLoading);
        $("#account_login").hide();
        $("#scan_login").show();
        $("#scan_verify").hide();

        $(".scan-check").hide();
        $(".account-check").show();
        $(".login-check").hide();

        clearInterval(timer);
        getLoginQrCode();
    });
    $('.login-check').click(function () {
        $("#scan_verify_qr_code").attr('src',qrCodeLoading);
        $("#scan_qr_code").attr('src',qrCodeLoading);
        $("#account_login").show();
        $("#scan_verify").hide();
        $("#scan_login").hide();

        $(".scan-check").show();
        $(".account-check").hide();
        $(".login-check").hide();

        clearInterval(timer);
        getLoginQrCode();
    });

    $('.refresh_qrcode').click(function () {
        $("#scan_verify_qr_code").attr('src',qrCodeLoading);
        $("#scan_qr_code").attr('src',qrCodeLoading);
        clearInterval(timer);
        getLoginQrCode();
    });

    $('.refresh_verify_qrcode').click(function () {
        $("#scan_verify_qr_code").attr('src',qrCodeLoading);
        $("#scan_qr_code").attr('src',qrCodeLoading);
        clearInterval(timer);
        $("#scan_verify").show();
        getVerifyQrCode();
    });


    // 密码登陆
    $('.scan-password-btn').click(function () {
        passwordLogin();
    });



});


var getQrCodeLock = false;
function getLoginQrCode() {
    if (getQrCodeLock) {
        toast('正在生成二维码,请稍后');
        return false;
    }
    getQrCodeLock = true;
    $.post('/admin/api/scan/get_login', {}, function (result) {
        if(result['code'] == '00'){
            $('#scan_mark').hide();
            $('#scan_time_mark').hide();
            $('#tip1').show();
            $('#tip2').hide();
            $('#tip3').hide();
            $("#scan_qr_code").attr('src',result['data'][0]['url']);
            clearInterval(timer);
            codeId = result['data'][0]['code_id'];
            codeType = 'login';
            timer = setInterval(function(){
                getQrCodeStatus();
            },1000);
        }else{
            toast(result['msg']);
        }
        getQrCodeLock = false;
    });
}

var getVerifyQrCodeLock = false;
function getVerifyQrCode() {
    if (getVerifyQrCodeLock) {
        toast('正在生成二维码,请稍后');
        return false;
    }
    getVerifyQrCodeLock = true;
    $.post('/admin/api/scan/get_verify', {token:verifyCodeToken}, function (result) {
        if(result['code'] == '00'){
            $('#scan_verify_mark').hide();
            $('#scan_verify_time_mark').hide();
            $('#scan_verify_tip1').show();
            $('#scan_verify_tip2').hide();
            $('#scan_verify_tip3').hide();
            $("#scan_verify_qr_code").attr('src',result['data'][0]['url']);
            clearInterval(timer);
            codeId = result['data'][0]['code_id'];
            codeType = 'verify';
            timer = setInterval(function(){
                getQrCodeStatus();
            },1000);
        }else{
            toast(result['msg']);
        }
        getVerifyQrCodeLock = false;
    });
}




function getQrCodeStatus() {
    $.post('/admin/api/scan/code_status', {code_id:codeId}, function (result) {
        if(result['code'] == '00'){
            var status = result['data'][0];
            if(codeType == 'login'){
                if(status == 0){
                    $('#scan_mark').hide();
                    $('#scan_time_mark').hide();
                    $('#tip1').show();
                    $('#tip2').hide();
                    $('#tip3').hide();
                }else if(status == 1){
                    $('#scan_mark').show();
                    $('#scan_time_mark').hide();
                    $('#tip1').hide();
                    $('#tip2').show();
                    $('#tip3').hide();
                }else if(status == 2){
                    $('#scan_mark').hide();
                    $('#scan_time_mark').show();
                    $('#tip1').hide();
                    $('#tip2').hide();
                    $('#tip3').show();
                    clearInterval(timer);
                }else if(status == 3){
                    clearInterval(timer);
                    toast("登陆成功");
                    $('#admin_token').val(result['data'][2]);
                    $('#to_login_form').attr('action',result['data'][1]);
                    $('#to_login_form').submit();
                }
            }else if(codeType == 'verify'){
                if(status == 0){
                    $('#scan_verify_mark').hide();
                    $('#scan_verify_time_mark').hide();
                    $('#scan_verify_tip1').show();
                    $('#scan_verify_tip2').hide();
                    $('#scan_verify_tip3').hide();
                }else if(status == 1){
                    $('#scan_verify_mark').show();
                    $('#scan_verify_time_mark').hide();
                    $('#scan_verify_tip1').hide();
                    $('#scan_verify_tip2').show();
                    $('#scan_verify_tip3').hide();
                }else if(status == 2){
                    $('#scan_verify_mark').hide();
                    $('#scan_verify_time_mark').show();
                    $('#scan_verify_tip1').hide();
                    $('#scan_verify_tip2').hide();
                    $('#scan_verify_tip3').show();
                    clearInterval(timer);
                }else if(status == 3){
                    clearInterval(timer);
                    toast("验证成功");
                    $('#admin_token').val(result['data'][2]);
                    $('#to_login_form').attr('action',result['data'][1]);
                    $('#to_login_form').submit();
                }
            }
        }else{
            toast(result['msg']);
        }
    });

}


var passwordLoginLock = false;
function passwordLogin(){
    if (passwordLoginLock) {
        toast('正在登陆,请稍后');
        return false;
    }
    var username = $("#password_login_username").val();
    var password = $("#password_login_password").val();
    if(username ==''){
        toast('用户名不能为空');
        return false;
    }
    if(password ==''){
        toast('密码不能为空');
        return false;
    }
    passwordLoginLock = true;
    $.post('/admin/auth/login', {
        username:username,
        password:password,
        _token:csrfToken,
    }, function (result) {
        if(result['code'] == '00'){
            // toast(result['msg'],result['data'][0]);
            // 直接去登陆
            window.location.href = result['data'][0];
        }else if(result['code'] == '403'){

            // 开始扫码验证
            clearInterval(timer);
            $('#scan_verify .scan_verify_username').html(result['data']['name']);
            verifyCodeToken = result['data']['token'];

            if(result['data']['is_verify']){
                $("#scan_verify_tip_is_verify").show();
            }else{
                $("#scan_verify_tip_is_bind").show();
            }
            $("#account_login").hide();
            $("#scan_login").hide();
            $("#scan_verify").show();

            $(".scan-check").hide();
            $(".account-check").hide();
            $(".login-check").show();
            getVerifyQrCode();

        }else{
            toast(result['msg']);
        }
        passwordLoginLock = false;
    });
}

















/**
 * 土司
 * @param msg
 */
function toast(msg) {
    let url = arguments[1] ? arguments[1] : "";
    var timestamp = Date.parse(new Date());
    let id = "toast_" + timestamp + "_" + get_random_str();
    var str = '<div style="position: fixed;\n' +
        '    background: #666666;\n' +
        '    color: #ffffff;\n' +
        '    text-align: center;\n' +
        '    min-height: 1.2rem;\n' +
        '    line-height: 1.2rem;\n' +
        '    width: 200px;\n' +
        '    padding: 1rem 2%;\n' +
        '    left: 50%;\n' +
        '    margin-left: -100px;\n' +
        '    bottom: 20%;\n' +
        '    font-size: .5rem;\n' +
        '    opacity: .8;\n' +
        '    overflow:hidden;\n' +
        '    white-space:normal;\n' +
        '    word-break:break-all;\n' +
        '    border-radius: .5rem;" id="' + id + '">' + msg + '</div>';
    $("body").append(str);
    $("#" + id).fadeIn();
    setTimeout(function () {
        $("#" + id).fadeOut('1000', function () {
            $("#" + id).remove();
            if (url != '') {
                window.location.href = url;
            }
        });
    }, 2000);
}

/**
 * 获得随机字符串
 * @param len
 * @returns {string}
 */
function get_random_str(len) {
    len = len || 32;
    var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}
