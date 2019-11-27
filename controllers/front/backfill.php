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

class BayonetBackfillModuleFrontController extends ModuleFrontController
{
    private $api_key;
    private $order_id;
    private $bayonet;
    private $orderModule;
    private $orderDate;

    /**
     * Gets the 'mode' value and evaluates it to execute the correct function.
     */
    public function init()
    {
        parent::init();
        if ('initiate' == Tools::getValue('mode')) {
            if (1 == Configuration::get('BAYONET_BACKFILL_MODE')) {
                $response = array();
                $response['error'] = 1;
                echo json_encode($response);
                exit;
            }
            $this->startBackfill();
        } elseif ('execute' == Tools::getValue('mode')) {
            $this->executeBackfill();
        } elseif ('stop' == Tools::getValue('mode')) {
            $this->stopBackfill();
        } elseif ('status' == Tools::getValue('mode')) {
            if (0 == Configuration::get('BAYONET_BACKFILL_MODE')) {
                $response = array();
                $response['error'] = 1;
                echo json_encode($response);
                exit;
            }
            $this->getStatus();
        }
    }

    /**
     * Updates the 'BAYONET_BACKFILL_MODE' value to start the backfill process.
     */
    public function startBackfill()
    {
        header('content-type', 'application/json');
        $response = array();
        if (Configuration::updateValue('BAYONET_BACKFILL_MODE', 1)) {
            $response['error'] = 0;
        } else {
            $response['error'] = 1;
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
        $query = 'SELECT * FROM (SELECT * FROM `'._DB_PREFIX_.'orders` 
            WHERE `reference` IN (SELECT `order_reference` FROM `'._DB_PREFIX_.'order_payment`)) o 
            WHERE o.`id_order` NOT IN (SELECT `order_no` FROM `'._DB_PREFIX_.'bayonet`)';
        $orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

        $ordersNo = sizeof($orders);
        $currentOrder = 0;

        include_once(_PS_MODULE_DIR_.'bayonet/sdk/paymentMethods.php');

        while (0 != $this->getBackfillMode() && $currentOrder < $ordersNo)
        {
            $data = array(
                'order_no' => $orders[$currentOrder]['id_order'],
                'id_cart' => $orders[$currentOrder]['id_cart'],
                'bayonet_tracking_id' => '',
                'decision' => '',
                'is_executed' => 0,
            );
            Db::getInstance()->insert('bayonet', $data);

            if (1 == Configuration::get('BAYONET_API_MODE')) {
                $this->api_key = Configuration::get('BAYONET_API_LIVE_KEY');
            } elseif (0 == Configuration::get('BAYONET_API_MODE')) {
                $this->api_key = Configuration::get('BAYONET_API_TEST_KEY');
            }

            $this->bayonet = new BayonetClient([
                'api_key' => $this->api_key
            ]);

            $this->order_id = $orders[$currentOrder]['id_order'];
            $this->orderModule = $orders[$currentOrder]['module'];
            $this->orderDate = $orders[$currentOrder]['date_add'];
            $customer = new Customer((int)$orders[$currentOrder]['id_customer']);
            $currency = new Currency((int)$orders[$currentOrder]['id_currency']);
            $cart = new Cart((int)$orders[$currentOrder]['id_cart']);
            $address_delivery = new Address((int)$orders[$currentOrder]['id_address_delivery']);
            $address_invoice = new Address((int)$orders[$currentOrder]['id_address_invoice']);
            $state_delivery = 0 != $address_delivery->id_state ? 
                (new State((int)$address_delivery->id_state))->name : "NA";
            $country_delivery = new Country((int)$address_delivery->id_country);
            $state_invoice = 0 != $address_invoice->id_state ? 
                (new State((int)$address_invoice->id_state))->name : "NA";
            $country_invoice = new Country((int)$address_invoice->id_country);

            $products = $cart->getProducts();
            $products_list = array();

            foreach ($products as $product)
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
                'consumer_internal_id' => $customer->id,
                'transaction_amount' => (float)$orders[$currentOrder]['total_paid_tax_incl'],
                'currency_code' => $currency->iso_code,
                'email' => $customer->email,
                'shipping_address' => [
                    'line_1' => $address_delivery->address1,
                    'line_2' => $address_delivery->address2,
                    'city' => $address_delivery->city,
                    'state' => $state_delivery,
                    'country' => convertCountryCode($country_delivery->iso_code),
                    'zip_code' => $address_delivery->postcode
                ],
                'billing_address' => [
                    'line_1' => $address_invoice->address1,
                    'line_2' => $address_invoice->address2,
                    'city' => $address_invoice->city,
                    'state' => $state_invoice,
                    'country' => convertCountryCode($country_invoice->iso_code),
                    'zip_code' => $address_invoice->postcode
                ],
                'products' => $products_list,
                'order_id' => (int)$this->order_id,
                'transaction_status' => 'success',
                'transaction_time' => strtotime($this->orderDate),
            ];
          
            $request['payment_method'] = getPaymentMethod($orders[$currentOrder], 1);

            if ('paypalmx' == $this->orderModule) {
                $request['payment_gateway'] = 'paypal';
            } elseif ('openpayprestashop' == $this->orderModule) {
                $request['payment_gateway'] = 'openpay';
            } elseif ('conektaprestashop' == $this->orderModule) {
                $request['payment_gateway'] = 'conekta';
            }

            if (!empty($address_invoice->phone) || !empty($address_invoice->phone_mobile)) {
                if (!empty($address_invoice->phone)) {
                    $request['telephone'] = $address_invoice->phone;
                } elseif (!empty($address_invoice->phone_mobile)) {
                    $request['telephone'] = $address_invoice->phone_mobile;
                }
            } else {
                $request['telephone'] = null;
            }

            $class = $this;

            $this->bayonet->feedbackHistorical([
                'body' => $request,
                'on_success' => function ($response) use ($class) {
                    Db::getInstance()->update(
                        'bayonet', 
                        array(
                            'historical_api' => 1,
                            'historical_api_response' => json_encode(
                                array(
                                    'reason_code' => $response->reason_code,
                                    'message' => $response->reason_message,
                                )
                            ),
                            'is_executed' => 1,
                        ), 
                        'order_no = '.(int)$class->order_id
                    );
                },
                'on_failure' => function ($response) use ($class) {
                    $message = str_replace("'", "-", $response->reason_message);
                    Db::getInstance()->update(
                        'bayonet', 
                        array(
                            'historical_api' => 0,
                            'historical_api_response' => json_encode(
                                array(
                                    'reason_code' => $response->reason_code,
                                    'message' => $message,
                                )
                            ),
                            'is_executed' => 1,
                        ), 
                        'order_no = '.(int)$class->order_id
                    );
                },
            ]);

            $currentOrder++;
        }
        Configuration::updateValue('BAYONET_BACKFILL_MODE', 0);
        exit;
    }

    /**
     * Updates the 'BAYONET_BACKFILL_MODE' value to stop the backfill process.
     */
    public function stopBackfill()
    {
        header('content-type', 'application/json');
        $response = array();
        if (Configuration::updateValue('BAYONET_BACKFILL_MODE', 0)) {
            $response['error'] = 0;
        } else {
            $response['error'] = 1;
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Gets the current status of the backfill process.
     */
    public function getStatus()
    {
        header('content-type', 'application/json');
        $response = array();

        $queryOrders = 'SELECT count(*) AS total FROM `'._DB_PREFIX_.'orders` 
            WHERE `reference` IN (SELECT `order_reference` FROM `'._DB_PREFIX_.'order_payment`)';
        $queryBayonet = 'SELECT count(*) AS completed FROM `'._DB_PREFIX_.'bayonet` WHERE `is_executed` = 1';

        $totalOrders = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($queryOrders);
        $completedOrders = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($queryBayonet);

        $percentage = ($completedOrders['completed']/$totalOrders['total'])*100;
        $response['percentage'] = ceil($percentage);
        
        if (100 <= $response['percentage']) {
            $response['error'] = 1;
            Configuration::updateValue('BAYONET_BACKFILL_MODE', 0);
        } else {
            $response['error'] = 0;
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Gets the current value of the BAYONET_BACKFILL_MODE configuration.
     */
    private function getBackfillMode()
    {
        $query = 'SELECT value FROM `'._DB_PREFIX_.'configuration` WHERE `name` = "BAYONET_BACKFILL_MODE"';
        $val = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

        $current_backfill_mode = $val[0]['value'];

        return $current_backfill_mode;
    }
}
