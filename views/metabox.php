<?php if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<form>
    <div class="misc-pub-section">
        <span><b><?= __('Who can see :', GB_D_textDomainString) ?> </b></span>
        <select name="noticeMetaData[whoCanSee]">
            <option <?= selected($notice->postMeta['whoCanSee'], 'everyone', false) ?>
                value="everyone"><?= __('Everyone', GB_D_textDomainString) ?></option>
            <option <?= selected($notice->postMeta['whoCanSee'], 'onlyUser', false) ?>
                value="onlyUser"><?= __('Only User', GB_D_textDomainString) ?></option>
        </select>
    </div>
    <div class="misc-pub-section">
        <span><b><?= __('Display Mode :', GB_D_textDomainString) ?></b></span>
        <select name="noticeMetaData[displayMode]">
            <option <?= selected($notice->displayMode, 'window', false) ?>
                value="window"><?= __('Window', GB_D_textDomainString) ?></option>
            <option <?= selected($notice->displayMode, 'bar', false) ?>
                value="bar"><?= __('Bar', GB_D_textDomainString) ?></option>
        </select>
    </div>
    <div class="clear"></div>
    <div class="misc-pub-section curtime">
        <span id="timestamp"><b><?= __('Last display date :', GB_D_textDomainString) ?></b></span>
        <br/>
        <input type="text" maxlength="2" size="2" value="<?= $date["day"] ?>" name="noticeExpireDate[day]" id="jj">
        <select name="noticeExpireDate[month]" id="mm">
            <?= createMonthOptionList($selectedMonth)?>
        </select>
        <input type="text" maxlength="4" size="4" value="<?= $date["year"] ?>" name="noticeExpireDate[year]" id="aa">@<input
            type="text" maxlength="2" size="2" value="<?= $date["hour"] ?>" name="noticeExpireDate[hour]" id="hh">:<input
            type="text" maxlength="2" size="2" value="<?= $date["minute"] ?>" name="noticeExpireDate[minute]" id="mn">
    </div>

    <div class="misc-pub-section">
        <span><b><?= __('Type :', GB_D_textDomainString) ?></b></span>
        <div class="notice-white">
            <input type="radio" <?= checked($notice->color, "notice-white", false) ?> name="noticeMetaData[color]"
                   value="notice-white"><?= __('White', GB_D_textDomainString) ?>
        </div>
        <div class="notice-red">
            <input type="radio" <?= checked($notice->color, "notice-red", false) ?> name="noticeMetaData[color]"
                   value="notice-red"><?= __('Red', GB_D_textDomainString) ?>
        </div>
        <div class="notice-blue">
            <input type="radio" <?= checked($notice->color, "notice-blue", false) ?> name="noticeMetaData[color]"
                   value="notice-blue"><?= __('Blue', GB_D_textDomainString) ?>
        </div>
        <div class="notice-green">
            <input type="radio" <?= checked($notice->color, "notice-green", false) ?> name="noticeMetaData[color]"
                   value="notice-green"><?= __('Green', GB_D_textDomainString) ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="misc-pub-section">
        <span><b><?= __('No Border :', GB_D_textDomainString) ?></b></span>
        <input type="checkbox" name="noticeMetaData[noBorder]" <?= checked(@$notice->postMeta['noBorder'], 'on', false) ?> />
    </div>
    <div class="misc-pub-section">
        <span><b><?= __('Size :', GB_D_textDomainString) ?></b></span>
        <select name="noticeMetaData[size]">
		    <option <?= selected('xLarge',$notice->size)?> value="xLarge"><?= __('xLarge', GB_D_textDomainString) ?></option>
		    <option <?= selected('large',$notice->size)?> value="large"><?= __('Large', GB_D_textDomainString) ?></option>
		    <option <?= selected('medium',$notice->size)?> value="medium"><?= __('Medium', GB_D_textDomainString) ?></option>
		    <option <?= selected('small',$notice->size)?> value="small"><?= __('Small', GB_D_textDomainString) ?></option>
        </select>
    </div>
    <div class="misc-pub-section misc-pub-section-last">
        <span><b><?= __( 'Add time limit', GB_D_textDomainString ) ?></b></span>
        <input type="checkbox" id="checkTimeLimit" <?= checked( ! is_null( $notice->displayTime ), true, false ) ?> >
        <script>
            var checkTimeLimit = jQuery('#checkTimeLimit');
            if (checkTimeLimit.is(':checked')) {
                checkTimeLimit.after('<div id="displayTimeForm">' +
                    '<span><b><?= __( "Display Time :", GB_D_textDomainString ) ?></b></span>' +
                    '<input style="width: 45px" type="text" name="noticeMetaData[displayTime]"' +
                    'value="<?= is_null( $notice->displayTime ) ? $notice::$defaultDisplayTime : $notice->displayTime ?>"/>' +
                    '</div>');
            }
            checkTimeLimit.change(function () {
                if (jQuery(this).is(':checked')) {
                    jQuery(this).after('<div id="displayTimeForm">' +
                        '<span><b><?= __( "Display Time :", GB_D_textDomainString ) ?></b></span>' +
                        '<input style="width: 45px" type="text" name="noticeMetaData[displayTime]"' +
                        'value="<?= is_null( $notice->displayTime ) ? $notice::$defaultDisplayTime : $notice->displayTime ?>"/>' +
                        '</div>')
                } else {
                    jQuery('#displayTimeForm').remove();
                }

            })
        </script>


    </div>

</form>