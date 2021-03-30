<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__) . '/model/BayonetDb.php');
include_once(dirname(__FILE__) . '/helper/RequestHelper.php');
include_once(dirname(__FILE__) . '/helper/OrderHelper.php');

class BayonetAntiFraud extends Module
{
    protected $html = '';
    protected $postErrors = [];

    public function __construct()
    {
        $this->name = 'bayonetantifraud';
        $this->tab = 'payment_security';
        $this->version = '2.0.0';
        $this->author = 'Bayonet';

        $this->bootstrap = true;

        $this->controllers = ['backfill', 'blocklist', 'fingerprint'];

        parent::__construct();

        $this->displayName = $this->l('Bayonet Anti-Fraud');
        $this->description = $this->l('A module to analyze the details of new orders to detect fraud attempts in PrestaShop stores.');

        $this->table_name = $this->name;

        $this->tabs = [
            [
                'name' => 'Bayonet Anti-Fraud',
                'class_name' => 'AdminBayonet',
                'visible' => true,
                'parent_class_name' => 'ShopParameters'
            ]
        ];
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
        if (!parent::install() ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('actionOrderStatusUpdate') ||
            !$this->registerHook('displayPaymentTop') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('displayHeader') ||
            !BayonetDb::createTables()) {
            return false;
        }

        $this->addTabs();

        Configuration::updateValue('BAYONET_AF_ENABLE', 0);
        Configuration::updateValue('BAYONET_AF_API_MODE', 0);
        Configuration::updateValue('BAYONET_AF_API_VERSION', 'v2');
        Configuration::updateValue('BAYONET_AF_BACKFILL_MODE', 0);
        Configuration::updateValue('BAYONET_AF_API_TEST_KEY', null);
        Configuration::updateValue('BAYONET_AF_API_LIVE_KEY', null);
        Configuration::updateValue('BAYONET_AF_JS_TEST_KEY', null);
        Configuration::updateValue('BAYONET_AF_JS_LIVE_KEY', null);

        return true;
    }

    /**
     * Bayonet's module uninstallation
     * Removes configuration values and removes tab from back office.
     *
     * @return boolean uninstallation result
     */
    public function uninstall()
    {
        Configuration::deleteByName('BAYONET_AF_ENABLE');
        Configuration::deleteByName('BAYONET_AF_API_MODE');
        Configuration::deleteByName('BAYONET_AF_API_VERSION');
        Configuration::deleteByName('BAYONET_AF_BACKFILL_MODE');
        Configuration::deleteByName('BAYONET_AF_API_TEST_KEY');
        Configuration::deleteByName('BAYONET_AF_API_LIVE_KEY');
        Configuration::deleteByName('BAYONET_AF_JS_TEST_KEY');
        Configuration::deleteByName('BAYONET_AF_JS_LIVE_KEY');

        if (!parent::uninstall()) {
            return false;
        }
        
        return true;
    }

    /**
     * Bayonet's tab installation
     * Adds the Bayonet tab in the back office; executed when installing the module.
     *
     */
    public function addTabs()
    {
        $languages = Language::getLanguages(false);
        
        //Main Parent menu
        if (!(int) Tab::getIdFromClassName('AdminBayonetAntiFraud')) {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminBayonetAntiFraud";
            
            foreach ($languages as $language) {
                $parentTab->name[$language['id_lang']] = 'Bayonet Anti-Fraud';
            }
            
            $parentTab->id_parent = 0;
            $parentTab->module = '';
            $parentTab->add();
        }
        
        //Sub menu code
        if (!(int) Tab::getIdFromClassName('AdminBayonetOrders')) {
            $parentTabID = Tab::getIdFromClassName('AdminBayonetAntiFraud');
            $parentTab = new Tab($parentTabID);
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminBayonetOrders";
            $tab->name = array();
            
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $this->l('Orders Processed by Bayonet');
            }
            
            $tab->id_parent = $parentTab->id;
            $tab->module = $this->name;
            $tab->add();
        }
    }

    /**
     * Defines all the settings that will be shown in the configuration screen.
     *
     * @return array with the settings
     */
    public function createConfigForm()
    {
        $inputs = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Bayonet Anti-Fraud Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'BAYONET_AF_ENABLE',
                        'desc' => $this->l('Enable the module'),
                        'hint' => $this->l('Enabling this setting will activate the module, while disabling it will deactivate it so no orders will be processed by Bayonet'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Live Mode'),
                        'name' => 'BAYONET_AF_API_MODE',
                        'desc' => $this->l('Use Bayonet in Live Mode'),
                        'hint' => $this->l('Enabling this setting will set the module to Live (production) Mode, disabling it will set the module to Sandbox (test) Mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ]
                        ]
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('The current version of the API'),
                        'hint' => $this->l('Must not be changed unless asked by the developer'),
                        'name' => 'BAYONET_AF_API_VERSION',
                        'label' => $this->l('API Version')
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Please enter a Bayonet sandbox (test) key'),
                        'hint' => $this->l('Required to use the module in Sandbox (test) mode'),
                        'name' => 'BAYONET_AF_API_TEST_KEY',
                        'label' => $this->l('Bayonet Sandbox (test) Key')
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Please enter a Device Fingerprint sandbox (test) key'),
                        'hint' => $this->l('Required to use the module in Sandbox (test) mode'),
                        'name' => 'BAYONET_AF_JS_TEST_KEY',
                        'label' => $this->l('Device Fingerprint Sandbox (test) Key')
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Please enter a Bayonet live (production) key'),
                        'hint' => $this->l('Required to use the module in Live (production) mode'),
                        'name' => 'BAYONET_AF_API_LIVE_KEY',
                        'label' => $this->l('Bayonet Live (production) Key')
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Please enter a Device Fingerprint live (production) key'),
                        'hint' => $this->l('Required to use the module in Live (production) mode'),
                        'name' => 'BAYONET_AF_JS_LIVE_KEY',
                        'label' => $this->l('Device Fingerprint Live (production) Key')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
        
        return $inputs;
    }

    /**
     * Gets the configuration values currently stored in the database.
     *
     * @return array with configuration values
     */
    public function getConfigFormValues()
    {
        $apiTestKey = '' !== Configuration::get('BAYONET_AF_API_TEST_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_AF_API_TEST_KEY');
        $apiLiveKey = '' !== Configuration::get('BAYONET_AF_API_LIVE_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_AF_API_LIVE_KEY');
        $jsTestKey = '' !== Configuration::get('BAYONET_AF_JS_TEST_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_AF_JS_TEST_KEY');
        $jsLiveKey = '' !== Configuration::get('BAYONET_AF_JS_LIVE_KEY') ? str_repeat("*", 10) : Configuration::get('BAYONET_AF_JS_LIVE_KEY');

        return [
            'BAYONET_AF_ENABLE' => Configuration::get('BAYONET_AF_ENABLE'),
            'BAYONET_AF_API_MODE' => Configuration::get('BAYONET_AF_API_MODE'),
            'BAYONET_AF_API_VERSION' => Configuration::get('BAYONET_AF_API_VERSION'),
            'BAYONET_AF_API_TEST_KEY' => $apiTestKey,
            'BAYONET_AF_API_LIVE_KEY' => $apiLiveKey,
            'BAYONET_AF_JS_TEST_KEY' => $jsTestKey,
            'BAYONET_AF_JS_LIVE_KEY' => $jsLiveKey
        ];
    }

    /**
     * Renders the form to display in the configuration screen.
     *
     * @return string HTML content
     */
    public function renderForm()
    {
        $fields_form = $this->createConfigForm();

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $this->fields_form = [];
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure='. $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        
        return $helper->generateForm([$fields_form]);
    }

    /**
     * Displays the configuration screen of the module
     * and checks if the configuration values are valid before saving.
     *
     * @return string HTML content
     */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                } 
            }
        } else {
            $this->html .= '<br />';
        }
        
        $this->context->smarty->assign('backfill_mode', Configuration::get('BAYONET_AF_BACKFILL_MODE'));
        $this->context->controller->addCSS($this->_path.'views/css/backfill.css');
        
        if (Configuration::get('BAYONET_AF_API_LIVE_KEY') &&
            Configuration::get('BAYONET_AF_JS_LIVE_KEY') &&
            1 === (int) Configuration::get('BAYONET_AF_API_MODE')) {
            $this->context->smarty->assign('backfill_enable', 1);
        } else {
            $this->context->smarty->assign('backfill_enable', 0);
        }

        Media::addJsDef(array('urlBackfill' => $this->context->link->getModuleLink(
            $this->name,
            'backfill',
            array()
        )));
        $this->context->controller->addJS($this->_path.'views/js/backfill.js');

        $configMessage = $this->context->smarty->fetch($this->local_path.'views/templates/admin/config.tpl');
        $backfill = $this->context->smarty->fetch($this->local_path.'views/templates/admin/backfill.tpl');

        $this->html .= $configMessage.$this->renderForm().$backfill;

        return $this->html;
    }

    /**
     * Validates the configuration values in the configuration window when
     * trying to save them to the database.
     */
    public function _postValidation()
    {      
        if (Tools::isSubmit('btnSubmit')) {
            $requestHelper = new RequestHelper();

            if ((int) Tools::getValue('BAYONET_AF_ENABLE') === 1 && (Configuration::get('BAYONET_AF_API_TEST_KEY') === '' || Configuration::get('BAYONET_AF_JS_TEST_KEY') === '')) {
                $this->postErrors[] = $this->l('The module cannot be enabled if no pair of keys have been saved first. Please add at least a pair of keys (sandbox & live) before enabling the module');
            }

            if ((int) Tools::getValue('BAYONET_AF_API_MODE') === 1 && (Configuration::get('BAYONET_AF_API_LIVE_KEY') === '' || Configuration::get('BAYONET_AF_JS_LIVE_KEY') === '')) {
                $this->postErrors[] = $this->l('Cannot set the API mode to live (production) with no live (production) API keys saved. Please save your live (production) API keys first');
            }

            if (!empty(trim(Tools::getValue('BAYONET_AF_API_TEST_KEY'))) && ('**********' !== trim(Tools::getValue('BAYONET_AF_API_TEST_KEY')))) {
                $requestBody['auth']['api_key'] = Tools::getValue('BAYONET_AF_API_TEST_KEY');
                $response = $requestHelper->consulting($requestBody);

                if (isset($response->reason_code) && (int)$response->reason_code !== 101) {
                    switch ((int)$response->reason_code) {
                        case 12:
                            $this->postErrors[] = $this->l('Invalid value for the Bayonet sandbox key. Please check your key and try again');
                            break;
                        case 13:
                            $this->postErrors[] = $this->l('Bayonet sandbox key: Source IP is not valid, please add your IP to the whitelist in Bayonet\'s console');
                            break;
                        case 15:
                            $this->postErrors[] = $this->l('Bayonet sandbox key: The key you entered has expired, please generate a new key from Bayonet\'s console');
                            break;
                    }
                }
            }

            if (!empty(trim(Tools::getValue('BAYONET_AF_JS_TEST_KEY'))) && ('**********' !== trim(Tools::getValue('BAYONET_AF_JS_TEST_KEY')))) {
                $requestBody['auth']['jsKey'] = Tools::getValue('BAYONET_AF_JS_TEST_KEY');
                $response = $requestHelper->deviceFingerprint($requestBody);

                if (isset($response->reasonCode) && (int)$response->reasonCode !== 51) {
                    switch ((int)$response->reasonCode) {
                        case 12:
                            $this->postErrors[] = $this->l('Invalid value for the Device Fingerprint sandbox key. Please check your key and try again');
                            break;
                        case 15:
                            $this->postErrors[] = $this->l('Device Fingerprint sandbox key: The key you entered has expired, please generate a new key from Bayonet\'s console');
                            break;
                        case 16:
                            $this->postErrors[] = $this->l('Device Fingerprint sandbox key: Store domain is not registered, please add your store domain to the whitelist in Bayonet\'s console');
                            break;
                    }
                }
            }

            if (!empty(trim(Tools::getValue('BAYONET_AF_API_LIVE_KEY'))) && ('**********' !== trim(Tools::getValue('BAYONET_AF_API_LIVE_KEY')))) {
                $requestBody['auth']['api_key'] = Tools::getValue('BAYONET_AF_API_LIVE_KEY');
                $response = $requestHelper->consulting($requestBody);

                if (isset($response->reason_code) && (int)$response->reason_code !== 101) {
                    switch ((int)$response->reason_code) {
                        case 12:
                            $this->postErrors[] = $this->l('Invalid value for the Bayonet live key. Please check your key and try again');
                            break;
                        case 13:
                            $this->postErrors[] = $this->l('Bayonet sandbox key: Source IP is not valid, please add your IP to the whitelist in Bayonet\'s console');
                            break;
                        case 15:
                            $this->postErrors[] = $this->l('Bayonet sandbox key: The key you entered has expired, please generate a new key from Bayonet\'s console');
                            break;
                    }
                }
            }

            if (!empty(trim(Tools::getValue('BAYONET_AF_JS_LIVE_KEY'))) && ('**********' !== trim(Tools::getValue('BAYONET_AF_JS_LIVE_KEY')))) {
                $requestBody['auth']['jsKey'] = Tools::getValue('BAYONET_AF_JS_LIVE_KEY');
                $response = $requestHelper->deviceFingerprint($requestBody);

                if (isset($response->reasonCode) && (int)$response->reasonCode !== 51) {
                    switch ((int)$response->reasonCode) {
                        case 12:
                            $this->postErrors[] = $this->l('Invalid value for the Device Fingerprint live key. Please check your key and try again');
                            break;
                        case 15:
                            $this->postErrors[] = $this->l('Device Fingerprint live key: The key you entered has expired, please generate a new key from Bayonet\'s console');
                            break;
                        case 16:
                            $this->postErrors[] = $this->l('Device Fingerprint live key: Store domain is not registered, please add your store domain to the whitelist in Bayonet\'s console');
                            break;
                    }
                }
            }
        }
    }

    /**
     * Saves the configuration values to the database.
     *
     * @return string HTML content
     */
    public function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $forms_values = $this->getConfigFormValues();
            foreach (array_keys($forms_values) as $key) {
                if ('**********' !== trim(Tools::getValue($key))) {
                    Configuration::updateValue($key, trim(Tools::getValue($key)));
                }
            }
            
            $this->html .= $this->displayConfirmation($this->l('Settings updated'));
        }
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
        if (0 === (int)Configuration::get('BAYONET_AF_ENABLE') ||
           (!Configuration::get('BAYONET_AF_API_TEST_KEY') && 0 === (int)Configuration::get('BAYONET_AF_API_MODE')) ||
           (!Configuration::get('BAYONET_AF_API_LIVE_KEY') && 1 === (int)Configuration::get('BAYONET_AF_API_MODE'))) {
               return;
        }

        $apiKey = '';

        if (0 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            $apiKey = Configuration::get('BAYONET_AF_API_TEST_KEY');
        } elseif (1 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            $apiKey = Configuration::get('BAYONET_AF_API_LIVE_KEY');
        }

        $dataToInsert = [];
        $dataToUpdate = [];
        $requestHelper = new RequestHelper();
        $orderHelper = new OrderHelper();
        $order = $params['order'];
        $cart = $this->context->cart;
        $address_delivery = new Address((int)$cart->id_address_delivery);
        $address_invoice = new Address((int)$cart->id_address_invoice);
        $state_delivery = 0 !== $address_delivery->id_state ? (new State((int)$address_delivery->id_state))->name : "NA";
        $country_delivery = new Country((int)$address_delivery->id_country);
        $state_invoice = 0 !== $address_invoice->id_state ? (new State((int)$address_invoice->id_state))->name : "NA";
        $country_invoice = new Country((int)$address_invoice->id_country);
        $customer = $this->context->customer;
        $currency = $this->context->currency;
        $products = $cart->getProducts();
        $products_list = [];

        foreach ($products as $product) {
            $products_list[] = [
                'product_id' => $product['id_product'],
                'product_name' => $product['name'],
                'product_price' => $product['price'],
                'product_category' => $product['category'],
            ];
        }

        $request = [
            'auth' => [
                'api_key' => $apiKey
            ],
            'email' => $customer->email,
            'consumer_name' => $customer->firstname . ' ' . $customer->lastname,
            'consumer_internal_id' => $customer->id,
            'shipping_address' => [
              'line_1' => $address_delivery->address1,
              'line_2' => $address_delivery->address2,
              'city' => $address_delivery->city,
              'state' => $state_delivery,
              'country' => $orderHelper->convertCountryCode($country_delivery->iso_code),
              'zip_code' => $address_delivery->postcode
            ],
            'billing_address' => [
              'line_1' => $address_invoice->address1,
              'line_2' => $address_invoice->address2,
              'city' => $address_invoice->city,
              'state' => $state_invoice,
              'country' => $orderHelper->convertCountryCode($country_invoice->iso_code),
              'zip_code' => $address_invoice->postcode
            ],
            'products' => $products_list,
            'order_id' => (int)$order->id,
            'transaction_amount' => $cart->getOrderTotal(),
            'currency_code' => $currency->iso_code,
            'transaction_time' => strtotime($order->date_add),
            'payment_gateway' => $order->module,
            'channel' => 'ecommerce',
        ];

        if (!empty($address_invoice->phone) || !empty($address_invoice->phone_mobile)) {
            if (!empty($address_invoice->phone)) {
                $request['telephone'] = preg_replace("/[^0-9]/", "", $address_invoice->phone);
            } elseif (!empty($address_invoice->phone_mobile)) {
                $request['telephone'] = preg_replace("/[^0-9]/", "", $address_invoice->phone_mobile);
            }
        } else {
            $request['telephone'] = null;
        }

        $queryFingerprint = 'SELECT * FROM `'._DB_PREFIX_.'bayonet_antifraud_fingerprint`
            WHERE `customer_id` = '.$this->context->customer->id;
        $fingerprintData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryFingerprint);

        if ($fingerprintData) {
            if ($fingerprintData[0]['fingerprint_token'] !== '') {
                $request['bayonet_fingerprint_token'] = $fingerprintData[0]['fingerprint_token'];
                Db::getInstance()->update(
                    'bayonet_antifraud_fingerprint',
                    [
                        'fingerprint_token' => '',
                    ],
                    'customer_id = '.(int)$this->context->customer->id
                );
            }
        }
        
        $response = $requestHelper->consulting($request);

        if (isset($response)) {
            $dataToInsert['cart_id'] = $this->context->cart->id;
            $dataToInsert['order_id'] = $order->id;
            $dataToInsert['executed'] = 1;

            if (isset($response->reason_code) && (int)$response->reason_code === 0) {
                $dataToInsert['decision'] = $response->decision;
                $dataToInsert['bayonet_tracking_id'] = $response->bayonet_tracking_id;
                $dataToInsert['consulting_api'] = 1;
                $dataToInsert['consulting_api_response'] = json_encode(
                    [
                        'reason_code' => (int)$response->reason_code,
                        'reason_message' => $response->reason_message,
                    ]
                );

                Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);
                
                $updateResponse = $requestHelper->updateTransaction([
                    'auth' => [
                        'api_key' => $apiKey
                    ],
                    'bayonet_tracking_id' => $response->bayonet_tracking_id,
                    'transaction_status' => 'success',
                ]);

                if (isset($updateResponse)) {
                    if (isset($updateResponse->reason_code) && (int)$updateResponse->reason_code === 0) {
                        Db::getInstance()->update(
                            'bayonet_antifraud_orders',
                            [
                                'feedback_api' => 1,
                                'feedback_api_response' => json_encode(
                                    [
                                        'reason_code' => (int)$updateResponse->reason_code,
                                        'reason_message' => $updateResponse->reason_message,
                                    ]
                                ),
                            ],
                            'order_id = '.(int)$order->id
                        );
                    } elseif (isset($updateResponse->reason_code) && (int)$updateResponse->reason_code !== 0) {
                        $message = str_replace("'", "-", $updateResponse->reason_message);
                        Db::getInstance()->update(
                            'bayonet_antifraud_orders',
                            [
                                'feedback_api' => 0,
                                'feedback_api_response' => json_encode(
                                    [
                                        'reason_code' => (int)$updateResponse->reason_code,
                                        'reason_message' => $message,
                                    ]
                                ),
                            ],
                            'order_id = '.(int)$order->id
                        );
                    }
                }

                $this->insertBlocklist($customer->email, $customer->id);
            } elseif (isset($response->reason_code) && (int)$response->reason_code !== 0) {
                $message = str_replace("'", "-", $response->reason_message);
                $dataToInsert['consulting_api'] = 0;
                $dataToInsert['consulting_api_response'] = json_encode(
                    [
                        'reason_code' => (int)$response->reason_code,
                        'reason_message' => $message,
                    ]
                );
                
                Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);
                $this->insertBlocklist($customer->email, $customer->id);
            }
        } else {
            $dataToInsert['consulting_api'] = 0;
            Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);
        }
    }

    /**
     * Sets the key for the device fingerprinting script execution.
     * Adds the php controller definition to process in the back office
     * the fingerprint token generated in the front office.
     *
     * @param object $params
     */
    public function hookHeader()
    {
        if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order.php' || $_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order-opc.php' || Tools::getValue('controller') == 'order' || Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3))) {
            return;
        }

        if (1 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            Media::addJsDef(array('bayonet_af_js_key' => Configuration::get('BAYONET_AF_JS_LIVE_KEY')));
        } elseif (0 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            Media::addJsDef(array('bayonet_af_js_key' => Configuration::get('BAYONET_AF_JS_TEST_KEY')));
        }

        Media::addJsDef(array('urlFingerprint' => $this->context->link->getModuleLink(
            $this->name,
            'fingerprint',
            array()
        )));

        $this->context->controller->registerJavascript(
            'module-bayonetantifraud',
            'modules/' . $this->name . '/views/js/fingerprint.js'
        );
    }
    
    /**
     * Displays the Bayonet information corresponding to a specific order in the back office.
     *
     * @param object $params
     */
    public function hookDisplayAdminOrder($params)
    {
        $blocklistIdSandbox = 0;
        $whitelistSandbox = 0;
        $blocklistSandbox = 0;
        $blocklistIdLive = 0;
        $whitelistLive = 0;
        $blocklistLive = 0;
        $apiMode = (int)Configuration::get('BAYONET_AF_API_MODE');
        $apiModeLabel = $apiMode === 0 ? $this->l('Sandbox (test)') : $this->l('Live (production)');
        $noKeys = false;
        $disabled = '';

        if (1 === $apiMode && !Configuration::get('BAYONET_AF_API_LIVE_KEY')) {
            $noKeys = true;
            $disabled = 'disabled';
        } elseif (0 === $apiMode && !Configuration::get('BAYONET_AF_API_TEST_KEY')) {
            $noKeys = true;
            $disabled = 'disabled';
        }
        
        $displayedOrder = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_orders` 
            WHERE `order_id` = ' . (int)$params['id_order']);
        $orderCustomer = Db::getInstance()->getRow('SELECT a.`email`, a.`id_customer` FROM 
            (SELECT `email`, `id_customer` FROM `' . _DB_PREFIX_ . 'customer` WHERE `id_customer` = 
            (SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'orders` WHERE 
            `id_order` = ' . (int)$params['id_order'] . ')) a');
        $blocklistDataSandbox = Db::getInstance()->getRow('SELECT `blocklist_id`, `whitelist`, `blocklist` 
            FROM `'._DB_PREFIX_.'bayonet_antifraud_blocklist` 
            WHERE `email` =  \'' . $orderCustomer['email'] . '\' AND `api_mode` = 0');
        $blocklistDataLive = Db::getInstance()->getRow('SELECT `blocklist_id`, `whitelist`, `blocklist` 
            FROM `'._DB_PREFIX_.'bayonet_antifraud_blocklist` 
            WHERE `email` =  \'' . $orderCustomer['email'] . '\' AND `api_mode` = 1');

        if ($blocklisDatatSandbox !== false && $blocklistDataLive !== false) {
            $blocklistIdSandbox = $blocklistDataSandbox['blocklist_id'];
            $whitelistSandbox = $blocklistDataSandbox['whitelist'];
            $blocklistSandbox = $blocklistDataSandbox['blocklist'];
            $blocklistIdLive = $blocklistDataLive['blocklist_id'];
            $whitelistLive = $blocklistDataLive['whitelist'];
            $blocklistLive = $blocklistDataLive['blocklist'];
        }
        
        if ($displayedOrder) {
            $apiResponse = $displayedOrder['consulting_api_response'];
            $apiResponse = rtrim($apiResponse, ',');
            $apiResponse = "[" . trim($apiResponse) . "]";
            $metadata = json_decode($apiResponse, true);
            $decisionMessage = '';

            if (null !== ($displayedOrder['consulting_api'])) {
                if ($displayedOrder['decision'] === 'accept') {
                    $decisionMessage = $this->l('Low risk of fraud. You should accept the order');
                } elseif ($displayedOrder['decision'] === 'review') {
                    $decisionMessage = $this->l('Medium risk of fraud. Please review the order');
                } elseif ($displayedOrder['decision'] === 'decline') {
                    $decisionMessage = $this->l('High risk of fraud. You should cancel the order');
                } else {
                    $decisionMessage = $this->l('An error occured while analysing the order, check details below');
                }

                $this->smarty->assign(array(
                    'not_consulting_order' => false,
                    'unprocessed_order' => false,
                    'decision_message' => $decisionMessage,
                    'decision' => '<span style="font-size:1.5em;font-weight:bold;color:#'.
                        (('accept' === $displayedOrder['decision']) ? '339933' :
                            (('decline' === $displayedOrder['decision']) ? 'f00' :
                                ('review' === $displayedOrder['decision'] ? 'ff7f27' : '000000'))).'">'.
                        (('accept' == $displayedOrder['decision']) ? $this->l('ACCEPTED') :
                            (('decline' === $displayedOrder['decision']) ? $this->l('DECLINED') :
                                ('review' === $displayedOrder['decision'] ? $this->l('REVIEW') : 'ERROR'))).'</span>',
                    'bayonet_tracking_id' => $displayedOrder['bayonet_tracking_id'],
                    'reason_code' => $metadata[0]['reason_code'],
                    'reason_message' => 'success' === $metadata[0]['reason_message'] ? $this->l('Correct') : $metadata[0]['reason_message'],
                    'triggered_rules' => $displayedOrder['triggered_rules'],
                    'api_mode_order' => $displayedOrder['api_mode'],
                    'customer_email' => $orderCustomer['email'],
                    'customer_id' => $orderCustomer['id_customer'],
                    'blocklist_id_sandbox' => $blocklistIdSandbox,
                    'whitelist_sandbox' => $whitelistSandbox,
                    'blocklist_sandbox' => $blocklistSandbox,
                    'blocklist_id_live' => $blocklistIdLive,
                    'whitelist_live' => $whitelistLive,
                    'blocklist_live' => $blocklistLive,
                    'current_api_mode' => $apiMode,
                    'no_keys' => $noKeys,
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

        $ajax_controller_url = $this->context->link->getModuleLink(
            $this->name,
            'blocklist',
            ['ajax' => true]
        );

        $this->smarty->assign('path', $this->_path);
        $this->smarty->assign('urlBlocklist', $ajax_controller_url);
            
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

    /**
     * Inserts the email of a customer to the blocklist table if this doesn't
     * exist in there already.
     * Adds two rows, one for sandbox mode and another for live mode.
     * 
     * @param string $email
     * @param int $customer
     */
    private function insertBlocklist($email, $customer)
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'bayonet_antifraud_blocklist` 
            WHERE `email` = ' . '\'' . $email . '\'';
        $blocklistData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

        if ($blocklistData !== false && sizeof($blocklistData) === 0) {
            $blocklistInsert = [
                'customer_id' => $customer,
                'email' => $email,
                'api_mode' => 0
            ];
            Db::getInstance()->insert('bayonet_antifraud_blocklist', $blocklistInsert);
            
            $blocklistInsert['api_mode'] = 1;
            Db::getInstance()->insert('bayonet_antifraud_blocklist', $blocklistInsert);
        }
    }
}
