<?php
// The module's main class. Will always be loaded.
if (!defined('_PS_VERSION_'))
 exit;

class DpdDelisPrintExport extends Module
{
	/**
	 * mandatory module functions
	 */
	public function __construct()
	{
		$this->name = 'dpddelisprintexport';
		$this->tab = 'shipping_logistics';
		$this->version = '0.1';
		$this->author = 'Michiel Van Gucht';
		//$this->need_instance = 1;
		//$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;
		
		parent::__construct();
		
		$this->displayName = $this->l('DPD DelisPrint Export');
		$this->description = $this->l('This module depends on the DPD Shipping module, and will export the data needed to print labels via DelisPrint.');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall the DPD DelisPrint Export module?');
	}
	
	public function install()
	{
		// Verify if multishop is active.
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL); // If active select all shops to install new module
		
		if (!parent::install()
		)
			return false;
		
		return(Db::getInstance()->insert('request_sql', array(
			'name' => 'DPDDP567',
			'sql' => "SELECT 
					CONCAT(IF(`psoc`.`weight` < 3, \'SCP\',\'NCP\'), \',PRO\', IF(`psc`.`name` LIKE \'%ParcelShop%\', \',PS\', \'\')) as `Services`,
					CONCAT_WS (\' \', `psa`.`firstname`, `psa`.`lastname`) as `Cnee Name 1`,
					`psa`.`address1` as `Cnee Address 1`,
					`psa`.`address2` as `Cnee Address 2`,
					(SELECT `ps_country`.`iso_code` FROM `ps_country` WHERE `ps_country`.`id_country` = `psa`.`id_country`) as `Cnee Country`, 
					`psa`.`postcode`as `Cnee Zip Code`,
					`psa`.`city` as `Cnee City`,
					`pso`.`reference` as `CustomerRef 1`,
					\'E\' as `Proactive Notification 1 Type`, # Always Email
					(SELECT `ps_customer`.`email` FROM `ps_customer` WHERE `ps_customer`.`id_customer` = `pso`.`id_customer`) as `Proactive Notification 1 Value`, 
					\'904\' as `Proactive Notification 1 Rule`, # Always 904
					UCASE((SELECT `ps_lang`.`iso_code` FROM `ps_lang` WHERE `ps_lang`.`id_lang` = `pso`.`id_lang`)) as `Proactive Notification 1 Language`,
					FORMAT(`psoc`.`weight`, 2) as `Weight`,
					1 as `Amount`,
					`psdps`.`shop_name` as `Shop Name`,
					CONCAT_WS(\' \', `psdps`.`shop_street`, `psdps`.`shop_houseno`) as `Shop Address 1`,
					`psdps`.`shop_country` as `Shop Country`,
					`psdps`.`shop_zipcode` as `Shop Zip Code`,
					`psdps`.`shop_city` as `Shop City`,
					`psdps`.`id_parcelshop` as `Shop ID`
				FROM `ps_orders` as `pso` 
				JOIN `ps_address` as `psa` ON `pso`.`id_address_delivery` = `psa`.`id_address`
				JOIN `ps_carrier` as `psc` ON `pso`.`id_carrier` = `psc`.`id_carrier`
				JOIN `ps_cart_dpdparcelshop` as `psdps` ON `pso`.`id_cart` = `psdps`.`id_cart`
				JOIN `ps_order_carrier` as `psoc` ON `psoc`.`id_order` = `pso`.`id_order`
				WHERE `pso`.`current_state` = 3 # preparation in progress
				AND `psc`.`external_module_name` = \'dpdshipping\'
				AND `psc`.`deleted`= 0"
		)));
	}
	
	public function uninstall()
	{
		if (!parent::uninstall()
		)
			return false;
		
		return(Db::getInstance()->delete('request_sql', 'name = \'DPDDP567\'', 1));
	}
}