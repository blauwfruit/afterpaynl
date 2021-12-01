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

{if isset($afterpay_result)}
	{if $afterpay_result.id == 3}
		<div class="alert alert-danger">
			<h2>{l s='Order rejected by Afterpay' mod='afterpaynl'}</h2>
			<p>{l s='The order cannot be placed, use a different payment method' mod='afterpaynl'}</p>
		</div>
		<a href="{$return_link|escape:'htmlall':'UTF-8'}" class="button button-small btn btn-default">
			<span>
				{l s='Go back to choose another payment method' mod='afterpaynl'}
			</span>
		</a>
	{else}
		<div class="alert alert-warning">
			<h2>{l s='Oops. Something did not go quiet well' mod='afterpaynl'}</h2>
			
			{foreach $afterpay_result.message as $message}
				<p>{$message.message|escape:'htmlall':'UTF-8'}</p>
				<p><small>{$message.description|escape:'htmlall':'UTF-8'}</small></p>
			{/foreach}
		</div>
	{/if}
{/if}

<div>
	{if count($error)!=0}
		<div class="alert alert-info">
			<p>{l s='Correct the following' mod='afterpaynl'}:</p>
			<ul>
				{foreach $error as $err}
					<li>{$err|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul>
		</div>
	{/if}
	<div>
	{if $customer_type == 'consumer'}
		{* AfterPay's disclaimer should not be translated *}
		<h2>AfterPay – Achteraf betalen voor consumenten (NL/BE)</h2>
		<img class="afterpaynl" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/afterpaynl/views/img/Logo-afterpay-checkout_XL.png" alt="AfterPay">
		<p>
			AfterPay voert voor <strong>{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}</strong> het volledige
			achteraf betaalproces uit. Wanneer je je bestelling afrondt via AfterPay ontvang je van hen een digitaal
			betaaloverzicht en betaal je het orderbedrag aan AfterPay. Ter goedkeuring van je verzoek om achteraf te
			betalen voert AfterPay een gegevenscontrole uit. AfterPay hanteert een strikt privacybeleid zoals
			omschreven in het privacy statement. Mocht onverhoopt jouw verzoek tot betaling met AfterPay niet
			geautoriseerd worden, dan kun je jouw bestelling natuurlijk betalen met een andere betaalmethode. Wil je
			meer informatie over achteraf betalen met AfterPay? Ga dan naar de
			<a href="https://www.afterpay.nl/zo-werkt-afterpay">website van AfterPay</a>.				
		</p>
	{/if}
	{if $customer_type == 'business'}
		{* AfterPay's disclaimer should not be translated *}
		<h2>AfterPay – Achteraf betalen voor bedrijven</h2>
		<img class="afterpaynl" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/afterpaynl/views/img/Logo-afterpay-checkout_XL.png" alt="AfterPay">
		<p>
			Bij <strong>{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}</strong> kun je achteraf betalen met
			AfterPay als zakelijke klant. Wanneer je je bestelling afrondt via AfterPay ontvang je van hen een digitaal
			betaaloverzicht en betaal je het orderbedrag aan AfterPay. Ter goedkeuring van je verzoek om achteraf te
			betalen voert AfterPay een gegevenscontrole uit. AfterPay hanteert een strikt privacybeleid zoals omschreven
			in het privacy statement. Mocht onverhoopt jouw verzoek tot betaling met AfterPay niet geautoriseerd worden,
			dan kun je jouw bestelling natuurlijk betalen met een andere betaalmethode. Wil je meer informatie over
			achteraf betalen met AfterPay? Ga dan naar de
			<a href="https://www.afterpay.nl/zo-werkt-afterpay">website van AfterPay</a>.
		</p>
	{/if}
	</div>
	<div>
		<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post">
			<table class="afterpaynl">
				{foreach $fields as $field}

					{if $field.type == 'radio'}
						
						<tr>
							<td>
								<label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name}</label>
								</td>
							<td>
								{foreach $field.options as $key => $value}
									  <label for="{$field.field_id|escape:'htmlall':'UTF-8'}_{$key|escape:'htmlall':'UTF-8'}">
										<input
											type="radio"
											name="{$field.field_id|escape:'htmlall':'UTF-8'}"
									  		id="{$field.field_id|escape:'htmlall':'UTF-8'}_{$key|escape:'htmlall':'UTF-8'}"
									  		value="{$key|escape:'htmlall':'UTF-8'}" 
									  		{if $field.value == $key}checked{/if}>
									  	{$value|escape:'htmlall':'UTF-8'}
									  </label>
								{/foreach}
							</td>
						</tr>		
					{/if}
					
					{if $field.type == 'date'}
						<tr>
							<td><label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name}</label></td>
							<td><input
									type='date'
									name="{$field.field_id|escape:'htmlall':'UTF-8'}"
									value="{$field.value|escape:'htmlall':'UTF-8'}"
{* 									max="{$field.max|escape:'htmlall':'UTF-8'}"
									min="{$field.min|escape:'htmlall':'UTF-8'}"
 *}									></td>
						</tr>
					{/if}
					{if $field.type == 'text'}
						<tr>
							<td><label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name}</label></td>
							<td><input type='text' name="{$field.field_id|escape:'htmlall':'UTF-8'}" value="{$field.value|escape:'htmlall':'UTF-8'}"></td>
						</tr>
					{/if}

					{if $field.type == 'checkbox'}
						<tr>
							<td>
							  <input type="checkbox" class="form-control" name="{$field.field_id|escape:'htmlall':'UTF-8'}" {$field.value}>
							</td>
							<td>
								<label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name nofilter}</label>
							</td>
						</tr>		
					{/if}

				{/foreach}
			</table>
			<input type="hidden" name="secure_key" value="{$secure_key|escape:'htmlall':'UTF-8'}">
			<input type="submit" name="accept_and_complete_personal_details" value="{l s='Complete order' mod='afterpaynl'}" class="btn-default-afterpay hideOnSubmit">
			<a href="{$cancel|escape:'htmlall':'UTF-8'}" class="go_back">{l s='Choose another payment method' mod='afterpaynl'}</a>
			<style type="text/css">
			</style>
		</form>
	</div>
</div>
