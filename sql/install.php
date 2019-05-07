<?php
/**
* 2007-2017 PrestaShop
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
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bayonet` (
    `id_bayonet` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NULL,
    `order_no` varchar(255) NOT NULL,
    `bayonet_trans_code` varchar(255) NOT NULL,
    `consulting_api` tinyint(1) NULL,
    `consulting_api_response` text NULL,
    `feedback_api` tinyint(1) NULL,
    `feedback_api_response` text NULL,
    `historical_api` tinyint(1) NULL,
    `historical_api_response` text NULL,
    `status` varchar(15) NOT NULL,
    `is_executed` int(11) NOT NULL,
    `date_add` timestamp default current_timestamp NOT NULL,
    PRIMARY KEY  (`id_bayonet`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
