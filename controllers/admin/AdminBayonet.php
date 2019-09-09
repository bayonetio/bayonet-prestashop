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

class AdminBayonetController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'bayonet';
        $this->module = 'bayonet';
        $this->lang = false;
        $this->identifier = 'id_bayonet';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        $this->fields_list = array(
            'id_bayonet' => array(
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true,
                ),
            'id_cart' => array(
                'title' => $this->l('Cart'),
                'remove_onclick' => true,
                ),
            'order_no' => array(
                'title' => $this->l('Order'),
                'remove_onclick' => true,
                ),
            'bayonet_tracking_id' => array(
                'title' => $this->l('Bayonet Tracking ID'),
                'remove_onclick' => true,
                'orderby' => false,
                ),
            'consulting_api_response' => array(
                'title' => $this->l('Consulting API Response'),
                'remove_onclick' => true,
                'orderby' => false,
                ),
            'decision' => array(
                'title' => $this->l('Decision'),
                'remove_onclick' => true,
                ),
        );

        parent::__construct();
    }
    
    /**
     * Initializes the Controller's toolbar, disabling the "new" button.
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
    
    /**
     * Renders the list to display in the module's tab in the back office.
     *
     * @return string HTML content
     */
    public function renderList()
    {
        if (isset($this->_filter) &&  '' == trim($this->_filter)) {
            $this->_filter = $this->original_filter;
        }
        
        $content = parent::renderList();

        return $content;
    }
}
