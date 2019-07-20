<?php

/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once('vendor/autoload.php');

include_once(_PS_MODULE_DIR_.'bayonet/sdk/BayonetClient.php');
include_once(_PS_MODULE_DIR_.'bayonet/sdk/Countries.php');

class Bayonet extends PaymentModule
{
    private $_html = '';
    private $bayonet;
    private $bayonetFingerprint;
    protected $errors;
    protected $dataToInsert;
    protected $order;
    protected $bayoID;
    public $response;

    public function __construct()
    {
        $this->name = 'bayonet';
        $this->tab = 'payment_security';
        $this->version = '1.0.0';
        $this->author = 'Bayonet.io';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_,);

        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bayonet E-commerce Plugin');
        $this->description = $this->l('This plugin will validate order details for fraud.');

        $this->statuses = array('Accept', 'Reject', 'Review',);
        $this->colors = array('#00b301', '#bf1a00', '#e7c600',);

        $this->table_name = $this->name;
    }
    
    /**
     * Bayonet's module installation
     * Adds configuration values, registers hooks, creates table in database,
     * creates order statuses and adds a tab in the back office.
     *
     * @return boolean installation result
     */
    public function install()
    {
        Configuration::updateValue('BAYONET_API_MODE', 0);
        Configuration::updateValue('BAYONET_API_TEST_KEY', null);
        Configuration::updateValue('BAYONET_API_LIVE_KEY', null);
        Configuration::updateValue('BAYONET_JS_TEST_KEY', null);
        Configuration::updateValue('BAYONET_JS_LIVE_KEY', null);
        Configuration::updateValue('BAYONET_BACKFILL_MODE', 0);

        include(_PS_MODULE_DIR_.'bayonet/sql/install.php');

        $this->addOrderStatus();

        if (
            !parent::install() ||
            !$this->createTab() ||
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionPaymentConfirmation') ||
            !$this->registerHook('actionObjectOrderPaymentAddBefore') ||
            !$this->registerHook('displayPaymentTop')
        ) {
            return false;
        }
        
        return true;
    }

    /**
     * Bayonet's module uninstallation
     * Removes configuration values, deletes table from database,
     * deletes order statuses and removes tab from back office.
     *
     * @return boolean uninstallation result
     */
    public function uninstall()
    {
        Configuration::deleteByName('BAYONET_API_MODE');
        Configuration::deleteByName('BAYONET_API_TEST_KEY');
        Configuration::deleteByName('BAYONET_API_LIVE_KEY');
        Configuration::deleteByName('BAYONET_JS_TEST_KEY');
        Configuration::deleteByName('BAYONET_JS_LIVE_KEY');
        Configuration::deleteByName('BAYONET_BACKFILL_MODE');

        include(_PS_MODULE_DIR_.'bayonet/sql/uninstall.php');

        $query = 'SELECT * FROM `'._DB_PREFIX_.'order_state` WHERE `module_name` = '."'$this->name'";
        $records = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if (count($records) > 0) {
            foreach ($records as $record) {
                $orderState = new OrderState($record['id_order_state']);
                $orderState->delete();
            }
        }

        if (!parent::uninstall() ||
            !$this->eraseTab()
        ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Bayonet's statuses installation
     * Adds the Bayonet statuses; executed when installing the module.
     */
    private function addOrderStatus()
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
    
    /**
     * Bayonet's tab installation
     * Adds the Bayonet tab in the back office; executed when installing the module.
     *
     * @return boolean installation result
     */
    private function createTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->displayName;
            }
        }
        $tab->class_name = 'Admin'.ucfirst($this->name);
        $tab->module = $this->name;
        $tab->id_parent = 0;

        return (bool)$tab->add();
    }
    
    /**
     * Bayonet's tab uninstallation
     * Removes the Bayonet tab from the back office.
     *
     * @return boolean uninstallation result
     */
    private function eraseTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('Admin'.ucfirst($this->name));
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        
        return true;
    }
    
    /**
     * Displays the configuration screen of the module
     * and checks if the configuration values are valid before saving.
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $this->errors = '';

        if (((bool)Tools::isSubmit('submitBayonetModule')) == true && !empty(Tools::getValue('save'))) {
            $posted_data = $this->getConfigFormValues();

            if (empty(Tools::getValue('BAYONET_API_TEST_KEY'))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API Sandbox Key</div>';
            }

            if (empty(Tools::getValue('BAYONET_API_LIVE_KEY'))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Bayonet API Live Key</div>';
            }

            if (empty(Tools::getValue('BAYONET_JS_TEST_KEY'))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Device Fingerprint API Sandbox Key</div>';
            }

            if (empty(Tools::getValue('BAYONET_JS_LIVE_KEY'))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please enter Device Fingerprint API Live Key</div>';
            }

            require_once(__DIR__ .'/sdk/TestRequest.php');

            if (empty($this->errors)) {
                $this->bayonet = new BayonetClient([
                    'api_key' => Tools::getValue('BAYONET_API_TEST_KEY'),
                ]);

                $this->bayonet->consulting([
                    'body' => $request['consulting'],
                    'on_success' => function ($response) {
                    },
                    'on_failure' => function ($response) {
                        if ($response->reason_code == 12) {
                            $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>API Test Key: '.$response->reason_message.'</div>';
                        }
                    },
                ]);

                if (empty($this->errors)) {
                    $this->bayonet = new BayonetClient([
                        'api_key' => Tools::getValue('BAYONET_API_LIVE_KEY'),
                    ]);

                    $this->bayonet->consulting([
                        'body' => $request['consulting'],
                        'on_success' => function ($response) {
                        },
                        'on_failure' => function ($response) {
                            // if ($response->reason_code == 12) {
                            // 	$this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>API Live Key: '.$response->reason_message.'</div>';
                            // }
                        },
                    ]);
                }
            }

            if (empty($this->errors)) {
                $this->errors = $this->postProcess();
            }
        }

        $this->context->smarty->assign('error_msgs', $this->errors);
        $this->context->smarty->assign('backfill_mode', Configuration::get('BAYONET_BACKFILL_MODE'));

		if (!empty(Configuration::get('BAYONET_API_TEST_KEY')) &&
			!empty(Configuration::get('BAYONET_API_LIVE_KEY')) &&
            !empty(Configuration::get('BAYONET_JS_TEST_KEY')) &&
            !empty(Configuration::get('BAYONET_JS_LIVE_KEY'))) {
            $this->context->smarty->assign('backfill_enable', 1);
		} else {
			$this->context->smarty->assign('backfill_enable', 0);
		}

		Media::addJsDef(array('urlBackfill' => $this->context->link->getModuleLink($this->name,'backfill',array())));
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/config.tpl');
		$output1 = $this->context->smarty->fetch($this->local_path.'views/templates/admin/backfill.tpl');

		$this->context->controller->addJS($this->_path.'views/js/back.js');
		$this->context->controller->addCSS($this->_path.'views/css/back.css');

		return $output.$this->renderForm().$output1;
    }
    
    /**
     * Renders the form to display in the configuration screen.
     *
     * @return string HTML content
     */
    public function renderForm()
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
    
    /**
     * Defines all the settings that will be shown in the configuration screen.
     *
     * @return array with the settings
     */
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
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'desc' => $this->l('Enter Bayonet API Sandbox Key'),
                    'name' => 'BAYONET_API_TEST_KEY',
                    'label' => $this->l('Bayonet API Sandbox Key'),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'desc' => $this->l('Enter Bayonet API Live Key'),
                    'name' => 'BAYONET_API_LIVE_KEY',
                    'label' => $this->l('Bayonet API Live Key'),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'desc' => $this->l('Enter Device Fingerprint API Sandbox Key'),
                    'name' => 'BAYONET_JS_TEST_KEY',
                    'label' => $this->l('Device Fingerprint API Sandbox Key'),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'desc' => $this->l('Enter Device Fingerprint API Live Key'),
                    'name' => 'BAYONET_JS_LIVE_KEY',
                    'label' => $this->l('Device Fingerprint API Live Key'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'save',
            ),
        );
        
        return $inputs;
    }
    
    /**
     * Gets the configuration values currently stored in the database.
     *
     * @return array with configuration values
     */
    public function getConfigFormValues()
    {
        return array(
            'BAYONET_API_MODE' => Configuration::get('BAYONET_API_MODE'),
            'BAYONET_API_TEST_KEY' => Configuration::get('BAYONET_API_TEST_KEY'),
            'BAYONET_API_LIVE_KEY' => Configuration::get('BAYONET_API_LIVE_KEY'),
            'BAYONET_JS_TEST_KEY' => Configuration::get('BAYONET_JS_TEST_KEY'),
            'BAYONET_JS_LIVE_KEY' => Configuration::get('BAYONET_JS_LIVE_KEY'),
        );
    }
    
    /**
     * Saves the configuration values to the database.
     *
     * @return string HTML content
     */
    public function postProcess()
    {
        $forms_values = $this->getConfigFormValues();
        foreach (array_keys($forms_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return $this->_html .= $this->displayConfirmation($this->l('Settings Updated'));
    }

    /**
     * Sets the key for the device fingerprinting script execution.
     * Adds the php controller definition to process in the back office
     * the fingerprint token generated in the front office.
     *
     * @param object $params Object
     */
    public function hookDisplayPaymentTop($params)
    {
        if (1 == Configuration::get('BAYONET_API_MODE')) {
            Media::addJsDef(array('bayonet_js_key' => Configuration::get('BAYONET_JS_LIVE_KEY')));
        } else {
            Media::addJsDef(array('bayonet_js_key' => Configuration::get('BAYONET_JS_TEST_KEY')));
        }
        Media::addJsDef(array('urlFingerprint' => $this->context->link->getModuleLink($this->name,'fingerprint',array())));
        $this->context->controller->addJS($this->_path.'views/js/fingerprint.js');
    }
    
    /**
     * Bayonet's order validation
     * Creates the request body and executes a consulting call to the API,
     * then it saves the corresponding data in the module table depending on the response.
     * This is done after an order has been created.
     *
     * @param object $params Object
     */
    public function hookActionValidateOrder($params)
    {
        include_once(_PS_MODULE_DIR_.'bayonet/sdk/Paymethods.php');
        
        $this->order = $params['order'];
        $cart = $this->context->cart;
        $address_delivery = new Address((int)$cart->id_address_delivery);
        $address_invoice = new Address((int)$cart->id_address_invoice);
        $state_delivery = 0 != $address_delivery->id_state ? (new State((int)$address_delivery->id_state))->name : "NA";
        $country_delivery = new Country((int)$address_delivery->id_country);
        $state_invoice = 0 != $address_invoice->id_state ? (new State((int)$address_invoice->id_state))->name : "NA";
        $country_invoice = new Country((int)$address_invoice->id_country);
        $customer = $this->context->customer;
        $currency = $this->context->currency;
        $products = $cart->getProducts();
        $product_list = array();

        foreach ($products as $product) {
            $products_list[] = [
                "product_id" => $product['id_product'],
                "product_name" => $product['name'],
                "product_price" => $product['price'],
                "product_category" => $product['category'],
            ];
        }

        $request = [
            'channel' => 'ecommerce',
            'consumer_name' => $customer->firstname.' '.$customer->lastname,
            'consumer_internal_id' => $customer->id,
            'transaction_amount' => $cart->getOrderTotal(),
            'currency_code' => $currency->iso_code,
            'transaction_time' => strtotime($this->order->date_add),
            'email' => $customer->email,
            'payment_gateway' => $this->order->module,
            'shipping_address' => [
              'line_1' => $address_delivery->address1,
              'line_2' => $address_delivery->address2,
              'city' => $address_delivery->city,
              'state' => $state_delivery,
              'country' => convert_country_code($country_delivery->iso_code),
              'zip_code' => $address_delivery->postcode,
            ],
            'billing_address' => [
              'line_1' => $address_invoice->address1,
              'line_2' => $address_invoice->address2,
              'city' => $address_invoice->city,
              'state' => $state_invoice,
              'country' => convert_country_code($country_invoice->iso_code),
              'zip_code' => $address_invoice->postcode,
            ],
            "products" => $products_list,
            "order_id" => (int)$this->order->id,
        ];

        if ('' != $address_invoice->phone) {
            $request['telephone'] = $address_invoice->phone;
        } else {
            $request['telephone'] = null;
        }

        if ($this->context->cookie->__isset('fingerprint') & (!empty($this->context->cookie->__get('fingerprint')))) {
          $this->bayonetFingerprint = $this->context->cookie->__get('fingerprint');
          $this->context->cookie->__unset('fingerprint');
          $request['bayonet_fingerprint_token'] = $this->bayonetFingerprint;
        } 
        
        foreach ($paymentMethods as $key => $value) {
            if ($this->order->module == $key) {
                $request['payment_method'] = $value;
                if ('paypalmx' == $this->order->module) {
                    $request['payment_gateway'] = 'paypal';
                }
                if ('openpayprestashop' == $this->order->module) {
                    $request['payment_gateway'] = 'openpay';
                }
                if ('conektaprestashop' == $this->order->module) {
                    $request['payment_gateway'] = 'conekta';
                }
                if ('bankwire' == $this->order->module) {
                    $request['payment_gateway'] = 'stripe';
                }
                if ('cheque' == $this->order->module) {
                    $request['payment_gateway'] = 'conekta';
                }
            }
        }

        $this->bayonet = new BayonetClient([
                    'api_key' => Configuration::get('BAYONET_API_TEST_KEY'),
                ]);

        $this->bayonet->consulting([
            'body' => $request,
            'on_success' => function ($response) {
                $triggered = '';
                $rules = $response->rules_triggered->dynamic;
                foreach ($rules as $rule) {
                    $triggered .= '- ' . $rule . '<br>';
                }
                $this->dataToInsert = array(
                    'id_cart' => $this->context->cart->id,
                    'order_no' => $this->order->id,
                    'decision' => $response->decision,
                    'rules_triggered' => $triggered,
                    'bayonet_tracking_id' => $response->bayonet_tracking_id,
                    'consulting_api' => 1,
                    'consulting_api_response' => json_encode(
                    	array(
                            'reason_code' => $response->reason_code,
                            'reason_message' => $response->reason_message,
                        )
                    ),
                    'is_executed' => 1,
                );
                Db::getInstance()->insert('bayonet', $this->dataToInsert);
            },
            'on_failure' => function ($response) {
                $message = str_replace("'", "-", $response->reason_message);
                $this->dataToInsert = array(
                    'id_cart' => $this->context->cart->id,
                    'order_no' => $this->order->id,
                    'decision' => $response->decision,
                    'bayonet_tracking_id' => $response->bayonet_tracking_id,
                    'consulting_api' => 0,
                    'consulting_api_response' => json_encode(
                        array(
                            'reason_code' => $response->reason_code,
                            'reason_message' => $message,
                        )
                    ),
                    'is_executed' => 1,
                );
                Db::getInstance()->insert('bayonet', $this->dataToInsert);
            },
        ]);
    }
    
    /**
     * Bayonet's order feedback
     * Creates the request body and executes a feedback call to the API
     * then it saves the corresponding data in the module table depending on the response.
     * This is done after a payment has been confirmed for an order.
     *
     * @param object $params Object
     */
    public function hookActionPaymentConfirmation($params)
    {
        $bayoOrder = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'bayonet` WHERE `order_no` = '.(int)$params['id_order']);
        
        if ($bayoOrder) {
            $this->bayoID = $bayoOrder['id_bayonet'];
            if (!empty($bayoOrder['bayonet_tracking_id'])) {
                $updateRequest = [
                    'bayonet_tracking_id' => $bayoOrder['bayonet_tracking_id'],
                    'transaction_status' => 'success',
                ];
                
                $this->bayonet = new BayonetClient([
                        'api_key' => Configuration::get('BAYONET_API_TEST_KEY'),
                    ]);

                $this->bayonet->updateTransaction([
                    'body' => $updateRequest,
                    'on_success' => function ($response) {
                        Db::getInstance()->update(
                            'bayonet',
                            array(
                                'feedback_api' => 1,
                                'feedback_api_response' => json_encode(
                                    array(
                                    'reason_code' => $response->reason_code,
                                    'message' => $response->reason_message,
                                    )
                                ),
                            ),
                            'id_bayonet = '.(int)$this->bayoID
                        );
                    },
                    'on_failure' => function ($response) {
                        $message = str_replace("'", "-", $response->reason_message);
                        Db::getInstance()->update(
                            'bayonet',
                            array(
                                'feedback_api' => 0,
                                'feedback_api_response' => json_encode(
                                    array(
                                    'reason_code' => $response->reason_code,
                                    'message' => $message,
                                    )
                                ),
                            ),
                            'id_bayonet = '.(int)$this->bayoID
                        );
                    },
                ]);
            }
        }
    }
    
    /**
     * Displays the Bayonet information corresponding to a specific order in the back office.
     *
     * @param object $params Object
     */
    public function hookDisplayAdminOrder($params)
    {
        $displayedOrder = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'bayonet` WHERE `order_no` = ' . (int)$params['id_order']);
        
        if ($displayedOrder) {
            if (!empty($displayedOrder['bayonet_tracking_id'])) {
                $this->smarty->assign(array(
                    'not_consulting_order' => false,
                    'unprocessed_order' => false,
                    'decision' => '<span style="font-size:1.5em;font-weight:bold;color:#'.
                        (('accept' == $displayedOrder['decision']) ? '339933' : (('review' == $displayedOrder['decision']) ? 'ff7f27' : 'f00')).'">'.
                        (('accept' == $displayedOrder['decision']) ? 'ACCEPTED' : (('decline' == $displayedOrder['decision']) ? 'DECLINED' : strtoupper($displayedOrder['decision']))).'</span>',
                    'bayonet_tracking_id' => $displayedOrder['bayonet_tracking_id'],
                    'api_response' => $displayedOrder['consulting_api_response'],
                    'rules_triggered' => $displayedOrder['rules_triggered'],
                ));
            } else {
                $this->smarty->assign(array(
                    'not_consulting_order' => true,
                    'unprocessed_order' => false,
                ));    
            }
        } else {
            $this->smarty->assign(array(
                'not_consulting_order' => false,
                'unprocessed_order' => true,
            ));
        }
            
        return $this->display(__FILE__, 'admin_order.tpl');
    }
}
