{**
 * mc2p Module
 *
 * Copyright (c) 2017 MyChoice2Pay
 *
 * @category  Payment
 * @author    MyChoice2Pay, <www.mychoice2pay.com>
 * @copyright 2017, MyChoice2Pay
 * @link      https://www.mychoice2pay.com/
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * Description:
 *
 * Payment module mc2p
 *
 * --
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to hola@mychoice2pay.com so we can send you a copy immediately.
 *}

{literal}
<style type="text/css">
fieldset a {
    color:#0099ff !important;
    text-decoration:underline;
}
fieldset a:hover {
    color:#000000;
    text-decoration:underline;
}
.level1 {
    font-size:1.2em
}
.level2 {
    font-size:0.9em
}
</style>
{/literal}

<div><img src="{$sofort.dfl.image_path|escape:'htmlall':'UTF-8'}" alt="142x38.png" width="142" height="38" title="" /></div>
<form method="post" action="{$sofort.dfl.action|escape:'htmlall':'UTF-8'}">
<br />

<fieldset class="level1">
<legend>{l s='About MyChoice2Pay' mod='mc2p'}</legend>
    <b>{l s='MyChoice2Pay allows with a simple integration using several payment gateways while offering the possibility of dividing payments between several people.' mod='mc2p'}</b> <a target="_blank" href="https://www.mychoice2pay.com/"><b>{l s='mychoice2pay.com.' mod='mc2p'}</b></a><br />
</fieldset>
<br />

<fieldset class="level1">
    <legend>{l s='Setup and Configuration' mod='mc2p'}</legend>
    <b>{l s='To use MyChoice2Pay a few steps are necessary:' mod='mc2p'}</b><br /><br />
    <fieldset class="level2">
        <legend>{l s='Registration' mod='mc2p'}</legend>
        <b>{l s='In order to offer MyChoice2Pay you need a customer account. You are not a customer?' mod='mc2p'}</b>
        <a target="_blank" href="https://dashboard.mychoice2pay.com/#/register"><b>{l s='Register now!' mod='mc2p'}</b></a><br />
    </fieldset>
    <br />
    
    <fieldset class="level2">
        <legend>{l s='Module configuration' mod='mc2p'}</legend>
        <b>{l s='Please leave your MyChoice2Pay keys in the fields below:' mod='mc2p'}</b><br /><br />
        <label>{l s='Key' mod='mc2p'}</label>
        <div class="margin-form">
            <input type="text" name="MC2P_KEY" value="{$sofort.config.MC2P_KEY|escape:'htmlall':'UTF-8'}" />
            <p>{l s='Leave it blank for disabling' mod='mc2p'}</p>
        </div>
        <div class="clear"></div>
        <label>{l s='Secret Key' mod='mc2p'}</label>
        <div class="margin-form">
            <input type="text" name="MC2P_SECRET_KEY" value="{$sofort.config.MC2P_SECRET_KEY|escape:'htmlall':'UTF-8'}" />
            <p>{l s='Leave it blank for disabling' mod='mc2p'}</p>
        </div>
        <div class="clear"></div>
        <div class="margin-form clear pspace"><input type="submit" name="submitUpdate" value="{l s='Save' mod='mc2p'}" class="button" /></div>
    </fieldset>
</fieldset>
</form>
<br />

<fieldset class="level1 space">
    <legend>{l s='Help' mod='mc2p'}</legend>
    <b>{l s='For detailed instructions, please visit our' mod='mc2p'}</b> <a target="_blank" href="https://developer.mychoice2pay.com/"><b>{l s='Website' mod='mc2p'}</b></a>.<br /><br />
</fieldset>