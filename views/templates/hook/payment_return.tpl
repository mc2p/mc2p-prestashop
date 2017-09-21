<img src="{$this_path|escape:'htmlall'}assets/images/icons/mc2p.png" /><br /><br />
{if $status == 'ok'}
	<p>
	{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='redsys'}
		<br /><br />- {l s='Payment amount.' mod='redsys'} <span class="price"><strong>{$total_to_pay|escape:'htmlall'}</strong></span>
		<br /><br />- N# <span class="price"><strong>{$id_order|escape:'htmlall'}</strong></span>
		<br /><br />{l s='An email has been sent to you with this information.' mod='redsys'}
		<br /><br />{l s='For any questions or for further information, please contact our' mod='redsys'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='redsys'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='mc2p'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='mc2p'}</a>.
	</p>
{/if}
