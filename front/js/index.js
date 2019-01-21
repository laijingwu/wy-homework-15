$(function() {
	$('#form-login').submit(function() {
		var $btn = $('#btn-login').button('loading');
		$('#login-username').attr('disabled', 'disabled');
		$('#login-password').attr('disabled', 'disabled');

		var username = $('#login-username').val();
		var password = $('#login-password').val();
		$.ajax({
			url: api_host + '/login/' + encodeURIComponent($.trim(username)) + '/' + encodeURIComponent($.trim(password)),
			type: 'get',
			dataType: 'json'
		})
		.done(function(data) {
			data = eval(data);
            switch (data.status) {
                case 200:
                	Cookies.set('token', data.data, {
                		expires: 1,
                		path: '/',
                		domain: 'hws.test.com',
                        secure: false
                	});
                    location.href = 'upload.html';
                    break;
                case 10002:
                case 10003:
                    $.alertWithoutLay('手滑...用户名或密码错误', '请检查您的用户名或密码是否输入正确', 'error');
                    break;
                default:
					$.alertWithoutLay('呃...貌似出现了点未知错误', '请稍后再试', 'error');
                    break;
            }
		})
		.fail(function() {
            $.alertWithoutLay('呃...服务器貌似出现了点问题', '攻城狮正在解决，请稍后再试', 'error');
		})
		.always(function() {
            $btn.button('reset');
            $('#login-username').val('').removeAttr('disabled').focus();
            $('#login-password').val('').removeAttr('disabled');
		});

		return false;
	});
});
