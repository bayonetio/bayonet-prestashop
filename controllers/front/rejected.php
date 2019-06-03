<?php 

class bayonetRejectedModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		$message = '';

		$message = 'Sorry! Your order has been rejected';

		$this->context->smarty->assign(array(
			'name' => $this->context->shop->name,
			'message' => $message,
			)
		);
		parent::initContent();
		$this->setTemplate('rejected.tpl');
	}
}