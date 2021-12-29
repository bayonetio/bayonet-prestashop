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

/**
 * Controller class for the device fingerprint feature
 */
class BayonetantifraudFingerprintModuleFrontController extends ModuleFrontController
{
    /**
     * Receives the fingerprint token from the front office and sets it as a cookie variable
     * to make it available in Bayonet's order validation process.
     */
    public function postProcess()
    {
        $fingerprint = Tools::getValue('fingerprint');
        $apiMode = Tools::getValue('apiMode');

        $queryFingerprint = 'SELECT * FROM `' . _DB_PREFIX_ . 'bayonet_antifraud_fingerprint`
            WHERE `customer_id` = ' . $this->context->customer->id . ' AND `api_mode` = ' . (int) $apiMode;
        $fingerprintData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryFingerprint);

        if (isset($fingerprintData) && false !== $fingerprintData && 0 < sizeof($fingerprintData)) {
            Db::getInstance()->update(
                'bayonet_antifraud_fingerprint',
                [
                    'fingerprint_token' => pSQL($fingerprint),
                ],
                'customer_id = ' . $this->context->customer->id . ' AND api_mode = ' . (int) $apiMode
            );
        } elseif (isset($fingerprintData) && false !== $fingerprintData && 0 === sizeof($fingerprintData)) {
            $data = [
                'customer_id' => $this->context->customer->id,
                'fingerprint_token' => pSQL($fingerprint),
                'api_mode' => (int) $apiMode,
            ];
            Db::getInstance()->insert('bayonet_antifraud_fingerprint', $data);
        }

        exit;
    }
}
