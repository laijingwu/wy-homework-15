function randomFilename() {
    var date = new Date();
    var name = '' + date.getFullYear()
        + (date.getMonth() + 1)
        + date.getDate()
        + date.getHours()
        + date.getMinutes()
        + date.getSeconds()
        + '_' + $.getRandom(99999);
    return name;
}

function progressHandlingFunction(e){
    if(e.lengthComputable){
    	var percentComplete = parseInt((e.loaded/e.total)*100);
    	var percentVal = percentComplete + '%';

        $('.progress-bar').attr('aria-valuenow', percentComplete).css('width', percentVal);
        $('.progress-bar span').html(percentVal);
    }
}

$(function () {
    var api_token = Cookies.get('token');
    // 禁用上传按钮
    var $btn = $('#btn-file-hws').button('loading');
    console.log('token: ' + api_token);
    // 判断token
    if (!api_token) {
        location.href = 'index.html';
        return false;
    }
    // token鉴权
    $.ajax({
        type: 'GET',
        url: api_host + '/info',
        dataType: 'json',
        headers: { Authorization: api_token },
        cache: false
    })
    .done(function(data) {
        data = eval(data);
        $('#logined-username').html(data.data.name);
        $btn.button('reset');
    })
    .fail(function(xhr) {
        if (xhr.status == 401) {
            $('#btn-logout').click();
            return false;
        }
    });

    // 上传按钮事件
    $('#btn-file-hws').click(function(event) {
        $('#input_file_hws').click();
    });

    $('#input_file_hws').change(function(event) {
        if ($.trim($(this).val()) != '') {
            // 获取文件对象
            var file = document.getElementById('input_file_hws').files[0];
            
            if (file) {
                // 禁用按钮
                $btn.button('loading');
                filename = filename + randomFilename();

                // 类型判断
                var type = file.name.substr(file.name.lastIndexOf('.'));
                if (type != '.zip' && type != '.rar' && type != '.7z') {
                    $.alertWithoutLay('上传失败', '仅允许上传zip, rar, 7z格式的作业文件', 'error');
                    $btn.button('reset');
                    return false;
                }
                filename = filename + type;

                // 大小判断
                if (file.size > 20*1024*1024) {
                    $.alertWithoutLay('上传失败', '文件大小限制20M以内', 'error');
                    $btn.button('reset');
                    return false;
                }
            
                // 获取sign
                $.ajax({
                    type: 'GET',
                    url: api_host + '/getUploadSign',
                    dataType: 'json',
                    headers: { Authorization: api_token },
                    cache: false
                })
                .done(function(data) {
                    data = eval(data);
                    sign = data.data;

                    // 隐藏按钮 显示进度条
                    $('.upload-note').html('正在上传...');
                    $('#btn-file-hws').fadeOut('slow', function() {
                        $('#upload-hws-progress').fadeIn('slow');
                    });

                    console.log('COS save as: ' + filename);
                    // 上传至COS
                    var formData = new FormData();
                    formData.append('op', 'upload');
                    formData.append('fileContent', file);
                    $.ajax({
                        type: 'POST',
                        url: 'http://web.file.myqcloud.com/files/v1/'+ appid + '/' + bucket_name + '/' + encodeURIComponent(filename) + '?sign=' + encodeURIComponent(sign),
                        data: formData,
                        crossDomain: true,
                        processData: false,
                        contentType: false,
                        cache: false,
                        headers: { Authorization: sign },
                        xhr: function() {
                            myXhr = $.ajaxSettings.xhr();
                            if (myXhr.upload) {
                                myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
                            }
                            return myXhr;
                        },
                        success: function() {
                            $.ajax({
                                type: 'POST',
                                url: api_host + '/submitwork',
                                dataType: 'json',
                                data: {
                                    path: filename
                                },
                                headers: { Authorization: api_token },
                                cache: false
                            })
                            .done(function(data) {
                                data = eval(data);
                                if (data.status == 200) {
                                    $('#upload-hws-progress').fadeOut('slow', function() {
                                        $('.upload-note').html('上传完成');
                                    });
                                    $.alertWithoutLay('上传成功', '你可以多次提交作业，但只有最后一次提交有效');
                                } else if (data.status == 10006) {
                                    $('#upload-hws-progress').fadeOut('slow', function() {
                                        $('.upload-note').html('本次作业上传已截止');
                                    });
                                    $.alertWithoutLay('上传失败', '本次作业上传已截止', 'error');
                                    return false;
                                } else {
                                    $('#upload-hws-progress').fadeOut('slow', function() {
                                        $('.upload-note').html('请重新上传');
                                        $('#btn-file-hws').fadeIn('slow');
                                    });
                                    $btn.button('reset');
                                    $.alertWithoutLay('上传失败', data.errmsg, 'error');
                                    return false;
                                }
                            })
                            .fail(function(xhr) {
                                $('#upload-hws-progress').fadeOut('slow', function() {
                                    $('.upload-note').html('请重新上传');
                                    $('#btn-file-hws').fadeIn('slow');
                                });
                                $btn.button('reset');
                                $.alertWithoutLay('上传失败', '呃...服务器貌似出现了点问题', 'error');
                                return false;
                            });
                        }
                    });
                })
                .fail(function() {
                    $.alertWithoutLay('呃...服务器貌似出现了点问题', '攻城狮正在解决，请稍后再试', 'error');
                    $btn.button('reset');
                    return false;
                });
            }
        }
    });

    // 登出按钮事件
    $('#btn-logout').click(function(event) {
        $.ajax({
            type: 'GET',
            url: api_host + '/logout',
            dataType: 'json',
            headers: { Authorization: api_token },
            cache: false
        })
        .always(function() {
            Cookies.remove('token');
            location.href = 'index.html';
        });
        
    });
    
});