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

/**
 * Gets the payment method of the order based on the module used to
 * process the order.
 * @param Object created order
 * @return string payment method
 */

function getPaymentMethod($order, $mode) 
{
    $paymentMethod = '';
    $paymentMethods = array(
        'bankwire' => 'offline',
        'cheque' => 'offline',
        'simplifycommerce'=> 'tokenized_card',
        'stripe' => 'tokenized_card',
        'skrill' => 'tokenized_card',
        'amzpayments' => 'tokenized_card',
        'billriantpay' => 'tokenized_card',
        'conektaprestashop' => 'tokenized_card',
        'paypal' => 'paypal',
        'paypalusa' => 'paypal',
        'paypalmx' => 'paypal',
        'blockonomics' => 'crypto_currency',
        'mercadopago' => 'tokenized_card',
        'paygol' => 'tokenized_card',
        'pagofacil' => 'tokenized_card',
    );

    if (0 === $mode) {
        if ('openpayprestashop' == $order->module) {
            if (strpos(strtolower($order->payment), 'tarjeta') !== false || strpos(strtolower($order->payment), 'card') !== false) {
                $paymentMethod = 'tokenized_card';
            } elseif (strpos(strtolower($order->payment), 'bitcoin') !== false) {
                $paymentMethod = 'crypto_currency';
            } else {
                $paymentMethod = 'offline';
            }
        } else {
            foreach ($paymentMethods as $key => $value) {
                if ($order->module == $key) {
                    $paymentMethod = $value;
                }
            }
        }
    } elseif (1 === $mode) {
        if ('openpayprestashop' == $order['module']) {
            if (strpos(strtolower($order['payment']), 'tarjeta') !== false || strpos(strtolower($order['payment']), 'card') !== false) {
                $paymentMethod = 'tokenized_card';
            } elseif (strpos(strtolower($order['paymet']), 'bitcoin') !== false) {
                $paymentMethod = 'crypto_currency';
            } else {
                $paymentMethod = 'offline';
            }
        } else {
            foreach ($paymentMethods as $key => $value) {
                if ($order['module'] == $key) {
                    $paymentMethod = $value;
                }
            }
        }
    }

    return $paymentMethod;
}
