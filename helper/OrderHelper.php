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

class OrderHelper
{
    public function generateRequestBody($order, $cart, $customer, $currency, $type, $apiMode)
    {
        $requestBody = [];
        $address_delivery = new Address((int)$cart->id_address_delivery);
        $address_invoice = new Address((int)$cart->id_address_invoice);
        $state_delivery = 0 !== $address_delivery->id_state ? (new State((int)$address_delivery->id_state))->name : "NA";
        $country_delivery = new Country((int)$address_delivery->id_country);
        $state_invoice = 0 !== $address_invoice->id_state ? (new State((int)$address_invoice->id_state))->name : "NA";
        $country_invoice = new Country((int)$address_invoice->id_country);
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

        $requestBody = [
            'email' => $customer->email,
            'consumer_name' => $customer->firstname . ' ' . $customer->lastname,
            'consumer_internal_id' => $customer->id,
            'shipping_address' => [
              'line_1' => $address_delivery->address1,
              'line_2' => $address_delivery->address2,
              'city' => $address_delivery->city,
              'state' => $state_delivery,
              'country' => $this->convertCountryCode($country_delivery->iso_code),
              'zip_code' => $address_delivery->postcode
            ],
            'billing_address' => [
              'line_1' => $address_invoice->address1,
              'line_2' => $address_invoice->address2,
              'city' => $address_invoice->city,
              'state' => $state_invoice,
              'country' => $this->convertCountryCode($country_invoice->iso_code),
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
                $requestBody['telephone'] = preg_replace("/[^0-9]/", "", $address_invoice->phone);
            } elseif (!empty($address_invoice->phone_mobile)) {
                $requestBody['telephone'] = preg_replace("/[^0-9]/", "", $address_invoice->phone_mobile);
            }
        } else {
            $requestBody['telephone'] = null;
        }

        $requestBody['payment_method'] = $this->getPaymentMethod($order);

        if ('new' === $type) {
            $queryFingerprint = 'SELECT * FROM `'._DB_PREFIX_.'bayonet_antifraud_fingerprint`
            WHERE `customer_id` = '.$customer->id. ' AND `api_mode` = ' .$apiMode;
            $fingerprintData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryFingerprint);

            if ($fingerprintData) {
                if ($fingerprintData[0]['fingerprint_token'] !== '') {
                    $requestBody['bayonet_fingerprint_token'] = $fingerprintData[0]['fingerprint_token'];
                    Db::getInstance()->update(
                        'bayonet_antifraud_fingerprint',
                        [
                        'fingerprint_token' => '',
                    ],
                        'customer_id = '.(int)$customer->id. ' AND api_mode = ' .$apiMode
                    );
                }
            }
        }

        if ('backfill' === $type) {
            $transationStatus = '';

            if (NULL !== $order->getCurrentOrderState()) {
                if (1 === (int)$order->getCurrentOrderState()->paid) {
                    $transationStatus = 'success';
                    $requestBody['transaction_status'] = 'success';
                } elseif (0 === (int)$order->getCurrentOrderState()->paid) {
                    foreach ($order->getCurrentOrderState()->paid as $template) {
                        if (false !== strpos(strtolower($template), 'cancel') ||
                        false !== strpos(strtolower($template), 'refund')) {
                            $transationStatus = 'cancelled';
                        }
                    }

                    if ('' === $transationStatus) {
                        $transationStatus = 'pending';
                    }
                }
            } else {
                $transationStatus = 'pending';
            }

            $requestBody['transaction_status'] = $transationStatus;
        }

        return $requestBody;
    }

    public function convertCountryCode($country)
    {
        $countries = [
            'AF' => 'AFG', //Afghanistan
            'AX' => 'ALA', //&#197;land Islands
            'AL' => 'ALB', //Albania
            'DZ' => 'DZA', //Algeria
            'AS' => 'ASM', //American Samoa
            'AD' => 'AND', //Andorra
            'AO' => 'AGO', //Angola
            'AI' => 'AIA', //Anguilla
            'AQ' => 'ATA', //Antarctica
            'AG' => 'ATG', //Antigua and Barbuda
            'AR' => 'ARG', //Argentina
            'AM' => 'ARM', //Armenia
            'AW' => 'ABW', //Aruba
            'AU' => 'AUS', //Australia
            'AT' => 'AUT', //Austria
            'AZ' => 'AZE', //Azerbaijan
            'BS' => 'BHS', //Bahamas
            'BH' => 'BHR', //Bahrain
            'BD' => 'BGD', //Bangladesh
            'BB' => 'BRB', //Barbados
            'BY' => 'BLR', //Belarus
            'BE' => 'BEL', //Belgium
            'BZ' => 'BLZ', //Belize
            'BJ' => 'BEN', //Benin
            'BM' => 'BMU', //Bermuda
            'BT' => 'BTN', //Bhutan
            'BO' => 'BOL', //Bolivia
            'BQ' => 'BES', //Bonaire, Saint Estatius and Saba
            'BA' => 'BIH', //Bosnia and Herzegovina
            'BW' => 'BWA', //Botswana
            'BV' => 'BVT', //Bouvet Islands
            'BR' => 'BRA', //Brazil
            'IO' => 'IOT', //British Indian Ocean Territory
            'BN' => 'BRN', //Brunei
            'BG' => 'BGR', //Bulgaria
            'BF' => 'BFA', //Burkina Faso
            'BI' => 'BDI', //Burundi
            'KH' => 'KHM', //Cambodia
            'CM' => 'CMR', //Cameroon
            'CA' => 'CAN', //Canada
            'CV' => 'CPV', //Cape Verde
            'KY' => 'CYM', //Cayman Islands
            'CF' => 'CAF', //Central African Republic
            'TD' => 'TCD', //Chad
            'CL' => 'CHL', //Chile
            'CN' => 'CHN', //China
            'CX' => 'CXR', //Christmas Island
            'CC' => 'CCK', //Cocos (Keeling) Islands
            'CO' => 'COL', //Colombia
            'KM' => 'COM', //Comoros
            'CG' => 'COG', //Congo
            'CD' => 'COD', //Congo, Democratic Republic of the
            'CK' => 'COK', //Cook Islands
            'CR' => 'CRI', //Costa Rica
            'CI' => 'CIV', //Côte d\'Ivoire
            'HR' => 'HRV', //Croatia
            'CU' => 'CUB', //Cuba
            'CW' => 'CUW', //Curaçao
            'CY' => 'CYP', //Cyprus
            'CZ' => 'CZE', //Czech Republic
            'DK' => 'DNK', //Denmark
            'DJ' => 'DJI', //Djibouti
            'DM' => 'DMA', //Dominica
            'DO' => 'DOM', //Dominican Republic
            'EC' => 'ECU', //Ecuador
            'EG' => 'EGY', //Egypt
            'SV' => 'SLV', //El Salvador
            'GQ' => 'GNQ', //Equatorial Guinea
            'ER' => 'ERI', //Eritrea
            'EE' => 'EST', //Estonia
            'ET' => 'ETH', //Ethiopia
            'FK' => 'FLK', //Falkland Islands
            'FO' => 'FRO', //Faroe Islands
            'FJ' => 'FIJ', //Fiji
            'FI' => 'FIN', //Finland
            'FR' => 'FRA', //France
            'GF' => 'GUF', //French Guiana
            'PF' => 'PYF', //French Polynesia
            'TF' => 'ATF', //French Southern Territories
            'GA' => 'GAB', //Gabon
            'GM' => 'GMB', //Gambia
            'GE' => 'GEO', //Georgia
            'DE' => 'DEU', //Germany
            'GH' => 'GHA', //Ghana
            'GI' => 'GIB', //Gibraltar
            'GR' => 'GRC', //Greece
            'GL' => 'GRL', //Greenland
            'GD' => 'GRD', //Grenada
            'GP' => 'GLP', //Guadeloupe
            'GU' => 'GUM', //Guam
            'GT' => 'GTM', //Guatemala
            'GG' => 'GGY', //Guernsey
            'GN' => 'GIN', //Guinea
            'GW' => 'GNB', //Guinea-Bissau
            'GY' => 'GUY', //Guyana
            'HT' => 'HTI', //Haiti
            'HM' => 'HMD', //Heard Island and McDonald Islands
            'VA' => 'VAT', //Holy See (Vatican City State)
            'HN' => 'HND', //Honduras
            'HK' => 'HKG', //Hong Kong
            'HU' => 'HUN', //Hungary
            'IS' => 'ISL', //Iceland
            'IN' => 'IND', //India
            'ID' => 'IDN', //Indonesia
            'IR' => 'IRN', //Iran
            'IQ' => 'IRQ', //Iraq
            'IE' => 'IRL', //Republic of Ireland
            'IM' => 'IMN', //Isle of Man
            'IL' => 'ISR', //Israel
            'IT' => 'ITA', //Italy
            'JM' => 'JAM', //Jamaica
            'JP' => 'JPN', //Japan
            'JE' => 'JEY', //Jersey
            'JO' => 'JOR', //Jordan
            'KZ' => 'KAZ', //Kazakhstan
            'KE' => 'KEN', //Kenya
            'KI' => 'KIR', //Kiribati
            'KP' => 'PRK', //Korea, Democratic People\'s Republic of
            'KR' => 'KOR', //Korea, Republic of (South)
            'KW' => 'KWT', //Kuwait
            'KG' => 'KGZ', //Kyrgyzstan
            'LA' => 'LAO', //Laos
            'LV' => 'LVA', //Latvia
            'LB' => 'LBN', //Lebanon
            'LS' => 'LSO', //Lesotho
            'LR' => 'LBR', //Liberia
            'LY' => 'LBY', //Libya
            'LI' => 'LIE', //Liechtenstein
            'LT' => 'LTU', //Lithuania
            'LU' => 'LUX', //Luxembourg
            'MO' => 'MAC', //Macao S.A.R., China
            'MK' => 'MKD', //Macedonia
            'MG' => 'MDG', //Madagascar
            'MW' => 'MWI', //Malawi
            'MY' => 'MYS', //Malaysia
            'MV' => 'MDV', //Maldives
            'ML' => 'MLI', //Mali
            'MT' => 'MLT', //Malta
            'MH' => 'MHL', //Marshall Islands
            'MQ' => 'MTQ', //Martinique
            'MR' => 'MRT', //Mauritania
            'MU' => 'MUS', //Mauritius
            'YT' => 'MYT', //Mayotte
            'MX' => 'MEX', //Mexico
            'FM' => 'FSM', //Micronesia
            'MD' => 'MDA', //Moldova
            'MC' => 'MCO', //Monaco
            'MN' => 'MNG', //Mongolia
            'ME' => 'MNE', //Montenegro
            'MS' => 'MSR', //Montserrat
            'MA' => 'MAR', //Morocco
            'MZ' => 'MOZ', //Mozambique
            'MM' => 'MMR', //Myanmar
            'NA' => 'NAM', //Namibia
            'NR' => 'NRU', //Nauru
            'NP' => 'NPL', //Nepal
            'NL' => 'NLD', //Netherlands
            'AN' => 'ANT', //Netherlands Antilles
            'NC' => 'NCL', //New Caledonia
            'NZ' => 'NZL', //New Zealand
            'NI' => 'NIC', //Nicaragua
            'NE' => 'NER', //Niger
            'NG' => 'NGA', //Nigeria
            'NU' => 'NIU', //Niue
            'NF' => 'NFK', //Norfolk Island
            'MP' => 'MNP', //Northern Mariana Islands
            'NO' => 'NOR', //Norway
            'OM' => 'OMN', //Oman
            'PK' => 'PAK', //Pakistan
            'PW' => 'PLW', //Palau
            'PS' => 'PSE', //Palestinian Territory
            'PA' => 'PAN', //Panama
            'PG' => 'PNG', //Papua New Guinea
            'PY' => 'PRY', //Paraguay
            'PE' => 'PER', //Peru
            'PH' => 'PHL', //Philippines
            'PN' => 'PCN', //Pitcairn
            'PL' => 'POL', //Poland
            'PT' => 'PRT', //Portugal
            'PR' => 'PRI', //Puerto Rico
            'QA' => 'QAT', //Qatar
            'RE' => 'REU', //Reunion
            'RO' => 'ROU', //Romania
            'RU' => 'RUS', //Russia
            'RW' => 'RWA', //Rwanda
            'BL' => 'BLM', //Saint Barth&eacute;lemy
            'SH' => 'SHN', //Saint Helena
            'KN' => 'KNA', //Saint Kitts and Nevis
            'LC' => 'LCA', //Saint Lucia
            'MF' => 'MAF', //Saint Martin (French part)
            'SX' => 'SXM', //Sint Maarten / Saint Matin (Dutch part)
            'PM' => 'SPM', //Saint Pierre and Miquelon
            'VC' => 'VCT', //Saint Vincent and the Grenadines
            'WS' => 'WSM', //Samoa
            'SM' => 'SMR', //San Marino
            'ST' => 'STP', //S&atilde;o Tom&eacute; and Pr&iacute;ncipe
            'SA' => 'SAU', //Saudi Arabia
            'SN' => 'SEN', //Senegal
            'RS' => 'SRB', //Serbia
            'SC' => 'SYC', //Seychelles
            'SL' => 'SLE', //Sierra Leone
            'SG' => 'SGP', //Singapore
            'SK' => 'SVK', //Slovakia
            'SI' => 'SVN', //Slovenia
            'SB' => 'SLB', //Solomon Islands
            'SO' => 'SOM', //Somalia
            'ZA' => 'ZAF', //South Africa
            'GS' => 'SGS', //South Georgia/Sandwich Islands
            'SS' => 'SSD', //South Sudan
            'ES' => 'ESP', //Spain
            'LK' => 'LKA', //Sri Lanka
            'SD' => 'SDN', //Sudan
            'SR' => 'SUR', //Suriname
            'SJ' => 'SJM', //Svalbard and Jan Mayen
            'SZ' => 'SWZ', //Swaziland
            'SE' => 'SWE', //Sweden
            'CH' => 'CHE', //Switzerland
            'SY' => 'SYR', //Syria
            'TW' => 'TWN', //Taiwan
            'TJ' => 'TJK', //Tajikistan
            'TZ' => 'TZA', //Tanzania
            'TH' => 'THA', //Thailand
            'TL' => 'TLS', //Timor-Leste
            'TG' => 'TGO', //Togo
            'TK' => 'TKL', //Tokelau
            'TO' => 'TON', //Tonga
            'TT' => 'TTO', //Trinidad and Tobago
            'TN' => 'TUN', //Tunisia
            'TR' => 'TUR', //Turkey
            'TM' => 'TKM', //Turkmenistan
            'TC' => 'TCA', //Turks and Caicos Islands
            'TV' => 'TUV', //Tuvalu
            'UG' => 'UGA', //Uganda
            'UA' => 'UKR', //Ukraine
            'AE' => 'ARE', //United Arab Emirates
            'GB' => 'GBR', //United Kingdom
            'US' => 'USA', //United States
            'UM' => 'UMI', //United States Minor Outlying Islands
            'UY' => 'URY', //Uruguay
            'UZ' => 'UZB', //Uzbekistan
            'VU' => 'VUT', //Vanuatu
            'VE' => 'VEN', //Venezuela
            'VN' => 'VNM', //Vietnam
            'VG' => 'VGB', //Virgin Islands, British
            'VI' => 'VIR', //Virgin Island, U.S.
            'WF' => 'WLF', //Wallis and Futuna
            'EH' => 'ESH', //Western Sahara
            'YE' => 'YEM', //Yemen
            'ZM' => 'ZMB', //Zambia
            'ZW' => 'ZWE', //Zimbabwe
        ];
        
        $isoCode = isset($countries[$country]) ? $countries[$country] : $country;
        
        return $isoCode;
    }

    public function getPaymentMethod($order)
    {
        $paymentMethod = 'tokenized_card';
        $paymentMethods = [
            'bankwire' => 'offline',
            'cheque' => 'offline',
            'paypal' => 'paypal',
            'paypalusa' => 'paypal',
            'paypalmx' => 'paypal',
            'blockonomics' => 'crypto_currency',
        ];
        
        if ('openpayprestashop' === $order->module) {
            if (false !== strpos(strtolower($order->payment), 'tarjeta') ||
                false !== strpos(strtolower($order->payment), 'card')) {
                $paymentMethod = 'tokenized_card';
            } elseif (false !== strpos(strtolower($order->payment), 'bitcoin')) {
                $paymentMethod = 'crypto_currency';
            } else {
                $paymentMethod = 'offline';
            }
        } elseif (false !== strpos(strtolower($order->module), 'paypal')) {
            $paymentMethod = 'paypal';
        } else {
            foreach ($paymentMethods as $key => $value) {
                if ($key === $order->module) {
                    $paymentMethod = $value;
                }
            }
        }

        return $paymentMethod;
    }

    public function getTriggeredRules($response)
    {
        $triggeredRules = '';
        $dynamicRules = $response->rules_triggered->dynamic;
        $customRules = $response->rules_triggered->custom;

        foreach ($dynamicRules as $rule) {
            $triggeredRules .= '- ' . $rule . '<br>';
        }

        foreach ($customRules as $rule) {
            $triggeredRules .= '- ' . $rule . '<br>';
        }

        return $triggeredRules;
    }
}
