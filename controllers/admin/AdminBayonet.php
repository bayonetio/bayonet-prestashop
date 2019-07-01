<?php

class AdminBayonetController extends ModuleAdminController
{
	public function initToolbar() 
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
	}

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
				'remove_onclick' => true
				),
			'id_cart' => array(
				'title' => $this->l('Cart'),
				'remove_onclick' => true
				),
			'order_no' => array(
				'title' => $this->l('Order'),
				'remove_onclick' => true
				),
			'bayonet_tracking_id' => array(
				'title' => $this->l('Bayonet Tracking ID'),
				'remove_onclick' => true,
				'orderby' => false
				),
			'consulting_api_response' => array(
				'title' => $this->l('Consulting API Response'),
				'remove_onclick' => true,
				'orderby' => false
				),
			'decision' => array(
				'title' => $this->l('Decision'),
				'remove_onclick' => true
				),
		);

		parent::__construct();
	}

	public function renderList()
	{
		if(isset($this->_filter) && trim($this->_filter) == '')
			$this->_filter = $this->original_filter;

		return parent::renderList();
	}
}
