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
        $this->_where = 'AND consulting_api IS NOT NULL';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        $this->fields_list = array(
            'id_bayonet' => array(
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true,
                'hint' => $this->l('The ID of this table'),
                ),
            'id_cart' => array(
                'title' => $this->l('Cart'),
                'align' => 'center',
                'remove_onclick' => true,
                'hint' => $this->l('The cart associated to this order'),
                ),
            'order_no' => array(
                'title' => $this->l('Order'),
                'align' => 'center',
                'hint' => $this->l('The ID of the order'),
                'callback' => 'vieworder',
                ),
            'bayonet_tracking_id' => array(
                'title' => $this->l('Bayonet Tracking ID'),
                'align' => 'center',
                'remove_onclick' => true,
                'orderby' => false,
                'hint' => $this->l('An ID generated by the Bayonet API for this transaction'),
                ),
            'consulting_api_response' => array(
                'title' => $this->l('API Call Status'),
                'align' => 'center',
                'remove_onclick' => true,
                'orderby' => false,
                'hint' => $this->l('This information serves to know if any problems were present when making the call to the Bayonet API'),
                ),
            'decision' => array(
                'title' => $this->l('Decision'),
                'align' => 'center',
                'remove_onclick' => true,
                'hint' => $this->l('The actual decision obtained after the order analysis, this is what you use to decide what to do with an order, if an order has -reject- in this field, you should cancel it right away'),
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

    /**
     * Sets a link to the order detais in every order ID on the list in the module's tab in the back office.
     *
     * @param string $value
     * @param array $row
     */
    public function viewOrder($value, $row)
    {
        $link = $this->context->link->getAdminLink('AdminOrders').'&id_order='.(int)$value.'&vieworder';

        return '<a href="'.$link.'">'.(int)$value.'</a>';
    }
}
