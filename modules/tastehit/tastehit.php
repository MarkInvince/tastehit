<?php
if (!defined('_PS_VERSION_'))
	exit;

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
			!$this->registerHook('leftColumn') ||
			!$this->registerHook('rightColumn') ||
			!$this->registerHook('displayFooterProduct') ||
			!$this->registerHook('header') ||
			!$this->registerHook('displayBackOfficeHeader') ||
			!Configuration::updateValue('TH_MODULE_NAME', 'tastehit') ||
			!Configuration::updateValue('TH_COSTUMER_ID', 'customer id') ||
			!Configuration::updateValue('TH_URL', 'https://www.tastehit.com') ||
			!Configuration::updateValue('TH_EXPORTS_PATH', '0') ||
			!Configuration::updateValue('TH_EXPORTS_FREQUENCY', '0') ||
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
		$output = '<div id="th_wrapper" class="th_wrapper">';
		$output .= '<div class="module_logo"><img src="'.$this->_path.'img/module-logo.png" alt="'.$this->l('Tastehit').'"/></div>';

		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_module_name = strval(Tools::getValue('TH_MODULE_NAME'));
			if (!$my_module_name
				|| empty($my_module_name)
				|| !Validate::isGenericName($my_module_name))
				$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('TH_MODULE_NAME', $my_module_name);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
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

		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Configuration'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Customer ID'),
					'name' => 'TH_COSTUMER_ID',
					'size' => 20,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('TasteHit URL'),
					'name' => 'TH_URL',
					'size' => 20,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Public path to export'),
					'name' => 'TH_EXPORTS_PATH',
					'size' => 20,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Exports frequency'),
					'name' => 'TH_EXPORTS_FREQUENCY',
					'size' => 20,
					'required' => true
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
				array(
					'type' => 'switch',
					'label' => $this->l('Dispaly on category pages'),
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
				)
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
			'TH_DISPLAY_PRODUCT' => Configuration::get('TH_DISPLAY_PRODUCT'),
			'TH_DISPLAY_CATEGORY' => Configuration::get('TH_DISPLAY_CATEGORY')
		);
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
	public function hookHeader()
	{
		$this->context->controller->addJS($this->_path.'/js/front.js');
		$this->context->controller->addCSS($this->_path.'/css/front.css');
	}


}
