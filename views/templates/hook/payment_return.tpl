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

{if $status === true}
    <p>
        {l s='Your order on' mod='mc2p'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' mod='mc2p'}
        <br /><br />
        {l s='The total amount of this order is' mod='mc2p'} <span class="price">{$amount|escape:'UTF-8'}</span>
    </p>
{else}
    <p class="warning">
        {l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='mc2p'}
        <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' mod='mc2p'}</a>.
    </p>
{/if}