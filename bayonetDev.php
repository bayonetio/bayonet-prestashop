<?php

if (!defined('_PS_VERSION_'))
	exit;

#688c9f4a-e653-42ee-ad2a-53d6345b4118 - sandbox bayo
#829f9696-0388-4c77-af77-bdc367cfa692 - sandbox js

require_once('vendor/autoload.php');

include_once(_PS_MODULE_DIR_.'bayonetDev/sdk/BayonetClient.php');

//use Bayonet\BayonetClient;

class BayonetDev extends PaymentModule
{

	private $_html = '', $bayonet;
	protected $errors;

	public function __construct()
	{
		$this->name = 'bayonetDev';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'Nazli';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		$this->need_instance = 1;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Bayonet E-commerce Plugin');
		$this->description = $this->l('This plugin will validate order details before placing it.');
	}

	public function install()
	{
		Configuration::updateValue('BAYONET_API_VERSION', null);
		Configuration::updateValue('BAYONET_API_MODE', 0);
		Configuration::updateValue('BAYONET_API_TEST_KEY', null);
		Configuration::updateValue('BAYONET_API_LIVE_KEY', null);
		Configuration::updateValue('BAYONET_API_JS_TEST_KEY', null);
		Configuration::updateValue('BAYONET_API_JS_LIVE_KEY', null);
		Configuration::updateValue('BAYONET_API_JS_VERSION', null);

		include (dirname(__FILE__).'/sql/install.php');

		if (
			!parent::install()
		)
			return false;
		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('BAYONET_LIVE_MODE');
		Configuration::deleteByName('BAYONET_API_VERSION');
		Configuration::deleteByName('BAYONET_API_MODE');
		Configuration::deleteByName('BAYONET_API_TEST_KEY');
		Configuration::deleteByName('BAYONET_API_LIVE_KEY');
		Configuration::deleteByName('BAYONET_API_JS_TEST_KEY');
		Configuration::deleteByName('BAYONET_API_JS_LIVE_KEY');
		Configuration::deleteByName('BAYONET_API_JS_VERSION');

		include (dirname(__FILE__).'sql/uninstall.php');

		$query = "SELECT * FROM "._DB_PREFIX_."order_state WHERE module_name ='$this->name'";
        $records = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if (count($records) > 0) {
            foreach ($records as $record) {
                $orderState = new OrderState($record['id_order_state']);        
                $orderState->delete();
            }
        }

		if( !parent::uninstall()
		)
			return false;
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

			if (empty(Tools::getValue('BAYONET_API_VERSION'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API version</div>';
			}

			if (empty(Tools::getValue('BAYONET_API_JS_TEST_KEY'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API JS sandbox key</div>';
			}

			if (empty(Tools::getValue('BAYONET_API_JS_LIVE_KEY'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API JS live key</div>';
			}

			if (empty(Tools::getValue('BAYONET_API_JS_VERSION'))) {
				$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API JS version</div>';
			}

			require_once(__DIR__ .'/sdk/Requests.php');

			if (empty($this->errors)) {
				$this->bayonet = new BayonetClient([
					'api_key' => Tools::getValue('BAYONET_API_TEST_KEY'),
					'version' => Tools::getValue('BAYONET_API_VERSION')
				]);

				$this->bayonet->consulting([
					'body' => $requests['consulting'],
					'on_success' => function($response) {

					},
					'on_failure' => function($response) {
						if ($response->reason_code == 11) {
							$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Api Test Key :'.$response->reason_message.'</div>';
						}
					}
				]);
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
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API version'),
					'name' => 'BAYONET_API_VERSION',
					'label' => $this->l('Bayonet API version'),
				),
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API JS sandbox key'),
					'name' => 'BAYONET_API_JS_TEST_KEY',
					'label' => $this->l('Bayonet API JS sandbox key'),
				),
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API JS live key'),
					'name' => 'BAYONET_API_JS_LIVE_KEY',
					'label' => $this->l('Bayonet API JS live key'),
				),
				array(
					'col' => 3,
					'type' => 'text',
					'desc' => $this->l('Enter Bayonet API JS version'),
					'name' => 'BAYONET_API_JS_VERSION',
					'label' => $this->l('Bayonet API JS version'),
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
            'BAYONET_API_VERSION' => Configuration::get('BAYONET_API_VERSION', null),
            'BAYONET_API_JS_TEST_KEY' => Configuration::get('BAYONET_API_JS_TEST_KEY', null),
            'BAYONET_API_JS_LIVE_KEY' => Configuration::get('BAYONET_API_JS_LIVE_KEY', null),
            'BAYONET_API_JS_VERSION' => Configuration::get('BAYONET_API_JS_VERSION', null),
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
}