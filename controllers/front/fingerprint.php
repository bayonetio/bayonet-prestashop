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

class BayonetFingerprintModuleFrontController extends ModuleFrontController
{
    private $fingerprint;

    /**
     * Receives the fingerprint token from the front office and sets it as a cookie variable
     * to make it available in Bayonet's order validation process.
     */
    public function postProcess()
    {
        $this->fingerprint = Tools::getValue('fingerprint');

        $queryFingerprint = 'SELECT * FROM `'._DB_PREFIX_.'bayonet_fingerprint`
            WHERE `customer` = '.$this->context->customer->id;
        $fingerprintData = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryFingerprint);

        if ($fingerprintData) {
            Db::getInstance()->update(
                'bayonet_fingerprint',
                array(
                    'fingerprint_token' => $this->fingerprint,
                ),
                'customer = '.$this->context->customer->id
            );
        } else {
            $data = array(
                'customer' => $this->context->customer->id,
                'fingerprint_token' => $this->fingerprint,
            );
            Db::getInstance()->insert('bayonet_fingerprint', $data);
        }
        
        exit;
    }
}
