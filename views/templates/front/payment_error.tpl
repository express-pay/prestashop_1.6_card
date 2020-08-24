{*
 * @author    ООО "ТриИнком"
 * @copyright Copyright (c) 2019 express-pay.by
 *
*}
{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='expresspay_card'}">{l s='Checkout' mod='expresspay_card'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Pay by Bank Ware' mod='expresspay_card'}
{/capture}

<h2>{l s='Order summary' mod='expresspay_card'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<div class='row'>
    <div class='col-xs-12'>
        <h2 style='color:red;'>{l s='Payment error' mod='expresspay_card'}</h2>
        <p style='font-size: 1.5em;'>{l s='An error occurred while trying to pay. Contact the technical support of the online store.' mod='expresspay_card'}</p>
    </div>
</div>

<div class='expresspay_card_navigation'>
    <p class="cart_navigation clearfix" id="cart_navigation">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive">
            <i class="icon-chevron-left"></i>
            {l s='Other payment methods' mod='expresspay_card'}
        </a>
    </p>
</div>