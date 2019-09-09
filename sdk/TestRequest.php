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

$request = [
    "invalid_token" => "da2da838-6311-4646-805f-2466954b1a11",
    "consulting" => [
        "channel" => "ecommerce",
        "consumer_name" => "test",
        "consumer_internal_id" => "1",
        "cardholder_name" => "Test",
        "payment_method" => "card",
        "transaction_amount" => "100.00",
        "currency_code" => "MXN",
        "telephone" => "12345678999",
        "card_number" => "4242424242424242",
        "email" => "test_php@bayonet.io",
        "payment_gateway" => "stripe",
        "shipping_address" => [
            "line_1" => "abc",
            "line_2" => "xyz",
            "city" => "Mexico City",
            "state" => "CDMX",
            "country" => "MEX",
            "zip_code" => "05670",
        ],
        "billing_address" => [
            "line_1" => "abc",
            "line_2" => "xyz",
            "city" => "Mexico City",
            "state" => "CDMX",
            "country" => "MEX",
            "zip_code" => "05670",
        ],
        "products" => [
            [
                "product_id" => "1",
                "product_name" => "product_1",
                "product_price" => 12.2433,
                "product_category" => "test",
            ],
        ],
        "transaction_id" => "100011",
    ],
];
