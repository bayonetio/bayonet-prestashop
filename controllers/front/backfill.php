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

class BayonetantifraudBackfillModuleFrontController extends ModuleFrontController
{
    /**
     * Gets the 'mode' value and evaluates it to execute the correct function.
     */
    public function init()
    {
        parent::init();
        if ('initiate' === Tools::getValue('mode')) {
            if (1 === (int) Configuration::get('BAYONET_AF_BACKFILL_MODE')) {
                $response = [];
                $response['result'] = 1;
                echo json_encode($response);
                exit;
            }
            $this->startBackfill();
        } elseif ('execute' == Tools::getValue('mode')) {
            $this->executeBackfill();
        } elseif ('stop' == Tools::getValue('mode')) {
            $this->stopBackfill();
        } elseif ('status' == Tools::getValue('mode')) {
            $this->getStatus();
        }
    }

    /**
     * Updates the 'BAYONET_AF_BACKFILL_MODE' value to start the backfill process.
     */
    public function startBackfill()
    {
        $response = [];
        if (Configuration::updateValue('BAYONET_AF_BACKFILL_MODE', 1)) {
            $response['result'] = 0;
        } else {
            $response['result'] = 1;
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Executes the backfill process, sending a feedback historical call to the API
     * for each order not present in Bayonet's table.
     */
    public function executeBackfill()
    {
        $response = [];
        $response['result'] = 0;
        echo json_encode($response);

        $backfillQuery = 'SELECT * FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_backfill`';
        $backfillData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($backfillQuery);
        $ordersQuery = '';
        $ordersData = [];
        $totalOrders = 0;

        if ($backfillData !== false && sizeof($backfillData) === 0) {
            $ordersQuery = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `id_order` NOT IN (SELECT `order_id` FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_orders`)';
            $ordersData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($ordersQuery);

            if ($ordersData !== false && sizeof($ordersData) > 0) {
                $lastOrder = end($ordersData);
                reset($ordersData);
                $totalOrders = sizeof($ordersData);
            }

            $data = [
                'total_orders' => $totalOrders,
                'last_processed_order' => '0',
                'last_backfill_order' => $lastOrder['id_order'],
            ];
            if (Db::getInstance()->insert('bayonet_antifraud_backfill', $data)) {
                $backfillData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($backfillQuery);
            }
        } elseif ($backfillData !== false && sizeof($backfillData) > 0) {
            $ordersQuery = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `id_order` NOT IN (SELECT `order_id` FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_orders`)
                AND `id_order` <= ' . (int) $backfillData[0]['last_backfill_order'];
            if ((int) $backfillData[0]['processed_orders'] > 0) {
                $ordersQuery .= ' AND `id_order` > ' . (int) $backfillData[0]['last_processed_order'];
            }

            $totalOrders = (int) $backfillData[0]['total_orders'];
            $ordersData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($ordersQuery);
        }

        if ($ordersData !== false) {
            $requestHelper = new RequestHelper();
            $orderHelper = new OrderHelper();

            foreach ($ordersData as $currentOrder) {
                if ((int) $this->getBackfillMode() === 1) {
                    $order = new Order($currentOrder['id_order']);
                    $cart = new Cart($order->id_cart);
                    $customer = new Customer($order->id_customer);
                    $currency = new Currency($order->id_currency);

                    $requestBody = $orderHelper->generateRequestBody(
                        $order,
                        $cart,
                        $customer,
                        $currency,
                        'backfill',
                        1
                    );
                    $requestBody['auth']['api_key'] = Configuration::get('BAYONET_AF_API_LIVE_KEY');
                    $response = $requestHelper->feedbackHistorical($requestBody);

                    $updateQuery = 'UPDATE `' . _DB_PREFIX_ . 'bayonet_antifraud_backfill` 
                        SET `processed_orders` = `processed_orders` + 1, `last_processed_order` = ' . (int) $order->id .
                        ' WHERE `backfill_id` = ' . (int) $backfillData[0]['backfill_id'];

                    Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($updateQuery);
                } else {
                    exit;
                }
            }

            Db::getInstance()->update(
                'bayonet_antifraud_backfill',
                [
                    'backfill_status' => 1,
                ],
                'backfill_id = ' . (int) $backfillData[0]['backfill_id']
            );
            Configuration::updateValue('BAYONET_AF_BACKFILL_MODE', 0);
            exit;
        }
    }

    /**
     * Updates the 'BAYONET_AF_BACKFILL_MODE' value to stop the backfill process.
     */
    public function stopBackfill()
    {
        $response = [];
        if (Configuration::updateValue('BAYONET_AF_BACKFILL_MODE', 0)) {
            $response['result'] = 0;
        } else {
            $response['result'] = 1;
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Gets the current status of the backfill process.
     */
    public function getStatus()
    {
        $response = [];

        $backfillQuery = 'SELECT * FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_backfill`';
        $backfillData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($backfillQuery);
        $percentage = ((int) $backfillData[0]['processed_orders'] / (int) $backfillData[0]['total_orders']) * 100;
        $response['percentage'] = ceil($percentage);

        $response['float'] = $percentage;
        $response['percentage'] = ceil($percentage);
        $response['result'] = 0;
        echo json_encode($response);
        exit;
    }

    /**
     * Gets the current value of the BAYONET_AF_BACKFILL_MODE configuration.
     */
    private function getBackfillMode()
    {
        $query = 'SELECT value FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = "BAYONET_AF_BACKFILL_MODE"';
        $val = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

        $current_backfill_mode = $val[0]['value'];

        return $current_backfill_mode;
    }
}
