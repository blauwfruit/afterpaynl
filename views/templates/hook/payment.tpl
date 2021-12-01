{*
*   AfterPay Netherlands
*
*   Do not copy, modify or distribute this document in any form.
*
*   @copyright  Copyright (c) 2013-2021 blauwfruit (http://www.blauwfruit.nl)
*   @license    Proprietary Software
*   @author     Matthijs <matthijs@blauwfruit.nl>
*   @category   payment
*
*}

{if $cart->getOrderTotal() < 5}
	<a href="" class="afterpaynl">
		{l s='Minimum amount required in order to pay Afterpay:' mod='afterpaynl'} {convertPrice price=2}
	</a>
{else}
	<p class="payment_module" id="afterpaynl_payment_button">
		<a href="{$link->getModuleLink('afterpaynl', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" class="afterpaynl" title="{l s='Pay Afterpay' mod='afterpaynl'}"
			style="background: url({$modules_dir|escape:'htmlall':'UTF-8'}afterpaynl/views/img/Logo-afterpay-checkout_L.png) 9px 36px no-repeat;background-size: 80px;">
			{l s='Pay with Afterpay' mod='afterpaynl'}
			<span>{l s='(order will be processed directly, payment can be made later)' mod='afterpaynl'}</span>
		</a>
	</p>
{/if}
