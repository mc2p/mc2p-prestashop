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

<p class="payment_module">
    <a href="{$link->getModuleLink('mc2p', 'payment', array('token' => $static_token))}">
        <img src="{$path|escape:'htmlall'}views/img/icons/mc2p.png" title="{l s='Select among several payment methods the one that works best for you in MyChoice2Pay.' mod='mc2p'}" alt="142x38.png" width="142" height="38" /><br /><br />
        {l s='Select among several payment methods the one that works best for you in MyChoice2Pay.' mod='mc2p'}
	    <br class="clear" />
    </a>
</p>