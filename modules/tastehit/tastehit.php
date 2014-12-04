<?php
if (!defined('_PS_VERSION_'))
	exit;

require (dirname(__FILE__).'/classes/export.class.php');

class Tastehit extends Module
{
	protected $config_form = false;
    
	public function __construct()
	{
		$this->name = 'tastehit';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'RedCubeSystems';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.4', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('TasteHit products');
		$this->description = $this->l('TasteHit products module');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('TH_MODULE_NAME'))
			$this->warning = $this->l('No name provided');
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install() ||
			!$this->registerHook('displayLeftColumn') ||
			!$this->registerHook('displayRightColumn') ||
			!$this->registerHook('displayFooterProduct') ||
			!$this->registerHook('displayHeader') ||
			!$this->registerHook('displayBackOfficeHeader') ||
			!$this->registerHook('displayFooter') ||
			!$this->registerHook('displayHome') ||
			!Configuration::updateValue('TH_MODULE_NAME', 'tastehit') ||
			!Configuration::updateValue('TH_COSTUMER_ID', 'customer id') ||
			!Configuration::updateValue('TH_URL', 'https://www.tastehit.com') ||
			!Configuration::updateValue('TH_EXPORTS_PATH', _PS_MODULE_DIR_.$this->name.'/export/') ||
			!Configuration::updateValue('TH_EXPORTS_FREQUENCY', '1') ||
			!Configuration::updateValue('TH_DISPLAY_HOME', '1') ||
			!Configuration::updateValue('TH_DISPLAY_PRODUCT', '1') ||
			!Configuration::updateValue('TH_DISPLAY_CATEGORY', '1') ||
			!Configuration::updateValue('TH_PRODUCT_POSITION', '1') ||
			!Configuration::updateValue('TH_CATEGORY_POSITION', '1')
		)
			return false;

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!Configuration::deleteByName('TH_MODULE_NAME') ||
			!Configuration::deleteByName('TH_COSTUMER_ID') ||
			!Configuration::deleteByName('TH_URL') ||
			!Configuration::deleteByName('TH_EXPORTS_PATH') ||
			!Configuration::deleteByName('TH_EXPORTS_FREQUENCY') ||
			!Configuration::deleteByName('TH_DISPLAY_HOME') ||
			!Configuration::deleteByName('TH_DISPLAY_PRODUCT') ||
			!Configuration::deleteByName('TH_DISPLAY_CATEGORY') ||
			!Configuration::deleteByName('TH_PRODUCT_POSITION') ||
			!Configuration::deleteByName('TH_CATEGORY_POSITION')
		)
			return false;

		return true;
	}

	public function getContent()
	{
//		$this->exportProducts();
//		$this->exportBuyingHistory();

		/* Begin export if exportNow button pressed */
		if(Tools::getValue('action') == 'exportNow') {
			if ($this->exportProducts() && $this->exportBuyingHistory()){
				die(Tools::jsonEncode(array(
					'ajax' => 'ok'
				)));
			}else
				die(Tools::jsonEncode(array(
					'ajax' => 'not_ok'
				)));
		}

		$output = '<div id="th_wrapper" class="th_wrapper">';
		$output .= '<div class="module_logo"><a href="https://www.tastehit.com/login" title="tastehit.com" target="_blank"><img src="'.$this->_path.'img/module-logo.png" alt="'.$this->l('Tastehit').'"/></a></div>';
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			$submitErrors = '';

			if (!Tools::getValue('TH_COSTUMER_ID'))
				$submitErrors .= $this->displayError($this->l('Invalid customer ID'));

			if (!Validate::isAbsoluteUrl(Tools::getValue('TH_URL')))
				$submitErrors .= $this->displayError($this->l('TasteHit URL is not correct'));

			if (!Tools::getValue('TH_EXPORTS_PATH'))
				$submitErrors .= $this->displayError($this->l('Public path to export is not correct'));

			if ($submitErrors == '') {
				Configuration::updateValue('TH_COSTUMER_ID', pSQL(Tools::getValue('TH_COSTUMER_ID')));
				Configuration::updateValue('TH_URL', pSQL(Tools::getValue('TH_URL')));
				Configuration::updateValue('TH_EXPORTS_PATH', pSQL(Tools::getValue('TH_EXPORTS_PATH')));
				Configuration::updateValue('TH_EXPORTS_FREQUENCY', Tools::getValue('TH_EXPORTS_FREQUENCY'));
				Configuration::updateValue('TH_DISPLAY_HOME', Tools::getValue('TH_DISPLAY_HOME'));
				Configuration::updateValue('TH_DISPLAY_PRODUCT', Tools::getValue('TH_DISPLAY_PRODUCT'));
				Configuration::updateValue('TH_DISPLAY_CATEGORY', Tools::getValue('TH_DISPLAY_CATEGORY'));
				Configuration::updateValue('TH_CATEGORY_POSITION', Tools::getValue('TH_CATEGORY_POSITION'));
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			} else
				$output .= $submitErrors;
		}

		$output .= $this->currentStatus();
		$output .= $this->displayForm();
		$output .= '</div>';

		return $output;
	}

	/**
	 * Create the current status of your module.
	 */
	public function currentStatus()
	{
		$status = 'offline';
		$status = 'online';

		$output = '<div class="panel current_status">';
			$output .= '<div class="panel-heading">'.$this->l('Current status').'</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('State of service').'</div>';
				$output .= '<div class="col-lg-9"><span class="status '.$status.'">'.$status.'</span></div>';
			$output .= '</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('Recommendations').'</div>';
				$output .= '<div class="col-lg-9"><span class="status color-orange">'.$this->l('Not displaying').'</span></div>';
			$output .= '</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('Catalog URL').'</div>';
				$output .= '<div class="col-lg-9">'.$this->l('http://tastehit/export').'</div>';
			$output .= '</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('Buying history URL').'</div>';
				$output .= '<div class="col-lg-9">'.$this->l('http://tastehit/export').'</div>';
			$output .= '</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('Last catalog export').'</div>';
				$output .= '<div class="col-lg-9">'.$this->l('November 27').'</div>';
			$output .= '</div>';

			$output .= '<div class="form-group">';
				$output .= '<div class="col-lg-3">'.$this->l('Last cart history export').'</div>';
				$output .= '<div class="col-lg-9">'.$this->l('November 27').'</div>';
			$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Create the form that will be displayed in the configuration of your module.
	 */
	public function displayForm()
	{
		// Get default language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Frequency options
		$options = array(
			array(
				'id_option' => 1,
				'name' => $this->l('every day')
			),
			array(
				'id_option' => 2,
				'name' => $this->l('every 2 days')
			),
			array(
				'id_option' => 3,
				'name' => $this->l('every 3 days')
			),
			array(
				'id_option' => 4,
				'name' => $this->l('every week')
			),
			array(
				'id_option' => 5,
				'name' => $this->l('every month')
			),
		);

		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Configuration'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array( // Customer ID
					'type' => 'text',
					'label' => $this->l('Customer ID'),
					'name' => 'TH_COSTUMER_ID',
					'size' => 20,
					'required' => true
				),
				array( // TasteHit URL
					'type' => 'text',
					'label' => $this->l('TasteHit URL'),
					'name' => 'TH_URL',
					'size' => 20,
					'required' => true
				),
				array( // Public path to export
					'type' => 'text',
					'label' => $this->l('Public path to export'),
					'name' => 'TH_EXPORTS_PATH',
					'size' => 20,
					'required' => true
				),
				array( // Exports frequency
					'type' => 'select',
					'label' => $this->l('Exports frequency'),
					'name' => 'TH_EXPORTS_FREQUENCY',
					'required' => true,
					'options' => array(
						'query' => $options,
						'id' => 'id_option',
						'name' => 'name'
					)
				),
				array( // Export now button
					'type' => 'export_button',
					'name' => 'Button',
					'required' => true,
				),
				array( // Dispaly on home page
					'type' => 'switch',
					'label' => $this->l('Dispaly on home page'),
					'name' => 'TH_DISPLAY_HOME',
					'values' => array(
						array(
							'id'    => 'on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id'    => 'off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Dispaly on product pages'),
					'name' => 'TH_DISPLAY_PRODUCT',
					'values' => array(
						array(
							'id'    => 'on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id'    => 'off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				),
				array( // Display on category pages
					'type' => 'switch',
					'label' => $this->l('Display on category pages'),
					'name' => 'TH_DISPLAY_CATEGORY',
					'values' => array(
						array(
							'id'    => 'on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					)
				),
				array( // Position on category pages
					'type'      => 'radio',
					'label'     => $this->l('Position on category pages'),
					'name'      => 'TH_CATEGORY_POSITION',
					'required'  => true,
					'values'    => array(
						array(
							'id'    => 'Left column',
							'value' => 1,
							'label' => $this->l('Left column')
						),
						array(
							'id'    => 'Right column',
							'value' => 0,
							'label' => $this->l('Right column')
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
						'&token='.Tools::getAdminTokenLite('AdminModules'),
				),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// Load current value
		$helper->fields_value = $this->getConfigFormValues();

		return $helper->generateForm($fields_form);
	}

	/**
	 * Set values for the inputs.
	 */
	protected function getConfigFormValues()
	{
		return array(
			'TH_COSTUMER_ID' => Configuration::get('TH_COSTUMER_ID'),
			'TH_URL' => Configuration::get('TH_URL'),
			'TH_EXPORTS_PATH' => Configuration::get('TH_EXPORTS_PATH'),
			'TH_EXPORTS_FREQUENCY' => Configuration::get('TH_EXPORTS_FREQUENCY'),
			'TH_DISPLAY_HOME' => Configuration::get('TH_DISPLAY_HOME'),
			'TH_DISPLAY_PRODUCT' => Configuration::get('TH_DISPLAY_PRODUCT'),
			'TH_DISPLAY_CATEGORY' => Configuration::get('TH_DISPLAY_CATEGORY'),
			'TH_CATEGORY_POSITION' => Configuration::get('TH_CATEGORY_POSITION'),
		);
	}

	/**
	 * Export products to catalog.csv.
	 */
	protected function exportProducts() {
		$file = _PS_MODULE_DIR_.$this->name.'/export/catalog.csv';
		$productsIds = Export::getProducts();

		$csv = 'id;Name;Reference;Category;Description'.PHP_EOL;

		foreach ($productsIds as $productId){
			$product = new Product($productId['id_product'], true, intval(Configuration::get('PS_LANG_DEFAULT')));

			$name = Export::clearDescription($product->name);

			$description = Export::clearDescription($product->description_short);

			$csv .= $product->id.';'.$name.';'.$product->reference.';'.$product->id_category_default.';'.$description.PHP_EOL;

//			echo '<pre>';
//			print_r($product);
//			echo '</pre>';
		}

		if(file_put_contents($file, $csv))
			return true;
		return false;
	}

	/**
	 * Export buying history to history.csv.
	 */
	protected function exportBuyingHistory() {

		$file = _PS_MODULE_DIR_.$this->name.'/export/history.csv';
		$buying_history = Export::getBuyingHistory();

		$csv = 'id_user;product_id'.PHP_EOL;

		foreach ($buying_history as $history){
			$csv .= $history['id_customer'].';'.$history['product_id'].PHP_EOL;
		}

		if(file_put_contents($file, $csv))
			return true;
		return false;
	}


	/**
	 * Add the CSS & JavaScript files you want to be loaded in the BO.
	 */
	public function hookdisplayBackOfficeHeader()
	{
		$this->context->controller->addJS($this->_path.'js/back.js');
		$this->context->controller->addCSS($this->_path.'css/back.css');
	}

	/**
	 * Add the CSS & JavaScript files you want to be added on the FO.
	 */
	public function hookdisplayHeader()
	{
		$this->context->controller->addJS($this->_path.'/js/front.js');
		$this->context->controller->addCSS($this->_path.'/css/front.css');
	}

	/**
	 * Add tastehit API javascript in the bottom of the pages (footer).
	 */
	public function hookdisplayFooter()
	{
		$this->context->controller->addJS($this->_path.'front.js');

		$this->smarty->assign(array(
			'th_customer_id' => Configuration::get('TH_COSTUMER_ID'),
			'th_url' => Configuration::get('TH_URL'),
		));

		return $this->display(__FILE__, 'th_main_js.tpl');
	}



	public function hookdisplayRightColumn($params)
	{
		$this->smarty->assign(array(
			'th_display_home' => Configuration::get('TH_DISPLAY_HOME'),
			'th_display_category' => Configuration::get('TH_DISPLAY_CATEGORY'),
			'th_display_product' => Configuration::get('TH_DISPLAY_PRODUCT'),
			'position_category' => Configuration::get('TH_CATEGORY_POSITION')
		));

		return $this->display(__FILE__, 'tastehit_products.tpl');
	}

	public function hookdisplayLeftColumn($params)
	{
		return $this->hookdisplayRightColumn($params);
	}

	public function hookdisplayHome($params)
	{
		return $this->hookdisplayRightColumn($params);
	}

	public function hookdisplayFooterProduct($params)
	{
		return $this->hookdisplayRightColumn($params);
	}

}
