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

{if isset($title)}
    <h1>{$title|escape:'htmlall':'UTF-8'}</h1>
{else}
    <h1>{l s='Oops. Something did not go quiet well' mod='afterpaynl'}</h1>
{/if}

<div class="alert alert-warning">
    {if isset($message) && count($message)}
        {foreach $message as $value}
            <li>{$value|escape:'htmlall':'UTF-8'}</li>                  
        {/foreach}
    {/if}
</div>

<a href="{$return_link|escape:'htmlall':'UTF-8'}" class="button button-small btn btn-default">
    <span>
        {l s='Go back and correct the necessary details' mod='afterpaynl'}
    </span>
</a>