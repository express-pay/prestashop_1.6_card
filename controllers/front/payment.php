<?php

/**
 * @since 1.0.0
 */
class expresspay_cardpaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left  = false;
        $this->display_column_right = false;

        parent::initContent();

        $control = (int) Tools::getValue('control');
		$cart    = $this->context->cart;

		$config = json_decode(Configuration::get("EXPRESSPAY_CARD_CONFIG"), true);

		if(!$this->validation_currency())
		{
			$this->context->smarty->assign([
				'message' => ''
			]);
			$this->setTemplate('payment_error.tpl');
			return;
		}

		if(!$this->validation_setting($config))
		{
			$this->context->smarty->assign([
				'message' => ''
			]);
			$this->setTemplate('payment_error.tpl');
			return;
		}

		if(!$this->validation_authorized())
		{
			$this->context->smarty->assign([
				'message' => ''
			]);
			$this->setTemplate('payment_error.tpl');
			return;
		}

		$customer = new Customer($cart->id_customer);

		if(!$this->validation_customer($customer))
		{
			Tools::redirect('index.php?controller=order&step=1');
			return;
		}
		
		$link = $config['testing_mode'] ? $config['test_api_url'] : $config['api_url'];
		$link .= 'web_cardinvoices'; 

		$account_no = $cart->id;
		$amount = $cart->getOrderTotal(true, Cart::BOTH);
		

		$currency = $this->context->currency;

		/*if($config['create_order_after_payment'])
		{
			$account_no = $cart->id;
		}
		else
		{
			$this->module->validateOrder((int)$cart->id, 10, $amount, $this->module->displayName, NULL, "", (int)$currency->id, false, $customer->secure_key);
			$account_no = $this->module->currentOrder;
		}*/
		 
		$amount = str_replace('.',',', $amount);

		if (!empty($control)) {
            $cart = new Cart($control);
		}

		$request_params = array(
							'ServiceId' => $config['service_id'] ,
							'AccountNo' => $account_no,
							'Amount' => $amount,
							'Currency' => 933,
							'Signature' => '',
							'ReturnType' => 'redirect',
							'ReturnUrl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->module->name.'/validation?action=success',
							'FailUrl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->module->name.'/validation?action=fail',
							'Info' => $config['info_message']						
						);

		$request_params['Signature'] = $this->compute_signature_add_invoice($config['token'], $request_params, $config['send_secret_word']);
		
		$this->context->smarty->assign([
            'nbProducts' 		=> $cart->nbProducts(),
			'cust_currency' 	=> $cart->id_currency,
			'currencies' 		=> $this->module->getCurrency((int)$cart->id_currency),
			'total' 			=> $cart->getOrderTotal(true, Cart::BOTH),
			'isoCode' 			=> $this->context->language->iso_code,
			'chequeName' 		=> $this->module->chequeName,
			'chequeAddress' 	=> Tools::nl2br($this->module->address),
			'this_path' 		=> $this->module->getPathUri(),
			'this_path_ssl' 	=> Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->module->name.'/',
			'total' 			=> $cart->getOrderTotal(true, Cart::BOTH),
			'currencies' 		=> $this->module->getCurrency((int)$cart->id_currency),
			'image_path'		=> $this->module->getPathUri() . 'views/img/',
			'action'			=> $link,
			'request_param' 	=> $request_params,
			'save_order_link' 	=> Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->module->name.'/validation?action=save_order',
			'create_order_after_payment' => $config['create_order_after_payment']
		]);


		$this->setTemplate('payment_execution.tpl');
	}


	private function compute_signature_add_invoice($token, $request_params, $secret_word) 
	{
		$secret_word = trim($secret_word);
		$normalized_params = array_change_key_case($request_params, CASE_LOWER);
		$api_method = array(
			"serviceid",
			"accountno",
			"expiration",
			"amount",
			"currency",
			"info",
			"returnurl",
			"failurl",
			"language",
			"sessiontimeoutsecs",
			"expirationdate",
			"returntype"
		);

		$result = $token;

		foreach ($api_method as $item)
			$result .= ( isset($normalized_params[$item]) ) ? $normalized_params[$item] : '';

		$hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

		return $hash;
	}

	private function validation_currency()
	{
		return $this->context->currency->iso_code_num == 933;
	}

	private function validation_setting($config)
	{
		return isset($config['token']) && isset($config['service_id']);
	}

	private function validation_authorized()
	{
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'expresspay_card')
			{
				$authorized = true;
				break;
			}

		return $authorized;
	}
	
	private function validation_customer($customer)
	{
		return Validate::isLoadedObject($customer);
	}

}