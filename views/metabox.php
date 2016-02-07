<?php if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<form>
    <div class="misc-pub-section">
        <span><b><?= __('Who can see :', $this->textDomainString) ?> </b></span>
        <select name="GB_D_meta[whoCanSee]">
            <option <?= selected($this->meta['whoCanSee'], 'everyone', false) ?>
                value="everyone"><?= __('Everyone', $this->textDomainString) ?></option>
            <option <?= selected($this->meta['whoCanSee'], 'onlyUser', false) ?>
                value="onlyUser"><?= __('Only User', $this->textDomainString) ?></option>
        </select>
    </div>
    <div class="misc-pub-section">
        <span><b><?= __('Display Mode :', $this->textDomainString) ?></b></span>
        <select name="GB_D_meta[displayMode]">
            <option <?= selected($this->meta['displayMode'], 'window', false) ?>
                value="window"><?= __('Window', $this->textDomainString) ?></option>
            <option <?= selected($this->meta['displayMode'], 'bar', false) ?>
                value="bar"><?= __('Bar', $this->textDomainString) ?></option>
        </select>
    </div>
    <div class="clear"></div>
    <div class="misc-pub-section curtime">
        <span id="timestamp"><b><?= __('Last display date :', $this->textDomainString) ?></b></span>
        <br/>
        <input type="text" maxlength="2" size="2" value="<?= $date["day"] ?>" name="GB_D_date[day]" id="jj">
        <select name="GB_D_date[month]" id="mm">
            <?= $monthOptionList ?>
        </select>
        <input type="text" maxlength="4" size="4" value="<?= $date["year"] ?>" name="GB_D_date[year]" id="aa">@<input
            type="text" maxlength="2" size="2" value="<?= $date["hour"] ?>" name="GB_D_date[hour]" id="hh">:<input
            type="text" maxlength="2" size="2" value="<?= $date["minute"] ?>" name="GB_D_date[minute]" id="mn">
    </div>
    <div class="misc-pub-section">
        <span><b><?= __('Type :', $this->textDomainString) ?></b></span>
        <div class="alert">
            <input type="radio" <?= checked($this->meta['type'], "", false) ?> name="GB_D_meta[type]"
                   value=""><?= __('Default', $this->textDomainString) ?>
        </div>
        <div class="alert alert-white">
            <input type="radio" <?= checked($this->meta['type'], "alert-white", false) ?> name="GB_D_meta[type]"
                   value="alert-white"><?= __('White', $this->textDomainString) ?>
        </div>
        <div class="alert alert-error">
            <input type="radio" <?= checked($this->meta['type'], "alert-error", false) ?> name="GB_D_meta[type]"
                   value="alert-error"><?= __('Error', $this->textDomainString) ?>
        </div>
        <div class="alert alert-info">
            <input type="radio" <?= checked($this->meta['type'], "alert-info", false) ?> name="GB_D_meta[type]"
                   value="alert-info"><?= __('Info', $this->textDomainString) ?>
        </div>
        <div class="alert alert-success">
            <input type="radio" <?= checked($this->meta['type'], "alert-success", false) ?> name="GB_D_meta[type]"
                   value="alert-success"><?= __('Success', $this->textDomainString) ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="misc-pub-section misc-pub-section-last">
        <span><b><?= __('No Border :', $this->textDomainString) ?></b></span>
        <input type="checkbox" name="GB_D_meta[noBorder]" <?= checked($this->meta['noBorder'], 'on', false) ?> />
    </div>
    <!--<div class="misc-pub-section misc-pub-section-last">
        <span><b><?= __('Display Time :', $this->textDomainString) ?></b></span>
        <input type="text" name="GB_D_meta[displayTime]" value="<?= $this->meta['displayTime'] ?>" />
    </div>
    -->
</form>