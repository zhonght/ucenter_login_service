var timer;
var codeId;


function getLoginQrCode(key) {
    $(".id_scan_qr_code").attr('src', qrCodeLoading);
    clearInterval(timer);
    $.post('/admin/api/scan/get_bind', {
        'id': key,
        '_token': csrfToken
    }, function (result) {
        if (result['code'] == '00') {
            $('#grid-modal-' + key + '-scan_bind_code .id_scan_mark').hide();
            $('#grid-modal-' + key + '-scan_bind_code .id_scan_time_mark').hide();
            $('#grid-modal-' + key + '-scan_bind_code .id_tip1').show();
            $('#grid-modal-' + key + '-scan_bind_code .id_tip2').hide();
            $('#grid-modal-' + key + '-scan_bind_code .id_tip3').hide();
            $('#grid-modal-' + key + '-scan_bind_code .id_scan_qr_code').attr('src', result['data'][0]['url']);
            codeId = result['data'][0]['code_id'];
            timer = setInterval(function () {
                getQrCodeStatus(key);
            }, 1000);
        } else {
            toast(result['msg']);
        }
    });
}

function getQrCodeStatus(key) {
    $.post('/admin/api/scan/code_status', {code_id: codeId}, function (result) {
        if (result['code'] == '00') {
            var status = result['data'][0];
            if (status == 0) {
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_mark').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_time_mark').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip1').show();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip2').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip3').hide();
            } else if (status == 1) {
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_mark').show();
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_time_mark').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip1').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip2').show();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip3').hide();
            } else if (status == 2) {
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_mark').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_scan_time_mark').show();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip1').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip2').hide();
                $('#grid-modal-' + key + '-scan_bind_code .id_tip3').show();
                clearInterval(timer);
            } else if (status == 3) {
                clearInterval(timer);
                toast("绑定成功");
                location.reload();
            }
        } else {
            toast(result['msg']);
        }
    });
}

var actionResolver = function (data) {
    var response = data[0];
    var target   = data[1];
    if (typeof response !== 'object') {
        return $.admin.swal({type: 'error', title: 'Oops!'});
    }
    var then = function (then) {
        if (then.action == 'refresh') {
            $.admin.reload();
        }
        if (then.action == 'download') {
            window.open(then.value, '_blank');
        }
        if (then.action == 'redirect') {
            $.admin.redirect(then.value);
        }
        if (then.action == 'location') {
            window.location = then.value;
        }
    };
    if (typeof response.html === 'string') {
        target.html(response.html);
    }
    if (typeof response.swal === 'object') {
        $.admin.swal(response.swal);
    }
    if (typeof response.toastr === 'object' && response.toastr.type) {
        $.admin.toastr[response.toastr.type](response.toastr.content, '', response.toastr.options);
    }
    if (response.then) {
        then(response.then);
    }
};

var actionCatcher = function (request) {
    if (request && typeof request.responseJSON === 'object') {
        $.admin.toastr.error(request.responseJSON.message, '', {positionClass:"toast-bottom-center", timeOut: 10000}).css("width","500px")
    }
};

function deleteBindInfo(that) {
        var data = $(that).data();
        var target = $(that);
        Object.assign(data, {"_model": "Weigather_WJUcenterLoginService_Models_AdminScanBind"});
        var process = $.admin.swal({
            "type": "question",
            "showCancelButton": true,
            "showLoaderOnConfirm": true,
            "confirmButtonText": "\u63d0\u4ea4",
            "cancelButtonText": "\u53d6\u6d88",
            "title": "\u786e\u8ba4\u89e3\u7ed1?",
            "text": "",
            "confirmButtonColor": "#d33",
            preConfirm: function (input) {
                return new Promise(function (resolve, reject) {
                    Object.assign(data, {
                        _token: $.admin.token,
                        _action: 'Weigather_WJUcenterLoginService_Actions_Unbind',
                        _input: input,
                    });

                    $.ajax({
                        method: 'POST',
                        url: '/admin/_handle_action_',
                        data: data,
                        success: function (data) {
                            resolve(data);
                        },
                        error: function (request) {
                            reject(request);
                        }
                    });
                });
            }
        }).then(function (result) {
            if (typeof result.dismiss !== 'undefined') {
                return Promise.reject();
            }
            if (typeof result.status === "boolean") {
                var response = result;
            } else {
                var response = result.value;
            }
            return [response, target];
        });
        process.then(actionResolver).catch(actionCatcher);
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
