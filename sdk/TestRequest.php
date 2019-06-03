<?php

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
              "zip_code" => "05670"
           ],
           "billing_address" => [
              "line_1" => "abc",
              "line_2" => "xyz",
              "city" => "Mexico City",
              "state" => "CDMX",
              "country" => "MEX",
              "zip_code" => "05670"
           ],
           "products" => [
              [
                "product_id" => "1",
                "product_name" => "product_1",
                "product_price" => 12.2433,
                "product_category" => "test"
              ],
           ],
           "transaction_id" => "100011",
           "order_id" => "100011"
        ],
  ];