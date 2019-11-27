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

class BayonetBlocklistModuleFrontController extends ModuleFrontController
{
    private $api_key;
    private $bayonet;
    private $request;

    /**
     * Gets the 'mode' value and evaluates it to execute the correct function(s).
     * It also initializes the Bayonet object with the API key creates the request body.
     */
    public function init() 
    {
        parent::init();
        if (0 == Configuration::get('BAYONET_API_MODE')) {
            $this->api_key = Configuration::get('BAYONET_API_TEST_KEY');
        } elseif (1 == Configuration::get('BAYONET_API_MODE')) {
            $this->api_key = Configuration::get('BAYONET_API_LIVE_KEY');
        }

        $this->bayonet = new BayonetClient([
            'api_key' => $this->api_key
        ]);

        $this->request = [
            'email' => Tools::getValue('mail'),
        ];

        if ('addWhite' == Tools::getValue('mode')) {
            if (1 == Tools::getValue('blacklist')) {
                $this->removeBlack();
            }
            $this->addWhite();
            exit;
        } elseif ('removeWhite' == Tools::getValue('mode')) {
            $this->removeWhite();
            exit;
        } elseif ('addBlack' == Tools::getValue('mode')) {
            if (1 == Tools::getValue('whitelist')) {
                $this->removeWhite();
            }
            $this->addBlack();
            exit;
        } elseif ('removeBlack' == Tools::getValue('mode')) {
            $this->removeBlack();
            exit;
        }
    }

    /**
     * Executes the process to add a customer to the whitelist, sending a call to the API
     * and then checking if the customer's record was already on the database table to 
     * either insert or update its information.
     */
    public function addWhite()
    {
        $this->bayonet->addWhiteList([
            'body' => $this->request,
            'on_success' => function ($response) {
                if (Tools::getValue('id') > 0) {
                    Db::getInstance()->update(
                        'bayonet_blocklist',
                        array(
                            'whitelist' => 1,
                            'response_code' => $response->reason_code,
                            'response_message' => $response->reason_message,
                        ),
                        'id_blocklist = '.(int)Tools::getValue('id')
                    );
                } elseif (Tools::getValue('id') == 0) {
                    $data = array(
                        'id_customer' => Tools::getValue('customer'),
                        'email' => Tools::getValue('mail'),
                        'whitelist' => 1,
                        'response_code' => $response->reason_code,
                        'response_message' => $response->reason_message,
                        'api_mode' => Configuration::get('BAYONET_API_MODE'),
                    );
                    Db::getInstance()->insert('bayonet_blocklist', $data);
                }
            },
            'on_failure' => function ($response) {
                $message = str_replace("'", "-", $response->reason_message);
                if (Tools::getValue('id') > 0) {
                    Db::getInstance()->update(
                        'bayonet_blocklist',
                        array(
                            'response_code' => $response->reason_code,
                            'response_message' => $response->reason_message,
                        ),
                        'id_blocklist = '.(int)Tools::getValue('id')
                    );
                } elseif (Tools::getValue('id') == 0) {
                    $data = array(
                        'id_customer' => Tools::getValue('customer'),
                        'email' => Tools::getValue('mail'),
                        'whitelist' => 0,
                        'response_code' => $response->reason_code,
                        'response_message' => $response->reason_message,
                        'api_mode' => Configuration::get('BAYONET_API_MODE'),
                    );
                    Db::getInstance()->insert('bayonet_blocklist', $data);
                }
            },
        ]);
    }

    /**
     * Executes the process to remove a customer from the whitelist, sending a call to the API
     * and then updating update its information on the table in the database.
     */
    public function removeWhite()
    {
        $this->bayonet->removeWhiteList([
            'body' => $this->request,
            'on_success' => function ($response) {
                Db::getInstance()->update(
                    'bayonet_blocklist',
                    array(
                        'whitelist' => 0,
                        'response_code' => $response->reason_code,
                        'response_message' => $response->reason_message,
                    ),
                    'id_blocklist = '.(int)Tools::getValue('id')
                );
            },
            'on_failure' => function ($response) {
                $message = str_replace("'", "-", $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_blocklist',
                    array(
                        'response_code' => $response->reason_code,
                        'response_message' => $message,
                    ),
                    'id_blocklist = '.(int)Tools::getValue('id')
                );
            },
        ]);
    }

    /**
     * Executes the process to add a customer to the blacklist, sending a call to the API
     * and then checking if the customer's record was already on the database table to 
     * either insert or update its information.
     */
    public function addBlack()
    {
        $this->bayonet->addBlackList([
            'body' => $this->request,
            'on_success' => function ($response) {
                if (Tools::getValue('id') > 0) {
                    Db::getInstance()->update(
                        'bayonet_blocklist',
                        array(
                            'blacklist' => 1,
                            'response_code' => $response->reason_code,
                            'response_message' => $response->reason_message,
                        ),
                        'id_blocklist = '.(int)Tools::getValue('id')
                    );
                } elseif (Tools::getValue('id') == 0) {
                    $data = array(
                        'id_customer' => Tools::getValue('customer'),
                        'email' => Tools::getValue('mail'),
                        'blacklist' => 1,
                        'response_code' => $response->reason_code,
                        'response_message' => $response->reason_message,
                        'api_mode' => Configuration::get('BAYONET_API_MODE'),
                    );
                    Db::getInstance()->insert('bayonet_blocklist', $data);
                }
            },
            'on_failure' => function ($response) {
                $message = str_replace("'", "-", $response->reason_message);
                if (Tools::getValue('id') > 0) {
                    Db::getInstance()->update(
                        'bayonet_blocklist',
                        array(
                            'response_code' => $response->reason_code,
                            'response_message' => $message,
                        ),
                        'id_blocklist = '.(int)Tools::getValue('id')
                    );
                } elseif (Tools::getValue('id') == 0) {
                    $data = array(
                        'id_customer' => Tools::getValue('customer'),
                        'email' => Tools::getValue('mail'),
                        'blacklist' => 0,
                        'response_code' => $response->reason_code,
                        'response_message' => $message,
                        'api_mode' => Configuration::get('BAYONET_API_MODE'),
                    );
                    Db::getInstance()->insert('bayonet_blocklist', $data);
                }
            },
        ]);
    }

    /**
     * Executes the process to remove a customer from the blacklist, sending a call to the API
     * and then updating update its information on the table in the database.
     */
    public function removeBlack()
    {
        $this->bayonet->removeBlackList([
            'body' => $this->request,
            'on_success' => function ($response) {
                Db::getInstance()->update(
                    'bayonet_blocklist',
                    array(
                        'blacklist' => 0,
                        'response_code' => $response->reason_code,
                        'response_message' => $response->reason_message,
                    ),
                    'id_blocklist = '.(int)Tools::getValue('id')
                );
            },
            'on_failure' => function ($response) {
                $message = str_replace("'", "-", $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_blocklist',
                    array(
                        'response_code' => $response->reason_code,
                        'response_message' => $message,
                    ),
                    'id_blocklist = '.(int)Tools::getValue('id')
                );
            },
        ]);
    }
}
