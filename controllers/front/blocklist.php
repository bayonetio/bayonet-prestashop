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

include_once dirname(__FILE__) . '/../../helper/RequestHelper.php';

class BayonetantifraudBlocklistModuleFrontController extends ModuleFrontController
{
    protected $requestHelper;

    /**
     * Gets the 'mode' value and evaluates it to execute the correct function(s).
     */
    public function init()
    {
        parent::init();
        $this->requestHelper = new RequestHelper();
        $apiMode = (int) Tools::getValue('apiMode');
        $apiKey = '';

        if (1 === $apiMode) {
            $apiKey = Configuration::get('BAYONET_AF_API_LIVE_KEY');
        }

        $request = [
            'auth' => [
                'api_key' => $apiKey,
            ],
            'email' => Tools::getValue('mail'),
        ];

        if ('addWhite' === Tools::getValue('mode')) {
            if (1 === (int) Tools::getValue('blocklist')) {
                $responseBlock = $this->removeBlock($request);
                if (0 === (int) $responseBlock) {
                    $this->addWhite($request);
                }
            } elseif (0 === (int) Tools::getValue('blocklist')) {
                $this->addWhite($request);
            }
            echo json_encode(1);
            exit;
        } elseif ('removeWhite' === Tools::getValue('mode')) {
            $this->removeWhite($request);
            echo json_encode(1);
            exit;
        } elseif ('addBlock' == Tools::getValue('mode')) {
            if (1 === (int) Tools::getValue('whitelist')) {
                $responseWhite = $this->removeWhite($request);
                if (0 === (int) $responseWhite) {
                    $this->addBlock($request);
                }
            } elseif (0 === (int) Tools::getValue('whitelist')) {
                $this->addBlock($request);
            }
            echo json_encode(1);
            exit;
        } elseif ('removeBlock' == Tools::getValue('mode')) {
            $this->removeBlock($request);
            echo json_encode(1);
            exit;
        }
    }

    /**
     * Executes the process to add a customer's mail to the whitelist,
     * sending a call to the API and then updating update its information
     * in the table in the database.
     */
    public function addWhite($request)
    {
        $response = $this->requestHelper->addWhitelist($request);

        if (isset($response)) {
            if (isset($response->reason_code) && 0 === (int) $response->reason_code) {
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'whitelist' => 1,
                        'reason_code_whitelist' => (int) $response->reason_code,
                        'reason_message_whitelist' => $response->reason_message,
                        'attempted_action_whitelist' => 'Add',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            } elseif (isset($response->reason_code) && 0 !== (int) $response->reason_code) {
                $message = str_replace("'", '-', $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'reason_code_whitelist' => (int) $response->reason_code,
                        'reason_message_whitelist' => $message,
                        'attempted_action_whitelist' => 'Add',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            }
        }
    }

    /**
     * Executes the process to remove a customer's mail from the whitelist,
     * sending a call to the API and then updating update its information
     * in the table in the database.
     */
    public function removeWhite($request)
    {
        $response = $this->requestHelper->removeWhitelist($request);

        if (isset($response)) {
            if (isset($response->reason_code) && 0 === (int) $response->reason_code) {
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'whitelist' => 0,
                        'reason_code_whitelist' => (int) $response->reason_code,
                        'reason_message_whitelist' => $response->reason_message,
                        'attempted_action_whitelist' => 'Remove',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            } elseif (isset($response->reason_code) && 0 !== (int) $response->reason_code) {
                $message = str_replace("'", '-', $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'reason_code_whitelist' => (int) $response->reason_code,
                        'reason_message_whitelist' => $message,
                        'attempted_action_whitelist' => 'Remove',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            }
        }
    }

    /**
     * Executes the process to add a customer's mail to the blacklist,
     * sending a call to the API and then updating update its information
     * in the table in the database.
     */
    public function addBlock($request)
    {
        $response = $this->requestHelper->addBlocklist($request);

        if (isset($response)) {
            if (isset($response->reason_code) && 0 === (int) $response->reason_code) {
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'blocklist' => 1,
                        'reason_code_blocklist' => (int) $response->reason_code,
                        'reason_message_blocklist' => $response->reason_message,
                        'attempted_action_blocklist' => 'Add',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            } elseif (isset($response->reason_code) && 0 !== (int) $response->reason_code) {
                $message = str_replace("'", '-', $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'reason_code_blocklist' => (int) $response->reason_code,
                        'reason_message_blocklist' => $message,
                        'attempted_action_blocklist' => 'Add',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            }
        }
    }

    /**
     * Executes the process to remove a customer's mail from the blacklist,
     * sending a call to the API and then updating update its information
     * in the table in the database.
     */
    public function removeBlock($request)
    {
        $response = $this->requestHelper->removeBlocklist($request);

        if (isset($response)) {
            if (isset($response->reason_code) && 0 === (int) $response->reason_code) {
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'blocklist' => 0,
                        'reason_code_blocklist' => (int) $response->reason_code,
                        'reason_message_blocklist' => $response->reason_message,
                        'attempted_action_blocklist' => 'Remove',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            } elseif (isset($response->reason_code) && 0 !== (int) $response->reason_code) {
                $message = str_replace("'", '-', $response->reason_message);
                Db::getInstance()->update(
                    'bayonet_antifraud_blocklist',
                    [
                        'reason_code_blocklist' => (int) $response->reason_code,
                        'reason_message_blocklist' => $message,
                        'attempted_action_blocklist' => 'Remove',
                    ],
                    'blocklist_id = ' . (int) Tools::getValue('id')
                );

                return $response->reason_code;
            }
        }
    }
}
