<?php
/**
 * @since 1.0.0
 */
class expresspay_cardvalidationModuleFrontController extends ModuleFrontController
{
	public function postProcess(){
		
		$сonfig = json_decode(Configuration::get("EXPRESSPAY_CARD_CONFIG"), true);

		if($_REQUEST['action'] == 'save_order')
		{
			if($сonfig['create_order_after_payment'])
			{
				echo 'error';
			}
			else
			{
				$cart = new Cart($_REQUEST['cart_id']);

				$customer = new Customer($cart->id_customer);

				$currency = $this->context->currency;
				$this->module->validateOrder((int)$cart->id, 1, $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, "", (int)$currency->id, false, $customer->secure_key);
				
				echo $this->module->currentOrder;
			}
			die();
		}

		$this->display_column_left  = false;
        $this->display_column_right = false;

		parent::initContent();
		
		$this->display_column_left  = false;
        $this->display_column_right = false;

		parent::initContent();

		$сonfig = json_decode(Configuration::get("EXPRESSPAY_CARD_CONFIG"), true);

		if(isset($_REQUEST['Signature']) != $this->compute_signature_request($config['token'], $_REQUEST['ExpressPayAccountNumber'], $config['send_secret_word']))
		{
			$this->load_fale_page();
			return;
		}

		if($_REQUEST['action'] == 'success')
		{
			if($сonfig['create_order_after_payment'])
			{
				$cart = new Cart($_REQUEST['ExpressPayAccountNumber']);

				$customer = new Customer($cart->id_customer);

				$currency = $this->context->currency;
				$this->module->validateOrder((int)$cart->id, 2, $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, "", (int)$currency->id, false, $customer->secure_key);
			}
			else
			{
				$history = new OrderHistory($_REQUEST['ExpressPayAccountNumber']);
				
				$history->changeIdOrderState(2, $history->id_order);
			}
			$this->setTemplate('payment_success.tpl');
		}
		else
		{
			$this->load_fale_page();
			return;
		}
	}

	public function initContent()
	{
		
		$this->display_column_left  = false;
        $this->display_column_right = false;

		parent::initContent();

		$this->load_fale_page();
			return;
			
		$сonfig = json_decode(Configuration::get("EXPRESSPAY_CARD_CONFIG"), true);

		if(isset($_REQUEST['Signature']) != $this->compute_signature_request($config['token'], $_REQUEST['ExpressPayAccountNumber'], $config['send_secret_word']))
		{
			$this->load_fale_page();
			return;
		}

		if($_REQUEST['action'] == 'success')
		{
			if($сonfig['create_order_after_payment'])
			{
				$cart = new Cart($_REQUEST['ExpressPayAccountNumber']);

				$customer = new Customer($cart->id_customer);

				$currency = $this->context->currency;
				
				$this->module->validateOrder((int)$cart->id, 2, $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, "", (int)$currency->id, false, $customer->secure_key);
			}
			else
			{
				$history = new OrderHistory($_REQUEST['ExpressPayAccountNumber']);
				
				$history->changeIdOrderState(2, $history->id_order);
			}
			$this->setTemplate('payment_success.tpl');
		}
		else
		{
			$this->load_fale_page();
			return;
		}
	}

	private function compute_signature_request($token, $accountNo, $secret_word) {
		$secret_word = trim($secret_word);

		$result = $token . $accountNo;

		$hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

		return $hash;
	}

	private function load_fale_page()
	{
		$this->setTemplate('payment_error.tpl');
	}

}
