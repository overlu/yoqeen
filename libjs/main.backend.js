function ismobile(){ return navigator.userAgent.match(/(iPhone|iPod|Android|ios)/i); }

(function($) {
    $.message = function(message,type){
        type = arguments[0]===undefined?"warning":type;
        if(type == 'success'){
            $('div.notification #message-type-i').removeClass().addClass('fa fa-check').css("color","#5cb85c");
        }else{
            $('div.notification #message-type-i').removeClass().addClass('fa fa-warning').css("color","#c9302c");
        }
        $('div.notification span.message').html(message);
        $('div.notification-message').slideDown(300);
        setTimeout(function(){
            $('div.notification-message').slideUp(300);
        },3000);
    };

    $.uuid = function(num){
        num = arguments[0]===undefined?3:num;
        var x = "0123456789qwertyuioplkjhgfdsazxcvbnm";
        var UUID = '';
        var s = function(){
            var temp = '';
            for(var i=0;i<4;i++){ temp += x.charAt(Math.round(Math.random()*x.length));}
            return temp;
        };
        for(var i=0;i<num;i++){ UUID += s()+'-'; }
        return UUID.substring(0,UUID.length-1);
    };

    $.fn.call = function(url, form, fn){
        var t = this;
        $(t).find('i').addClass('fa-spinner');
        var formData = typeof(form[0])=='object' ? new FormData(form[0]) : form;
        if(typeof(form[0])=='object'){
            $.ajax({
            url: url,  //server script to process data
            type: 'POST',
            dataType: 'json',
            //Ajax事件
            success: function(result) {
                if(result.code == '1'){
                    if(result.data){
                        window.location = result.data;
                        return;
                    }
                }
                jQuery('div.notification span.message').html(result.message);
                if(result.code == '1'){
                    $('div.notification #message-type-i').removeClass().addClass('fa fa-check').css("color","#5cb85c");
                }else{
                    $('div.notification #message-type-i').removeClass().addClass('fa fa-warning').css("color","#c9302c");
                }
                jQuery('div.notification-message').slideDown(300,function(){
                    if(result.message == '登录状态已经过期，请先重新登录'){
                        location.reload();
                    }
                });
                setTimeout(function(){
                    jQuery('div.notification-message').slideUp(300,function(){
                        if(result.message == '登录状态已经过期，请先重新登录'){
                            location.reload();
                        }else{
                            if(typeof(fn)=='function'){
                                fn(result);
                            }
                        }
                    });
                },3000);
                $(t).find('i').removeClass('fa-spinner');
            },
            // Form数据
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });
        }else{
            jQuery.post(url,formData,function(result){
                if(result.code == '1'){
                    if(result.data){
                        window.location = result.data;
                        return;
                    }
                }
                if(typeof(fn)=='function'){
                    fn(result);
                }
                $.message(result.message,result.code=='1'?'success':'warning');
                $(t).find('i').removeClass('fa-spinner').addClass('fa-sign-in');

            },'json');
        }
    };
})(jQuery);

jQuery(function($){
    
})