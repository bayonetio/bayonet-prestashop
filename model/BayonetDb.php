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
 * Utility class to install the module's tables in the database
 */
class BayonetDb
{
    /**
     * Creates the required tables for the module
     */
    public static function createTables()
    {
        $sqlQueries = [];
        $installed = false;

        $sqlQueries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_orders` (
            `bayonet_id` int(11) NOT NULL AUTO_INCREMENT,
            `cart_id` int(11) NULL,
            `order_id` varchar(255) NOT NULL,
            `bayonet_tracking_id` varchar(255) NOT NULL,
            `consulting_api` tinyint(1) NULL,
            `consulting_api_response` text NULL,
            `feedback_api` tinyint(1) NULL,
            `feedback_api_response` text NULL,
            `decision` varchar(15) NOT NULL,
            `triggered_rules` varchar(1000) NULL,
            `api_mode` tinyint(1) NULL,
            `executed` int(11) NOT NULL,
            `current_status` varchar(45) NULL,
            `date_add` timestamp default current_timestamp NOT NULL,
            PRIMARY KEY  (`bayonet_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sqlQueries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_blocklist` (
            `blocklist_id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `email` varchar(255) NOT NULL,
            `whitelist` tinyint(1) default 0 NOT NULL,
            `blocklist` tinyint(1) default 0 NOT NULL,
            `reason_code_blocklist` int(11) default NULL,
            `reason_message_blocklist` text NULL,
            `reason_code_whitelist` int(11) default NULL,
            `reason_message_whitelist` text NULL,
            `attempted_action_blocklist` varchar(45) default NULL,
            `attempted_action_whitelist` varchar(45) default NULL,
            `api_mode` tinyint(1) NOT NULL,
            PRIMARY KEY  (`blocklist_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sqlQueries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_fingerprint` (
            `fingerprint_id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `fingerprint_token` varchar(45) default NULL,
            `api_mode` int(11) NOT NULL,
            PRIMARY KEY (`fingerprint_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sqlQueries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_backfill` (
            `backfill_id` int(11) NOT NULL AUTO_INCREMENT,
            `backfill_status` int(11) default 0 NOT NULL,
            `processed_orders` int(11) default 0 NOT NULL,
            `total_orders` int(11) default 0 NOT NULL,
            `last_processed_order` varchar(45) NOT NULL,
            `last_backfill_order` varchar(45) NOT NULL,
            PRIMARY KEY (`backfill_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sqlQueries as $query) {
            if (Db::getInstance()->execute($query)) {
                $installed = true;
            }
        }

        return $installed;
    }

    /**
     * Deletes the module tables from the database
     */
    public static function dropTables()
    {
        $sqlQueries = [];
        $dropped = false;

        $sqlQueries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_orders`;';
        $sqlQueries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_blocklist`;';
        $sqlQueries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_fingerprint`;';
        $sqlQueries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bayonet_antifraud_backfill`;';

        foreach ($sqlQueries as $query) {
            if (Db::getInstance()->execute($query) !== false) {
                $dropped = true;
            }
        }

        return $dropped;
    }
}
