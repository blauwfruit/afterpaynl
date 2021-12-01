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
		<h2>AfterPay – Achteraf betalen (Nederland)</h2>
		<img class="afterpaynl" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/afterpaynl/views/img/Logo-afterpay-checkout_XL.png" alt="AfterPay">
		<p>
			AfterPay voert voor <strong>{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}</strong> het volledige achteraf betaalproces uit.
			Dit betekent dat je een betaaloverzicht ontvangt van AfterPay.
			Met de AfterPay App kun je dit betaaloverzicht inzien en betalen, veilig en heel eenvoudig vanaf je smartphone.
			In de online omgeving Mijn AfterPay kun je jouw betaaloverzichten vanaf je computer of tablet beheren.
			AfterPay houdt je op de hoogte via pushnotificaties & e-mail wanneer er een betaaloverzicht voor je klaar staat.
			Ter goedkeuring van je verzoek om achteraf te betalen voert AfterPay een gegevenscontrole uit.
			AfterPay hanteert een strikt privacybeleid zoals omschreven in zijn privacy statement.
			Mocht onverhoopt jouw verzoek tot betaling met AfterPay niet geautoriseerd worden, dan kun je jouw bestelling natuurlijk betalen met een andere betaalmethode.
			Je kunt voor vragen altijd contact opnemen met AfterPay.
			Voor meer informatie verwijzen wij je door naar AfterPay.
		</p>


	{/if}
	{if $customer_type == 'business'}
		<h2>AfterPay – Achteraf betalen (Nederland, bedrijven)</h2>
		<img class="afterpaynl" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/afterpaynl/views/img/Logo-afterpay-checkout_XL.png" alt="AfterPay">
		<p>
			AfterPay voert voor <strong>{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}</strong> het volledige achteraf betaalproces uit.
			Dit betekent dat u een digitaal betaaloverzicht per e-mail ontvangt van AfterPay.
			AfterPay informeert u per e-mail wanneer er een betaaloverzicht voor u klaar staat.
			Ter goedkeuring van uw verzoek om achteraf te betalen voert AfterPay een gegevenscontrole uit.
			AfterPay hanteert een strikt privacybeleid zoals omschreven in zijn privacy statement.
			Mocht onverhoopt uw verzoek tot betaling met AfterPay niet geautoriseerd worden, dan kunt u uw bestelling betalen met een andere betaalmethode.
			U kunt voor vragen altijd contact opnemen met AfterPay.
			Voor meer informatie verwijzen wij u door naar AfterPay.
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
									  		{if $field.value==$key}checked{/if}>
									  	{$value|escape:'htmlall':'UTF-8'}
									  </label>
								{/foreach}
							</td>
						</tr>		
					{/if}
					
					{if $field.type == 'date'}
						<tr>
							<td><label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name}</label></td>
							<td><input type='date' name="{$field.field_id|escape:'htmlall':'UTF-8'}" value="{$field.value|escape:'htmlall':'UTF-8'}"></td>
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
							  <input type="checkbox" name="{$field.field_id|escape:'htmlall':'UTF-8'}" {$field.value}>
							</td>
							<td>
								<label for="{$field.field_id|escape:'htmlall':'UTF-8'}">{$field.field_name}</label>
							</td>
						</tr>		
					{/if}

				{/foreach}
			</table>
			<input type="hidden" name="secure_key" value="{$secure_key|escape:'htmlall':'UTF-8'}">
			<input type="submit" name="accept_and_complete_personal_details" value="{l s='Complete order' mod='afterpaynl'}" class="btn-default-afterpay">
			<a href="{$cancel|escape:'htmlall':'UTF-8'}" class="go_back">{l s='Choose another payment method' mod='afterpaynl'}</a>
			<style type="text/css">
			</style>
		</form>
	</div>
</div>
