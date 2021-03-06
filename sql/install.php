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

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bayonet` (
        `id_bayonet` int(11) NOT NULL AUTO_INCREMENT,
        `id_cart` int(11) NULL,
        `order_no` varchar(255) NOT NULL,
        `bayonet_tracking_id` varchar(255) NOT NULL,
        `consulting_api` tinyint(1) NULL,
        `consulting_api_response` text NULL,
        `feedback_api` tinyint(1) NULL,
        `feedback_api_response` text NULL,
        `historical_api` tinyint(1) NULL,
        `historical_api_response` text NULL,
        `decision` varchar(15) NOT NULL,
        `rules_triggered` varchar(1000) NULL,
        `is_executed` int(11) NOT NULL,
        `date_add` timestamp default current_timestamp NOT NULL,
        PRIMARY KEY  (`id_bayonet`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
$sql[] = 
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bayonet_blocklist` (
        `id_blocklist` int(11) NOT NULL AUTO_INCREMENT,
        `id_customer` int(11) NOT NULL,
        `email` varchar(255) NOT NULL,
        `whitelist` tinyint(1) default 0 NOT NULL,
        `blacklist` tinyint(1) default 0 NOT NULL,
        `response_code` int(11) NOT NULL,
        `response_message` text NULL,
        `api_mode` tinyint(1) NOT NULL,
        PRIMARY KEY  (`id_blocklist`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
$sql[] = 
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bayonet_fingerprint` (
        `id_fingerprint` INT NOT NULL AUTO_INCREMENT,
        `customer` INT NOT NULL,
        `fingerprint_token` VARCHAR(45) NULL,
        PRIMARY KEY (`id_fingerprint`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
