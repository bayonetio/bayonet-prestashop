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
include_once(_PS_MODULE_DIR_.'bayonet/sdk/Paymethods.php');

class Bayonet extends PaymentModule
{
    private $_html = '';
    private $bayonet;
    private $bayonetFingerprint;
    private $response;
    private $idBlockList;
    private $whiteList;
    private $blackList;
    private $api_key;
    private $errors;
    private $dataToInsert;
    private $order;
    private $bayoID;

    public function __construct()
    {
        $this->name = 'bayonet';
        $this->tab = 'payment_security';
        $this->version = '1.0.0';
        $this->author = 'Bayonet';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');

        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bayonet Anti-Fraud');
        $this->description = $this->l('This plugin will analyze the details of new orders to detect any fraud attempt.');

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

        if (
            !parent::install() ||
            !$this->createTab() ||
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionOrderStatusUpdate') ||
            !$this->registerHook('displayPaymentTop') ||
            !$this->registerHook('displayBackOfficeHeader')
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

        if (!parent::uninstall() ||
            !$this->eraseTab()
        ) {
            return false;
        }
        
        return true;
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

            if (empty(trim(Tools::getValue('BAYONET_API_TEST_KEY')))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('Please enter Bayonet API sandbox key').'</div>';
            }

            if (empty(trim(Tools::getValue('BAYONET_API_LIVE_KEY'))) && 1 == Tools::getValue('BAYONET_API_MODE')) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('Cannot enable live mode without a Bayonet API live key').'</div>';
            }
            
            if (empty(trim(Tools::getValue('BAYONET_JS_TEST_KEY')))) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('Please enter Device Fingerprint API sandbox key').'</div>';
            }

            if (empty(trim(Tools::getValue('BAYONET_JS_LIVE_KEY'))) && 1 == Tools::getValue('BAYONET_API_MODE')) {
                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('Cannot enable live mode without a Device Fingerprint API live key').'</div>';
            }

            require_once(__DIR__ .'/sdk/TestRequest.php');

            if (empty($this->errors)) {
                if ('**********' != trim(Tools::getValue('BAYONET_API_TEST_KEY'))) {
                    $this->bayonet = new BayonetClient([
                        'api_key' => trim(Tools::getValue('BAYONET_API_TEST_KEY')),
                    ]);

                    $this->bayonet->consulting([
                        'body' => $request['consulting'],
                        'on_success' => function ($response) {
                        },
                        'on_failure' => function ($response) {
                            if (159 == $response->reason_code) {
                            } elseif (12 == $response->reason_code) {
                                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('API Sandbox Key: ').$response->reason_message.'</div>';
                            } else {
                                $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('An error occurred while validating the sandbox API key: ').$response->reason_message.'</div>';   
                            }
                        },
                    ]);
                }
            }

            if (empty($this->errors)) {
                if (!empty(trim(Tools::getValue('BAYONET_API_LIVE_KEY')))) {
                    if ('**********' != trim(Tools::getValue('BAYONET_API_LIVE_KEY'))) {
                        $this->bayonet = new BayonetClient([
                            'api_key' => trim(Tools::getValue('BAYONET_API_LIVE_KEY')),
                        ]);

                        $this->bayonet->consulting([
                            'body' => $request['consulting'],
                            'on_success' => function ($response) {
                            },
                            'on_failure' => function ($response) {
                                if (159 == $response->reason_code) {
                                } elseif (12 == $response->reason_code) {
                                    $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('API Live Key: ').$response->reason_message.'</div>';
                                } elseif (13 == $response->reason_code) {
                                    $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Error: '.$response->reason_message.'. '.$this->l('In order to be able to use Bayonet in Live Mode properly, add your IP address to the whitelist in Bayonet\'s console').'</div>';
                                } else {
                                    $this->errors .= '<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'.$this->l('An error occurred while validating the live API key: ').$response->reason_message.'</div>';   
                                }
                            },
                        ]);
                    }
                }
            }

            if (empty($this->errors)) {
                $this->errors = $this->postProcess();
            }
        }

        $this->context->smarty->assign('error_msgs', $this->errors);
        $this->context->smarty->assign('backfill_mode', Configuration::get('BAYONET_BACKFILL_MODE'));
        
        if (!empty(Configuration::get('BAYONET_API_LIVE_KEY')) &&
            !empty(Configuration::get('BAYONET_JS_LIVE_KEY')))) {
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
                    'desc' => $this->l('Use Bayonet in Live Mode'),
                    'hint' => $this->l('Enabling this setting will set the module to Live (production) Mode, disabling it will set the module to Sandbox (test) Mode'),
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
                    'hint' => $this->l('Please enter a Bayonet API sandbox key to enable Sandbox Mode'),
                    'name' => 'BAYONET_API_TEST_KEY',
                    'label' => $this->l('Bayonet API Sandbox Key'),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'hint' => $this->l('Please enter a Device Fingerprint API sandbox key'),
                    'name' => 'BAYONET_JS_TEST_KEY',
                    'label' => $this->l('Device Fingerprint API Sandbox Key'),
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'hint' => $this->l('Please enter a Bayonet API live key to enable Live Mode'),
                    'name' => 'BAYONET_API_LIVE_KEY',
                    'label' => $this->l('Bayonet API Live Key'),
                ),
                
                array(
                    'col' => 3,
                    'type' => 'text',
                    'hint' => $this->l('Please enter a Device Fingerprint API live key'),
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
        $apiTestKey = null != Configuration::get('BAYONET_API_TEST_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_API_TEST_KEY');
        $apiLiveKey = null != Configuration::get('BAYONET_API_LIVE_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_API_LIVE_KEY');
        $jsTestKey = null != Configuration::get('BAYONET_JS_TEST_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_JS_TEST_KEY');
        $jsLiveKey = null != Configuration::get('BAYONET_JS_LIVE_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_JS_LIVE_KEY');

        return array(
            'BAYONET_API_MODE' => Configuration::get('BAYONET_API_MODE'),
            'BAYONET_API_TEST_KEY' => $apiTestKey,
            'BAYONET_API_LIVE_KEY' => $apiLiveKey,
            'BAYONET_JS_TEST_KEY' => $jsTestKey,
            'BAYONET_JS_LIVE_KEY' => $jsLiveKey,
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
            Configuration::updateValue($key, trim(Tools::getValue($key)));
        }

        return $this->_html .= $this->displayConfirmation($this->l('Settings Updated'));
    }

    /**
     * Sets the key for the device fingerprinting script execution.
     * Adds the php controller definition to process in the back office
     * the fingerprint token generated in the front office.
     *
     * @param object $params
     */
    public function hookDisplayPaymentTop($params)
    {
        if (1 == Configuration::get('BAYONET_API_MODE')) {
            Media::addJsDef(array('bayonet_js_key' => Configuration::get('BAYONET_JS_LIVE_KEY')));
        } elseif (0 == Configuration::get('BAYONET_API_MODE')) {
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
     * @param object $params
     */
    public function hookActionValidateOrder($params)
    {
        if ((!Configuration::get('BAYONET_API_TEST_KEY') && 0 == Configuration::get('BAYONET_API_MODE')) || (!Configuration::get('BAYONET_API_LIVE_KEY') && 1 == Configuration::get('BAYONET_API_MODE'))) {
            return;
        }

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
            'transaction_time' => strtotime($this->order->date_add),
            'currency_code' => $currency->iso_code,
            'transaction_time' => strtotime($this->order->date_add),
            'email' => $customer->email,
            'payment_gateway' => $this->order->module,
            'shipping_address' => [
              'line_1' => $address_delivery->address1,
              'line_2' => $address_delivery->address2,
              'city' => $address_delivery->city,
              'state' => $state_delivery,
              'country' => convertCountryCode($country_delivery->iso_code),
              'zip_code' => $address_delivery->postcode,
            ],
            'billing_address' => [
              'line_1' => $address_invoice->address1,
              'line_2' => $address_invoice->address2,
              'city' => $address_invoice->city,
              'state' => $state_invoice,
              'country' => convertCountryCode($country_invoice->iso_code),
              'zip_code' => $address_invoice->postcode,
            ],
            "products" => $products_list,
            "order_id" => (int)$this->order->id,
        ];

        if (!empty($address_invoice->phone) || !empty($address_invoice->phone_mobile)) {
            if (!empty($address_invoice->phone)) {
                $request['telephone'] = $address_invoice->phone;
            } elseif (!empty($address_invoice->phone_mobile)) {
                $request['telephone'] = $address_invoice->phone_mobile;
            }
        } else {
            $request['telephone'] = null;
        }

        if ($this->context->cookie->__isset('fingerprint') & (!empty($this->context->cookie->__get('fingerprint')))) {
          $this->bayonetFingerprint = $this->context->cookie->__get('fingerprint');
          $this->context->cookie->__unset('fingerprint');
          $request['bayonet_fingerprint_token'] = $this->bayonetFingerprint;
        }

        $request['payment_method'] = getPaymentMethod($this->order, 0);


        if ('paypalmx' == $this->order->module) {
            $request['payment_gateway'] = 'paypal';
        } elseif ('openpayprestashop' == $this->order->module) {
            $request['payment_gateway'] = 'openpay';
        } elseif ('conektaprestashop' == $this->order->module) {
            $request['payment_gateway'] = 'conekta';
        }

        if (0 == Configuration::get('BAYONET_API_MODE')) {
            $this->api_key = Configuration::get('BAYONET_API_TEST_KEY');
        } elseif (1 == Configuration::get('BAYONET_API_MODE')) {
            $this->api_key = Configuration::get('BAYONET_API_LIVE_KEY');
        }

        $this->bayonet = new BayonetClient([
            'api_key' => $this->api_key,
        ]);

        $this->bayonet->consulting([
            'body' => $request,
            'on_success' => function ($response) {
                $triggered = '';
                $rulesDynamic = $response->rules_triggered->dynamic;
                $rulesCustom = $response->rules_triggered->custom;
                foreach ($rulesDynamic as $rule) {
                    $triggered .= '- ' . $rule . '<br>';
                }
                foreach ($rulesCustom as $rule) {
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
     * @param object $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        if (1 == $params['newOrderStatus']->paid)
        {
            $bayoOrder = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'bayonet` WHERE `order_no` = '.(int)$params['id_order']);

            if ($bayoOrder) {
                $this->bayoID = $bayoOrder['id_bayonet'];
                if (!empty($bayoOrder['bayonet_tracking_id']) && null == $bayoOrder['feedback_api']) {
                    $updateRequest = [
                        'bayonet_tracking_id' => $bayoOrder['bayonet_tracking_id'],
                        'transaction_status' => 'success',
                    ];

                    if (0 == Configuration::get('BAYONET_API_MODE')) {
                        $this->api_key = Configuration::get('BAYONET_API_TEST_KEY');
                    } elseif (1 == Configuration::get('BAYONET_API_MODE')) {
                        $this->api_key = Configuration::get('BAYONET_API_LIVE_KEY');
                    }

                    $this->bayonet = new BayonetClient([
                        'api_key' => $this->api_key,
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
    }

    
    /**
     * Displays the Bayonet information corresponding to a specific order in the back office.
     *
     * @param object $params
     */
    public function hookDisplayAdminOrder($params)
    {
        $displayedOrder = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'bayonet` WHERE `order_no` = ' . (int)$params['id_order']);
        $orderCustomer = Db::getInstance()->getRow('SELECT a.`email`, a.`id_customer` FROM 
            (SELECT `email`, `id_customer` FROM `' . _DB_PREFIX_ . 'customer` WHERE `id_customer` = 
            (SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'orders` where `id_order` = ' . (int)$params['id_order'] . ')) a');
        $blockListData = Db::getInstance()->getRow('SELECT `id_blocklist`, `whitelist`, `blacklist` FROM `' ._DB_PREFIX_.'bayonet_blocklist` WHERE `id_customer` = (SELECT `id_customer` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = ' . (int)$params['id_order'] . ')');

        if ($blockListData) {
            $this->idBlockList = $blockListData['id_blocklist'];
            $this->whitelist = $blockListData['whitelist'];
            $this->blacklist = $blockListData['blacklist'];
        } else {
            $this->idBlockList = 0;
            $this->whitelist = 0;
            $this->blacklist = 0;
        }
        
        if ($displayedOrder) {
            $api_response = $displayedOrder['consulting_api_response'];
            $api_response = rtrim($api_response, ',');
            $api_response = "[" . trim($api_response) . "]";
            $metadata = json_decode($api_response, true);

            if (!empty($displayedOrder['bayonet_tracking_id'])) {

                $this->smarty->assign(array(
                    'not_consulting_order' => false,
                    'unprocessed_order' => false,
                    'decision' => '<span style="font-size:1.5em;font-weight:bold;color:#'.
                        (('accept' == $displayedOrder['decision']) ? '339933' : (('review' == $displayedOrder['decision']) ? 'ff7f27' : 'f00')).'">'.
                        (('accept' == $displayedOrder['decision']) ? $this->l('ACCEPTED') : (('decline' == $displayedOrder['decision']) ? $this->l('DECLINED') : $this->l('REVIEW'))).'</span>',
                    'bayonet_tracking_id' => $displayedOrder['bayonet_tracking_id'],
                    'reason_code' => $metadata[0]['reason_code'],
                    'reason_message' => 'success' == $metadata[0]['reason_message'] ? 'Correct' : $metadata[0]['reason_message'],
                    'rules_triggered' => $displayedOrder['rules_triggered'],
                    'mailCustomer' => $orderCustomer['email'],
                    'idCustomer' => $orderCustomer['id_customer'],
                    'idBlockList' => $this->idBlockList,
                    'whitelist' => $this->whitelist,
                    'blacklist' => $this->blacklist,
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

        Media::addJsDef(array('urlBlockList' => $this->context->link->getModuleLink($this->name,'blocklist',array())));
        $this->context->controller->addJS($this->_path.'views/js/blocklist.js');
            
        return $this->display(__FILE__, 'admin_order.tpl');
    }

    /**
     * Loads the necessary css file to set the Bayonet tab icon
     *
     * @param object $params
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/'.$this->name.'_bo.css', 'all');
    }
}
