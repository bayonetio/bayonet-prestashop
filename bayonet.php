<?php

if (!defined('_PS_VERSION_'))
	exit;

#688c9f4a-e653-42ee-ad2a-53d6345b4118 - sandbox bayo
#829f9696-0388-4c77-af77-bdc367cfa692 - sandbox js

require_once('vendor/autoload.php');

include_once(_PS_MODULE_DIR_.'bayonet/sdk/BayonetClient.php');
include_once(_PS_MODULE_DIR_.'bayonet/sdk/Countries.php');

class Bayonet extends PaymentModule
{

	private $_html = '', $bayonet;
	protected $errors, $dataToInsert, $order;
	public $response;

	public function __construct()
	{
		$this->name = 'bayonet';
		$this->tab = 'payment_security';
		$this->version = '1.0.0';
		$this->author = 'Nazli';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		$this->need_instance = 1;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Bayonet E-commerce Plugin');
		$this->description = $this->l('This plugin will validate order details for fraud.');

		$this->statuses = array('Accept', 'Reject', 'Review');
		$this->colors = array('#00b301', '#bf1a00', '#e7c600');

		$this->table_name = $this->name;
	}

	public function install()
	{
		Configuration::updateValue('BAYONET_API_MODE', 0);
		Configuration::updateValue('BAYONET_API_TEST_KEY', null);
		Configuration::updateValue('BAYONET_API_LIVE_KEY', null);

		include (dirname(__FILE__).'/sql/install.php');

		$this->addOrderStatus();

		if (
			!parent::install() ||
			!$this->createTab() ||
			!$this->registerHook('displayHeader') ||
			!$this->registerHook('displayOrderConfirmation') ||
			!$this->registerHook('actionPaymentCCAdd') ||
			!$this->registerHook('actionValidateOrder') ||
			!$this->registerHook('displayAdminOrder') ||
			!$this->registerHook('displayPaymentReturn') ||
			!$this->registerHook('actionPaymentConfirmation') ||
			!$this->registerHook('actionObjectOrderPaymentAddBefore') ||
			!$this->registerHook('actionObjectOrderAddBefore')
		)
			return false;
		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('BAYONET_API_MODE');
		Configuration::deleteByName('BAYONET_API_TEST_KEY');
		Configuration::deleteByName('BAYONET_API_LIVE_KEY');

		include (dirname(__FILE__).'sql/uninstall.php');

		$query = "SELECT * FROM "._DB_PREFIX_."order_state WHERE module_name ='$this->name'";
        $records = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if (count($records) > 0) {
            foreach ($records as $record) {
                $orderState = new OrderState($record['id_order_state']);        
                $orderState->delete();
            }
        }

		if( !parent::uninstall() ||
			!$this->eraseTab()
		)
			return false;
		return true;
	}

	public function addOrderStatus()
	{
		for ($i = 0; $i < count($this->statuses); $i++) {
			$orderState = new OrderState();
			$orderState->name = array();
			$orderState->module_name = $this->name;
			$orderState->send_email = false;
			$orderState->color = $this->colors[$i];
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = true;
			$orderState->invoice = false;
			$orderState->paid = false;
			foreach (Language::getLanguages() as $language) {
				$orderState->template[$language['id_lang']] = 'payment';
				$orderState->name[$language['id_lang']] = $this->statuses[$i];
			}
			$orderState->add();
		}
	}

	private function createTab()
	{
		$tab = new Tab();
		$tab->active = 1;
		$languages = Language::getLanguages(false);
		if(is_array($languages))
		{
			foreach ($languages as $language)
			{
				$tab->name[$language['id_lang']] = $this->displayName;
			}
		}
		$tab->class_name = 'Admin'.ucfirst($this->name);
		$tab->module = $this->name;
		$tab->id_parent = 0;

		return (bool)$tab->add();
	}

	private function eraseTab()
	{
		$id_tab = (int)Tab::getIdFromClassName('Admin'.ucfirst($this->name));
		if($id_tab)
		{
			$tab = new Tab($id_tab);
			$tab->delete();
		}
		return true;
	}

	public function getContent()
	{
		$this->errors = '';

		if (((bool)Tools::isSubmit('submitBayonetModule')) == true && !empty(Tools::getValue('save')))
		{
			$posted_data = $this->getConfigFormValues();

			if (empty(Tools::getValue('BAYONET_API_TEST_KEY'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API sandbox key</div>';
			}

			if (empty(Tools::getValue('BAYONET_API_LIVE_KEY'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API live key</div>';
			}


			require_once(__DIR__ .'/sdk/TestRequest.php');

			if (empty($this->errors)) {
				$this->bayonet = new BayonetClient([
					'api_key' => Tools::getValue('BAYONET_API_TEST_KEY'),
					'version' => Tools::getValue('BAYONET_API_VERSION')
				]);

				$this->bayonet->consulting([
					'body' => $request['consulting'],
					'on_success' => function($response) {

					},
					'on_failure' => function($response) {
						if ($response->reason_code == 12) {
							$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>API Test Key: '.$response->reason_message.'</div>';
						}
					}
				]);

				if(empty($this->errors))
				{
					$this->bayonet = new BayonetClient([
						'api_key' => Tools::getValue('BAYONET_API_LIVE_KEY'),
						'version' => Tools::getValue('BAYONET_API_VERSION')
					]);

					$this->bayonet->consulting([
						'body' => $request['consulting'],
						'on_success' => function($response) {
						},
						'on_failure' => function($response) {
							// if ($response->reason_code == 12) {
							// 	$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>API Live Key: '.$response->reason_message.'</div>';
							// }
						}
					]);
				}

			}

			if(empty($this->errors)) {
				$this->errors = $this->postProcess();
			}
		}

		$this->context->smarty->assign('error_msgs', $this->errors);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/config.tpl');
		return $output.$this->renderForm();
	}

	protected function renderForm() 
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitBayonetModule';
		$helper->current_index = $this->context->link->getAdminLink('AdminModules', false.'$configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFormValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		$inputs = $this->getConfigForm();
		return $helper->generateForm(array($inputs[0]));
	}

	protected function getConfigForm()
	{
		$inputs = array();

		$inputs[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Bayonet Settings'),
				'icon' => 'icon-cogs',
			),
			'input' => array(
				array(
					'type' => 'switch',
					'label' => $this->l('Live Mode'),
					'name' => 'BAYONET_API_MODE',
					'desc' => $this->l('Use Bayonet in live mode'),
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
				),
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API sandbox key'),
					'name' => 'BAYONET_API_TEST_KEY',
					'label' => $this->l('Bayonet API sandbox key'),
				),
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API live key'),
					'name' => 'BAYONET_API_LIVE_KEY',
					'label' => $this->l('Bayonet API live key'),
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'save',
			),
		);

		return $inputs;
	}

	public function getConfigFormValues()
	{
		return array(
            'BAYONET_API_MODE' => Configuration::get('BAYONET_API_MODE', 0),
            'BAYONET_API_TEST_KEY' => Configuration::get('BAYONET_API_TEST_KEY', null),
            'BAYONET_API_LIVE_KEY' => Configuration::get('BAYONET_API_LIVE_KEY', null),
        );
	}

	protected function postProcess()
	{
		$forms_values = $this->getConfigFormValues();
		foreach (array_keys($forms_values) as $key) {
			Configuration::updateValue($key, Tools::getValue($key));
		}

		return $this->_html .= $this->displayConfirmation($this->l('Settings Updated'));
	}

	public function hookActionValidateOrder($params)
	{

		include_once(_PS_MODULE_DIR_.'bayonet/sdk/Paymethods.php');

		$this->order = $params['order'];
		$cart = $this->context->cart;
		$address_delivery = new Address((int)$cart->id_address_delivery);
		$address_invoice = new Address((int)$cart->id_address_invoice);
		$state_delivery = new State((int)$address_delivery->id_state);
		$country_delivery = new Country((int)$address_delivery->id_country);
		$state_invoice = new State((int)$address_invoice->id_state);
		$country_invoice = new Country((int)$address_invoice->id_country);
		$customer = $this->context->customer;
		$currency = $this->context->currency;

		$products = $cart->getProducts();
		$product_list = array();

		foreach($products as $product)
		{
			$products_list[] = [
				"product_id" => $product['id_product'],
				"product_name" => $product['name'],
				"product_price" => $product['price'],
				"product_category" => $product['category']
			];
		}

		$request = [
			'channel' => 'ecommerce',
			'consumer_name' => $customer->firstname.' '.$customer->lastname,
			"consumer_internal_id" => $customer->id,
			//"cardholder_name" => $customer->firstname.' '.$customer->lastname,
			//"payment_method" => 'card',
			"transaction_amount" => $cart->getOrderTotal(),
			"currency_code" => $currency->iso_code,
			"telephone" => $address_invoice->phone,
		 	//"card_number" => "4242424242424242",
		 	"email" => $customer->email,
		 	"payment_gateway" => $this->order->module,
		 	"shipping_address" => [
		 	  "line_1" => $address_delivery->address1,
		 	  "line_2" => $address_delivery->address2,
		 	  "city" => $address_delivery->city,
		 	  "state" => $state_delivery->name,
		 	  "country" => convert_country_code($country_delivery->iso_code),
		 	  "zip_code" => $address_delivery->postcode
		 	],
		 	"billing_address" => [
		 	  "line_1" => $address_invoice->address1,
		 	  "line_2" => $address_invoice->address2,
		 	  "city" => $address_invoice->city,
		 	  "state" => $state_invoice->name,
		 	  "country" => convert_country_code($country_invoice->iso_code),
		 	  "zip_code" => $address_invoice->postcode
		 	],
		 	"products" => $products_list,
			"order_id" => (int)$this->order->id
		];

		foreach ($paymentMethods as $key => $value) {
			if ($this->order->module == $key)
			{
				$request['payment_method'] = $value;
				if ($this->order->module == 'paypalmx')
					$request['payment_gateway'] = 'paypal';  
			}
		}

		$request['payment_gateway'] = 'stripe';

		$this->bayonet = new BayonetClient([
					'api_key' => Configuration::get('BAYONET_API_TEST_KEY'),
					'version' => Configuration::get('BAYONET_API_VERSION')
				]);

		$this->bayonet->consulting([
			'body' => $request,
			'on_success' => function($response) {
				$this->dataToInsert = array(
					'id_cart' => $this->context->cart->id,
					'order_no' => $this->order->id,
					'status' => $response->decision,
					'bayonet_tracking_id' => $response->bayonet_tracking_id,
					'consulting_api' => 1,
					'consulting_api_response' => json_encode(array(
						'reason_code' => $response->reason_code,
						'tracking_id' => $response->bayonet_tracking_id
					)),
					'is_executed' => 1,
				);

				Db::getInstance()->insert('bayonet', $this->dataToInsert);

				if ($response->decision == 'decline')
				{
					$this->module = Module::getInstanceByName('bayonet');
					Tools::redirect($this->context->link->getModuleLink($this->module->name,'rejected', array()		));
				}
			},
			'on_failure' => function($response) {
				$this->dataToInsert = array(
					'id_cart' => $this->context->cart->id,
					'order_no' => $this->order->id,
					'status' => $response->decision,
					'bayonet_tracking_id' => $response->bayonet_tracking_id,
					'consulting_api' => 0,
					'consulting_api_response' => json_encode(array(
						'reason_code' => $response->reason_code,
					)),
					'is_executed' => 1,
				);

				Db::getInstance()->insert('bayonet', $this->dataToInsert);
				
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Api Test Key :'.$response->reason_message.'</div>';
				}
		]);
	}
}