<div class='main pure-g'><div class="pure-u-1">
    <form class='pure-form pure-form-aligned diary-form' enctype="multipart/form-data" method='post' accept-charset="utf-8" id='applForm'>
    <fieldset>
    <legend style="font-weight: bold;border-bottom: 0;">
        <a href="<?=YQ::baseUrl('application')?>" style='font-weight: normal;'>全部应用</a>
        <a href="<?=YQ::baseUrl('application/add')?>" style='margin-left: 1em;font-weight: normal;'>新增应用</a>
        <span style='margin-left: 1em;'>锁定应用</span>
        <a href="<?=YQ::baseUrl('application/remove')?>" style='margin-left: 1em;font-weight: normal;'>移除应用</a>
    </legend>
    <div class='pure-control-group'>
        <label>App Name：</label><input required="required" class='pure-u-5-6 pure-u-md-5-12' type='text' value='<?=$app?>' id='appname' name='appname'>
    </div>
    <div class='pure-control-group hide' id='applResult'>
        <label>Result ：</label><div class='pure-box' style="display: inline-block;" id='applResultMsg'></div>
    </div>
    <div class='pure-control-group' style='margin-top:50px;'>
        <button type="submit" class="pure-button pure-button-primary" id="applSubmit">确定 <i class="fa fa-lock"></i></button>
        <button type="button" class="pure-button cancel">取消</button>
    </div>
    </fieldset>
</form>
</div></div>
<script type="text/javascript">
    jQuery(function($){
        $('#applForm').submit(function(e){
            e.preventDefault();
            $('#applSubmit').call(baseUrl+'?mod=application&act=lockApplication', $(this),function(str){
                if(str.code == '1'){
                    $('#applResultMsg').html($('#appname').val()+(str.other=='lock'?' 已经锁定':' 已经解锁'));
                    $('#applResult').show();
                }
            });
            return false;
        });
    })
</script>