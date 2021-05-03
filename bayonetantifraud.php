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

include_once(dirname(__FILE__) . '/model/BayonetDb.php');
include_once(dirname(__FILE__) . '/helper/RequestHelper.php');
include_once(dirname(__FILE__) . '/helper/OrderHelper.php');


if (!defined('_PS_VERSION_')) {
    exit;
}

class BayonetAntiFraud extends Module
{
    protected $_html = '';
    protected $_postErrors = [];

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
        $this->description = $this->l('A module to analyze the details of new orders to detect any fraud attempt.');

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
     * Removes configuration values, deletes table from database,
     * deletes order statuses and removes tab from back office.
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
        $backfillCompleted = 0;

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
        
        if (Configuration::get('BAYONET_AF_API_LIVE_KEY') &&
            Configuration::get('BAYONET_AF_JS_LIVE_KEY') &&
            1 === (int) Configuration::get('BAYONET_AF_API_MODE')) {
            $this->context->smarty->assign('backfill_enable', 1);
        } else {
            $this->context->smarty->assign('backfill_enable', 0);
        }

        $this->context->smarty->assign('backfill_mode', Configuration::get('BAYONET_AF_BACKFILL_MODE'));
        $this->context->controller->addCSS($this->_path.'views/css/backfill.css');

        $backfillQuery = 'SELECT * FROM `'._DB_PREFIX_.'bayonet_antifraud_backfill`';
        $backfillData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($backfillQuery);

        if (false !== $backfillData && NULL !== (int)$backfillData[0]['backfill_status']) {
            if (1 === (int)$backfillData[0]['backfill_status']) {
                $backfillCompleted = 1;
            }
        }

        $this->context->smarty->assign('backfill_completed', $backfillCompleted);

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

            if (!empty(trim(Tools::getValue('BAYONET_AF_API_VERSION')))) {
                $requestBody = [
                    "auth" => [
                    ]
                ];

                $response = $requestHelper->apiValidation($requestBody, trim(Tools::getValue('BAYONET_AF_API_VERSION')));

                if (!isset($response)) {
                    $this->postErrors[] = $this->l('This API version is invalid, please try again');
                }
            } elseif (empty(trim(Tools::getValue('BAYONET_AF_API_VERSION')))) {
                $this->postErrors[] = $this->l('Cannot save an empty value for the API version, please add an API version and try again');
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
            } elseif (empty(trim(Tools::getValue('BAYONET_AF_API_LIVE_KEY'))) && 1 === (int) Configuration::get('BAYONET_AF_API_LIVE_KEY')) {
                $this->postErros[] = $this->l('Cannot save an empty live (production) API key when the live (production) mode is enabled');
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
            } elseif (empty(trim(Tools::getValue('BAYONET_AF_JS_LIVE_KEY'))) && 1 === (int) Configuration::get('BAYONET_AF_JS_LIVE_KEY')) {
                $this->postErros[] = $this->l('Cannot save an empty live (production) API key when the live (production) mode is enabled');
            }
        }
    }

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

        $apiMode = (int)Configuration::get('BAYONET_AF_API_MODE');
        $apiKey = '';

        if (0 === $apiMode) {
            $apiKey = Configuration::get('BAYONET_AF_API_TEST_KEY');
        } elseif (1 === $apiMode) {
            $apiKey = Configuration::get('BAYONET_AF_API_LIVE_KEY');
        }

        $dataToInsert = [];
        $dataToUpdate = [];
        $requestHelper = new RequestHelper();
        $orderHelper = new OrderHelper();
        $order = $params['order'];
        $orderStatus = $params['orderStatus'];
        $cart = $params['cart'];
        $customer = $params['customer'];
        $currency = $params['currency'];

        $requestBody = $orderHelper->generateRequestBody($order, $cart, $customer, $currency, 'new', $apiMode);
        $requestBody['auth']['api_key'] = $apiKey;
        
        // order analysis
        $response = $requestHelper->consulting($requestBody);

        $dataToInsert['cart_id'] = $cart->id;
        $dataToInsert['order_id'] = $order->id;
        
        if (isset($response)) {
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
                $dataToInsert['triggered_rules'] = $orderHelper->getTriggeredRules($response);
                $dataToInsert['api_mode'] = $apiMode;

                Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);

                // update transaction
                $transactionStatus = 1 === (int)$orderStatus->paid ? 'success' : 'pending';
                $updateRequest = [
                    'auth' => [
                        'api_key' => $apiKey
                    ],
                    'bayonet_tracking_id' => $response->bayonet_tracking_id,
                    'transaction_status' => $transactionStatus,
                ];

                $updateResponse = $requestHelper->updateTransaction($updateRequest);

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
                                'current_status' => $transactionStatus,
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
                                'current_status' => $transactionStatus,
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
                $dataToInsert['api_mode'] = $apiMode;
                
                Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);
                $this->insertBlocklist($customer->email, $customer->id);
            }
        } else {
            $dataToInsert['executed'] = 0;
            $dataToInsert['consulting_api'] = 0;
            $dataToInsert['api_mode'] = $apiMode;
            Db::getInstance()->insert('bayonet_antifraud_orders', $dataToInsert);
        }
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
        $apiKey = '';
        $transactionStatus = '';
        $requestHelper = new RequestHelper();
        $bayonetOrder = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'bayonet_antifraud_orders` WHERE `order_id` = '.(int)$params['id_order']);

        if (false !== $bayonetOrder) {
            if (NULL !== $bayonetOrder['bayonet_tracking_id'] && NULL !== $bayonetOrder['current_status']) {
                $updateRequest = [
                    'bayonet_tracking_id' => $bayonetOrder['bayonet_tracking_id'],
                ];

                if (1 === (int)$bayonetOrder['api_mode']) {
                    $apiKey = Configuration::get('BAYONET_AF_API_LIVE_KEY');
                } else if (0 === (int)$bayonetOrder['api_mode']) {
                    $apiKey = Configuration::get('BAYONET_AF_API_TEST_KEY');
                }

                if ('pending' === $bayonetOrder['current_status']) {
                    if (1 === (int)$params['newOrderStatus']->paid) {
                        $transactionStatus = 'success';
                    } else if (false !== strpos(strtolower($params['newOrderStatus']->template), 'cancel')) {
                        $transactionStatus = 'cancelled';
                    }
                } else if ('success' === $bayonetOrder['current_status']) {
                    if (false !== strpos(strtolower($params['newOrderStatus']->template), 'cancel') ||
                    false !== strpos(strtolower($params['newOrderStatus']->template), 'refund')) {
                        $transactionStatus = 'cancelled';
                    } else if (1 !== (int)$params['newOrderStatus']->paid) {
                        $transactionStatus = 'pending';
                    }
                } else if ('cancelled' === $bayonetOrder['current_status']) {
                    if (1 === (int)$params['newOrderStatus']->paid) {
                        $transactionStatus = 'success';
                    }
                }

                if ('' !== $transactionStatus && '' !== $apiKey) {
                    $updateRequest['transaction_status'] = $transactionStatus;
                    $updateRequest['auth']['api_key'] = $apiKey;
                    $response = $requestHelper->updateTransaction($updateRequest);
                    
                    if (isset($response)) {
                        if (isset($response->reason_code) && (int)$response->reason_code === 0) {
                            Db::getInstance()->update(
                                'bayonet_antifraud_orders',
                                [
                                    'feedback_api' => 1,
                                    'feedback_api_response' => json_encode(
                                        [
                                            'reason_code' => (int)$response->reason_code,
                                            'reason_message' => $response->reason_message,
                                        ]
                                    ),
                                    'current_status' => $transactionStatus,
                                ],
                                'order_id = '.(int)$params['id_order']
                            );
                        } elseif (isset($response->reason_code) && (int)$response->reason_code !== 0) {
                            $message = str_replace("'", "-", $response->reason_message);
                            Db::getInstance()->update(
                                'bayonet_antifraud_orders',
                                [
                                    'feedback_api' => 0,
                                    'feedback_api_response' => json_encode(
                                        [
                                            'reason_code' => (int)$response->reason_code,
                                            'reason_message' => $message,
                                        ]
                                    ),
                                    'current_status' => $transactionStatus,
                                ],
                                'order_id = '.(int)$params['id_order']
                            );
                        }
                    }
                }
            }
        }
    }

    public function hookHeader()
    {
        if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order.php' || $_SERVER['PHP_SELF'] == __PS_BASE_URI__ . 'order-opc.php' || Tools::getValue('controller') == 'order' || Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3))) {
            return;
        }

        if (1 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            Media::addJsDef(['bayonet_api_mode' => (int)Configuration::get('BAYONET_AF_API_MODE')]);
            Media::addJsDef(array('bayonet_af_js_key' => Configuration::get('BAYONET_AF_JS_LIVE_KEY')));
        } elseif (0 === (int)Configuration::get('BAYONET_AF_API_MODE')) {
            Media::addJsDef(['bayonet_api_mode' => (int)Configuration::get('BAYONET_AF_API_MODE')]);
            Media::addJsDef(array('bayonet_af_js_key' => Configuration::get('BAYONET_AF_JS_TEST_KEY')));
        }

        Media::addJsDef(['urlFingerprint' => $this->context->link->getModuleLink(
            $this->name,
            'fingerprint',
            []
        )]);

        $this->context->controller->registerJavascript(
            'module-bayonetantifraud',
            'modules/' . $this->name . '/views/js/fingerprint.js'
        );
    }
    
    public function hookDisplayAdminOrder($params)
    {
        $blocklistIdSandbox = 0;
        $whitelistSandbox = 0;
        $blocklistSandbox = 0;
        $blocklistIdLive = 0;
        $whitelistLive = 0;
        $blocklistLive = 0;
        $reasonCodeBlocklistSandbox = 'N/A';
        $reasonCodeBlocklistLive = 'N/A';
        $reasonCodeWhitelistSandbox = 'N/A';
        $reasonCodeWhitelistLive = 'N/A';
        $reasonMessageBlocklistSandbox = 'N/A';
        $reasonMessageBlocklistLive = 'N/A';
        $reasonMessageWhitelistSandbox = 'N/A';
        $reasonMessageWhitelistLive = 'N/A';
        $attemptedActionBlocklistSandbox = 'N/A';
        $attemptedActionBlocklistLive = 'N/A';
        $attemptedActionWhitelistSandbox = 'N/A';
        $attemptedActionWhitelistLive = 'N/A';

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
        $blocklistDataSandbox = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_. 'bayonet_antifraud_blocklist` WHERE `email` =  \'' . $orderCustomer['email'] . '\' AND `api_mode` = ' . 0);
        $blocklistDataLive = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_. 'bayonet_antifraud_blocklist` WHERE `email` =  \'' . $orderCustomer['email'] . '\' AND `api_mode` = ' . 1);

        if ($blocklistDataSandbox !== false && $blocklistDataLive !== false) {
            $blocklistIdSandbox = $blocklistDataSandbox['blocklist_id'];
            $whitelistSandbox = $blocklistDataSandbox['whitelist'];
            $blocklistSandbox = $blocklistDataSandbox['blocklist'];
            $blocklistIdLive = $blocklistDataLive['blocklist_id'];
            $whitelistLive = $blocklistDataLive['whitelist'];
            $blocklistLive = $blocklistDataLive['blocklist'];
            $reasonCodeBlocklistSandbox = NULL !== $blocklistDataSandbox['reason_code_blocklist'] ? $blocklistDataSandbox['reason_code_blocklist'] : 'N/A';
            $reasonCodeBlocklistLive = NULL !== $blocklistDataLive['reason_code_blocklist'] ? $blocklistDataLive['reason_code_blocklist'] : 'N/A';
            $reasonCodeWhitelistSandbox = NULL !== $blocklistDataSandbox['reason_code_whitelist'] ? $blocklistDataSandbox['reason_code_whitelist'] : 'N/A';
            $reasonCodeWhitelistLive = NULL !== $blocklistDataLive['reason_code_whitelist'] ? $blocklistDataLive['reason_code_whitelist'] : 'N/A';
            $reasonMessageBlocklistSandbox = NULL !== $blocklistDataSandbox['reason_message_blocklist'] ? $blocklistDataSandbox['reason_message_blocklist'] : 'N/A';
            $reasonMessageBlocklistLive = NULL !== $blocklistDataLive['reason_message_blocklist'] ? $blocklistDataLive['reason_message_blocklist'] : 'N/A';
            $reasonMessageWhitelistSandbox = NULL !== $blocklistDataSandbox['reason_message_whitelist'] ? $blocklistDataSandbox['reason_message_whitelist'] : 'N/A';
            $reasonMessageWhitelistLive = NULL !== $blocklistDataLive['reason_message_whitelist'] ? $blocklistDataLive['reason_message_whitelist'] : 'N/A';
            $attemptedActionBlocklistSandbox = NULL !== $blocklistDataSandbox['attempted_action_blocklist'] ? $blocklistDataSandbox['attempted_action_blocklist'] : 'N/A';
            $attemptedActionBlocklistLive = NULL !== $blocklistDataLive['attempted_action_blocklist'] ? $blocklistDataLive['attempted_action_blocklist'] : 'N/A';
            $attemptedActionWhitelistSandbox = NULL !== $blocklistDataSandbox['attempted_action_whitelist'] ? $blocklistDataSandbox['attempted_action_whitelist'] : 'N/A';
            $attemptedActionWhitelistLive = NULL !== $blocklistDataLive['attempted_action_whitelist'] ? $blocklistDataLive['attempted_action_whitelist'] : 'N/A';
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
                    'reason_code_blocklist_sandbox' => $reasonCodeBlocklistSandbox,
                    'reason_code_blocklist_live' => $reasonCodeBlocklistLive,
                    'reason_code_whitelist_sandbox' => $reasonCodeWhitelistSandbox,
                    'reason_code_whitelist_live' => $reasonCodeWhitelistLive,
                    'reason_message_blocklist_sandbox' => 'success' === $reasonMessageBlocklistSandbox ? $this->l('Correct') : $reasonMessageBlocklistSandbox,
                    'reason_message_blocklist_live' => 'success' === $reasonMessageBlocklistLive ? $this->l('Correct') : $reasonMessageBlocklistLive,
                    'reason_message_whitelist_sandbox' => 'success' === $reasonMessageWhitelistSandbox ? $this->l('Correct') : $reasonMessageWhitelistSandbox,
                    'reason_message_whitelist_live' => 'success' === $reasonMessageWhitelistLive ? $this->l('Correct') : $reasonMessageWhitelistLive,
                    'attempted_action_blocklist_sandbox' => 'N/A' === $attemptedActionBlocklistSandbox ? $attemptedActionBlocklistSandbox : ('Add' === $attemptedActionBlocklistSandbox ? $this->l('Add') : $this->l('Removal')),
                    'attempted_action_blocklist_live' => 'N/A' === $attemptedActionBlocklistLive ? $attemptedActionBlocklistLive : ('Add' === $attemptedActionBlocklistLive ? $this->l('Add') : $this->l('Removal')),
                    'attempted_action_whitelist_sandbox' => 'N/A' === $attemptedActionWhitelistSandbox ? $attemptedActionWhitelistSandbox : ('Add' === $attemptedActionWhitelistSandbox ? $this->l('Add') : $this->l('Removal')),
                    'attempted_action_whitelist_live' => 'N/A' === $attemptedActionWhitelistLive ? $attemptedActionWhitelistLive : ('Add' === $attemptedActionWhitelistLive ? $this->l('Add') : $this->l('Removal')),
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

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/'.$this->name.'_bo.css', 'all');
    }

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