<div class='main pure-g'><div class="pure-u-1">
    <form class='pure-form pure-form-aligned diary-form'>
    <fieldset>
    <legend style="font-weight: bold;border-bottom: 0;">
        <?=$upgradeTitle.'...'?>
    </legend>
    <div class='pure-control-group'>
        <button type="button" class="pure-button pure-button-primary" id="appUpgrade">升级 <i class="fa fa-angle-double-up"></i></button>
        <button type="button" class="pure-button cancel">返回</button>
    </div>

    <div class='pure-control-group hide text-left' id='result' style="text-align:center;margin:50px auto;">
        wfaffwfwq
    </div>
    </fieldset>
</form>
</div></div>
<script type="text/javascript">
    jQuery(function($){
        $('#appUpgrade').click(function(){
            $.ajax({
                type: 'get',
                url: baseUrl+'?mod=application&act=upgrade',
                success: function(str){
                    if(str.code == '1'){
                        $('#result').html(str.message);
                        $('#result').show();
                    }
                },
                dataType: 'json'
            });
        });
    })
</script>