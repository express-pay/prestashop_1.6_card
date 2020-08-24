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

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Shopping cart is empty.' mod='expresspay_card'}</p>
{else}
	<h3>{l s='Check payment' mod='expresspay_card'}</h3>

	<div class='row expresspay_card_confirm'>
		<div class='col-xs-12 col-md-2'>
			<p><img src="{$image_path}visa.jpg" alt="{l s='bank cards' mod='expresspay_card'}"/></p>
			<p><img src="{$image_path}mastercard.jpg" alt="{l s='bank cards' mod='expresspay_card'}"/></p>
		</div>
		<div class='col-xs-12 col-md-10'>
			<p>{l s='You have chosen to pay by bank ware.' mod='expresspay_card'}</p>
			<p>{l s='Here is a short summary of your order:' mod='expresspay_card'}</p>
			<p>
				{l s='Total Price:' mod='expresspay_card'} <span>{$total}</span>
			</p>
			<p>
				{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='expresspay_card'}
			</p>
		</div>
	</div>

	<form action="{$action}" method="post" class='expresspay_card_confirm' id="expresspay_card_confirm">

		{foreach from=$request_param key=k item=v}
			<input type="hidden" value="{$v}" id="{$k}" name="{$k}" />
		{/foreach}

	</form>

	<div class='expresspay_card_navigation'>
		<p class="cart_navigation clearfix" id="cart_navigation">
			<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive">
				<i class="icon-chevron-left"></i>
				{l s='Other payment methods' mod='expresspay_card'}
			</a>
			<input type="button" id="expresspay_card_submit_button" value="{l s='I confirm my order' mod='expresspay_card'}" class="button btn btn-default button-medium"/>
		</p>
	</div>

	
	<script type='text/javascript'>
		var failUrl = $('#FailUrl').val();

		$('input#expresspay_card_submit_button').click(function(e){
		{if $create_order_after_payment}
			
			$('form#expresspay_card_confirm').submit();
			
		{else}
			$.ajax({
				type: "POST",
				url: '{$save_order_link}' + '&cart_id=' + $("#AccountNo").val(),
				success: function(msg){
					if(parseInt(msg))
					{
						$("#AccountNo").val(msg);
						$('form#expresspay_card_confirm').submit();
					}
					else{
						window.location = failUrl;
					}
				}
			});

		{/if}
		});
		
	</script>
	
{/if}