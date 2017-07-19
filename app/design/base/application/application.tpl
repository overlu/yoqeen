<div class='main pure-g'><div class="pure-u-1">
    <form class='pure-form'>
    <fieldset>
    <legend style="font-weight: bold;border-bottom: 0;">
        <span>全部应用</span>
        <a href="<?=YQ::baseUrl('application/add')?>" style='margin-left: 1em;font-weight: normal;'>新增应用</a>
        <a href="<?=YQ::baseUrl('application/lock')?>" style='margin-left: 1em;font-weight: normal;'>锁定应用</a>
        <a href="<?=YQ::baseUrl('application/remove')?>" style='margin-left: 1em;font-weight: normal;'>移除应用</a>
    </legend>
    <?php foreach($apps as $app): 
          if(in_array($app, ['application','admin'])) continue;
    ?>
    <div class='pure-control-group' style="margin-top: 1em;box-sizing: border-box;height: 32px;line-height: 32px;">
        <label style="width: 200px;display: inline-block;text-align: left;margin:0;"><?=ucfirst($app)?></label>
        <div class='pure-box' style="margin-left: 2em;display: inline-block;">
            <span style="width:60px;display:inline-block;color:<?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'#108ee9':'#ff0000'?>"><?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'Locked':'Unlocked'?></span>
            <a href="<?=YQ::appUrl($app)?>" style='width:150px;box-sizing: border-box;display:inline-block;'>View</a>
            <a href="<?=YQ::baseUrl('application/lock').'?app='.$app?>" style='width:150px;box-sizing: border-box;display:inline-block;'><?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'Unlock':'Lock'?></a>
            <a href="<?=YQ::baseUrl('application/remove').'?app='.$app?>" style='width:150px;box-sizing: border-box;display:inline-block;'>Remove</a>
        </div>
    </div>
    <?php endforeach; ?>
    </fieldset>
</form>
</div></div>
<style type="text/css">
    .pure-control-group:hover { background: #ececec; }
    .pure-box a:hover { font-size: 1.6em;font-weight: bold; }
</style>