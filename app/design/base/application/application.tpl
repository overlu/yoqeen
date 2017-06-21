<div class='main pure-g'><div class="pure-u-1">
    <form class='pure-form'>
    <fieldset>
    <legend style="font-weight: bold;border-bottom: 0;">
        <span>全部应用</span>
        <a href="<?=YQ::baseUrl('application/add')?>" style='margin-left: 1em;font-weight: normal;'>新增应用</a>
        <a href="<?=YQ::baseUrl('application/lock')?>" style='margin-left: 1em;font-weight: normal;'>锁定应用</a>
        <a href="<?=YQ::baseUrl('application/remove')?>" style='margin-left: 1em;font-weight: normal;'>移除应用</a>
    </legend>
    <?php foreach($apps as $app): ?>
    <div class='pure-control-group' style="margin-top: 1em;">
        <label style="width: 200px;display: inline-block;text-align: left;"><?=ucfirst($app)?></label>
        <div class='pure-box' style="margin-left: 2em;display: inline-block;">
            <span style="width:60px;display:inline-block;color:<?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'#108ee9':'#ff0000'?>"><?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'Locked':'Unlocked'?></span>
            <a href="<?=YQ::appUrl($app)?>" style='width:50px;display:inline-block;margin-left: 2em;'>View</a>
            <a href="<?=YQ::baseUrl('application/lock').'?app='.$app?>" style='width:50px;display:inline-block;margin-left: 2em;'><?=is_file(APP.DS.'code'.DS.$app.DS.'.lock')?'Unlock':'Lock'?></a>
            <a href="<?=YQ::baseUrl('application/remove').'?app='.$app?>" style='width:50px;display:inline-block;margin-left: 2em;'>Remove</a>
        </div>
    </div>
    <?php endforeach; ?>
    </fieldset>
</form>
</div></div>