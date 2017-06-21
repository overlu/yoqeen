<div class='main pure-g'><div class="pure-u-1">
    <form class='pure-form pure-form-aligned diary-form' enctype="multipart/form-data" method='post' accept-charset="utf-8" id='appForm'>
    <fieldset>
    <legend style="font-weight: bold;border-bottom: 0;">
        <a href="<?=YQ::baseUrl('application')?>" style='font-weight: normal;'>全部应用</a>
        <span style='margin-left: 1em;'>新增应用</span>
        <a href="<?=YQ::baseUrl('application/lock')?>" style='margin-left: 1em;font-weight: normal;'>锁定应用</a>
        <a href="<?=YQ::baseUrl('application/remove')?>" style='margin-left: 1em;font-weight: normal;'>移除应用</a>
    </legend>
    <div class='pure-control-group'>
        <label>App Name：</label><input required="required" class='pure-u-5-6 pure-u-md-5-12' type='text' value='' id='appname' name='appname'>
    </div>
    <div class='pure-control-group hide' id='appResult'>
        <label>Result ：</label><div class='pure-box' id='appResultMsg' style="display: inline-block;"></div>
    </div>
    <div class='pure-control-group' style='margin-top:50px;'>
        <button type="submit" class="pure-button pure-button-primary" id="appSubmit">确定 <i class="fa fa-plus"></i></button>
        <button type="button" class="pure-button cancel">取消</button>
    </div>
    </fieldset>
</form>
</div></div>
<script type="text/javascript">
    jQuery(function($){
        $('#appForm').submit(function(e){
            e.preventDefault();
            $('#appSubmit').call(baseUrl+'?mod=application&act=createApplication', $(this),function(str){
                if(str.code == '1'){
                    $('#appResultMsg').html("<a href='"+baseUrl+str.other+"'>点击访问 "+str.other+"</a>");
                    $('#appResult').show();
                }
            });
            return false;
        });
    })
</script>