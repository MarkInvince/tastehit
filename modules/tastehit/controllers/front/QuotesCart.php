<?php

include_once(_PS_MODULE_DIR_.'quotes/classes/QuotesProduct.php');
include_once(_PS_MODULE_DIR_.'quotes/classes/QuotesSubmit.php');
include_once(_PS_MODULE_DIR_.'quotes/classes/QuotesTools.php');
class quotesQuotesCartModuleFrontController extends ModuleFrontController {
    
    public $ssl = true;
	public $display_column_left = true;
    private $user_token;

    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();

        $this->quote = new QuotesProductCart;
        $this->submit_quote = new QuotesSubmitCore;
        $this->user_token = uniqid();
        //set user unique key
        if(!$this->context->cookie->__isset('request_id')) {
            $this->context->cookie->__set('request_id', $this->user_token);
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJS(array(
            _THEME_JS_DIR_.'tools/vatManagement.js',
            _THEME_JS_DIR_.'tools/statesManagement.js',
            _PS_JS_DIR_.'validate.js'
        ));

        $this->addJS($this->module->getLocalPath().'js/quotes_cart.js');
    }
    
    public function initContent()
    {
        // Send noindex to avoid ghost carts by bots
        header("X-Robots-Tag: noindex, nofollow", true);

        parent::initContent();
        // default template
        $this->assign();
        $this->_processAddressFormat();
    }

    public function postProcess() {

        if (Tools::isSubmit('submitAccount') || Tools::isSubmit('submitGuestAccount'))
            $this->processSubmitAccount();

        if(Tools::getValue('action')) {
            if(Tools::getValue('action') == 'popup') {

                $this->context->smarty->assign('active_overlay', '1');
                $this->context->smarty->assign('total', 0);
                $this->context->smarty->assign('total_count', 0);

                die(Tools::jsonEncode(array('popup'  => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/hook/quotesCart.tpl"))));
            }
            if(Tools::getValue('action') == 'add') {
                $this->ajaxAddToQuotesCart();
                list($products, $cart) = $this->quote->getProducts();
                $this->context->smarty->assign('products', $products);
                $this->context->smarty->assign('cart', $cart);

                $product_count = 0;
                foreach($products as $key => $value)
                    if(is_numeric($key))
                        $product_count++;
                $this->context->smarty->assign('cartTotalProducts', $product_count);

                die(Tools::jsonEncode(array(
                    'products' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/hook/product-cart-item.tpl"),
                    'header' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_cart_header.tpl"),
                )));
            }
            if(Tools::getValue('action') == 'delete') {

                $this->deleteQuoteById(Tools::getValue('item_id'));

                list($products, $cart) = $this->quote->getProducts();
                $this->context->smarty->assign('products', $products);
                $this->context->smarty->assign('cart', $cart);

                $product_count = 0;
                foreach($products as $key => $value)
                    if(is_numeric($key))
                        $product_count++;
                $this->context->smarty->assign('cartTotalProducts', $product_count);

                die(Tools::jsonEncode(array(
                    'products' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/hook/product-cart-item.tpl"),
                    'header' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_cart_header.tpl"),
                )));
            }
            if(Tools::getValue('action') == 'recount') {
                $item_id = Tools::getValue('item_id');
                $items = explode('_', $item_id);

                $value = 1;
                if(!Tools::getIsset('button') AND !Tools::getValue('button'))
                    $value = (int)pSQL(Tools::getValue('value'));


                $this->quote->recountProductByValue((int)pSQL($items[0]), (int)pSQL($items[1]), $value ,pSQL(Tools::getValue('method')), pSQL($this->context->cookie->__get('request_id')));

                list($products, $cart) = $this->quote->getProducts();
                $this->context->smarty->assign('products', $products);
                $this->context->smarty->assign('cart', $cart);

                if ($this->context->customer->isLogged())
                    $this->context->smarty->assign('isLogged', '1');
                else
                    $this->context->smarty->assign('isLogged', '0');

                $this->context->smarty->assign('empty','true');
                $back = $this->context->link->getModuleLink($this->module->name, 'QuotesCart', array(), true);

                $tpl_path = $this->module->getLocalPath()."views/templates/front";

                $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));

                if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES'))
                    $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
                else
                    $countries = Country::getCountries($this->context->language->id, true);

                // If a rule offer free-shipping, force hidding shipping prices
                $free_shipping = false;
                foreach ($this->context->cart->getCartRules() as $rule)
                    if ($rule['free_shipping'] && !$rule['carrier_restriction'])
                    {
                        $free_shipping = true;
                        break;
                    }

                if (Tools::getValue('userRegistry'))
                    $this->context->smarty->assign('userRegistry', '1');

                $products = array();
                if ($this->context->cookie->__isset('request_id')) {
                    $this->quote->id_quote = $this->context->cookie->__get('request_id');
                    list($products, $cart) = $this->quote->getProducts();
                }
                $this->context->smarty->assign(array(
                    'products' => $products,
                    'cart' => $cart,
                    'tpl_path' => $tpl_path,
                    'back' => $back,
                    'PS_GUEST_QUOTES_ENABLED' => Configuration::get('PS_GUEST_QUOTES_ENABLED'),
                    'ADDRESS_ENABLED' => Configuration::get('ADDRESS_ENABLED'),
                    'isGuest' => isset($this->context->cookie->is_guest) ? $this->context->cookie->is_guest : 0,
                    'countries' => $countries,
                    'sl_country' => isset($selectedCountry) ? $selectedCountry : 0,
                    'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
                    'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
                    'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
                ));

                /* Load guest informations */
                if ($this->context->cookie->is_guest)
                    $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());

                $product_count = 0;
                foreach($products as $key => $value)
                    if(is_numeric($key))
                        $product_count++;
                $this->context->smarty->assign('cartTotalProducts', $product_count);

                die(Tools::jsonEncode(array(
                    'hasError' => false,
                    'data' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_quote_product_list.tpl"),
                    'header' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_cart_header.tpl"),
                    'products' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/hook/product-cart-item.tpl"),
                )));
            }
            if(Tools::getValue('action') == 'delete_from_cart') {
                $delete = $this->deleteQuoteById(Tools::getValue('item_id'));

                list($products, $cart) = $this->quote->getProducts();
                $this->context->smarty->assign('products', $products);
                $this->context->smarty->assign('cart', $cart);

                if ($this->context->customer->isLogged())
                    $this->context->smarty->assign('isLogged', '1');
                else
                    $this->context->smarty->assign('isLogged', '0');

                $this->context->smarty->assign('empty','true');
                $back = $this->context->link->getModuleLink($this->module->name, 'QuotesCart', array(), true);

                $tpl_path = $this->module->getLocalPath()."views/templates/front";

                $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));

                if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES'))
                    $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
                else
                    $countries = Country::getCountries($this->context->language->id, true);

                // If a rule offer free-shipping, force hidding shipping prices
                $free_shipping = false;
                foreach ($this->context->cart->getCartRules() as $rule)
                    if ($rule['free_shipping'] && !$rule['carrier_restriction'])
                    {
                        $free_shipping = true;
                        break;
                    }

                if (Tools::getValue('userRegistry'))
                    $this->context->smarty->assign('userRegistry', '1');

                $products = array();
                if ($this->context->cookie->__isset('request_id')) {
                    $this->quote->id_quote = $this->context->cookie->__get('request_id');
                    list($products, $cart) = $this->quote->getProducts();
                }
                $this->context->smarty->assign(array(
                    'products' => $products,
                    'cart' => $cart,
                    'tpl_path' => $tpl_path,
                    'back' => $back,
                    'PS_GUEST_QUOTES_ENABLED' => Configuration::get('PS_GUEST_QUOTES_ENABLED'),
                    'ADDRESS_ENABLED' => Configuration::get('ADDRESS_ENABLED'),
                    'isGuest' => isset($this->context->cookie->is_guest) ? $this->context->cookie->is_guest : 0,
                    'countries' => $countries,
                    'sl_country' => isset($selectedCountry) ? $selectedCountry : 0,
                    'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
                    'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
                    'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
                ));

                /* Load guest informations */
                if ($this->context->cookie->is_guest)
                    $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());

                $product_count = 0;
                foreach($products as $key => $value)
                    if(is_numeric($key))
                        $product_count++;
                $this->context->smarty->assign('cartTotalProducts', $product_count);

                die(Tools::jsonEncode(array(
                    'hasError' => false,
                    'data' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_quote_product_list.tpl"),
                    'header' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/front/ajax_cart_header.tpl"),
                    'products' => $this->context->smarty->fetch(_PS_MODULE_DIR_."quotes/views/templates/hook/product-cart-item.tpl"),
                )));
            }
            if(Tools::getValue('action') == 'submit') {
                if($this->submitQuote($this->quote)) {
                    die(Tools::jsonEncode(array('hasError' => false,'redirectUrl' => $this->context->link->getModuleLink($this->module->name, 'SubmitedQuotes', array(), true))));
                }
                else {
                    die(Tools::jsonEncode(array('hasError' => true)));
                }
            }
        }
    }

    protected function submitQuote($quote) {
        // check for user session
        if ($this->context->cookie->__isset('request_id')) {

            $address_delivery = $this->context->customer->getAddresses($this->context->language->id);
            $id_address_delivery = $address_delivery[0]['id_address'];

            if(!$id_address_delivery)
                $id_address_delivery = 0;

            $date_add = date('Y-m-d H:i:s', time());

            $sql = "INSERT INTO `"._DB_PREFIX_."cart` SET
                    `id_shop_group` = ".$this->context->shop->id_shop_group.",
                    `id_shop` = ".$this->context->shop->id.",
                    `id_carrier` = 0,
                    `id_lang` = ".$this->context->language->id.",
                    `id_address_delivery` = ".$id_address_delivery.",
                    `id_address_invoice` = ".$id_address_delivery.",
                    `id_currency` = ".$this->context->currency->id.",
                    `id_customer` = ".(int)$this->context->customer->id.",
                    `id_guest` = ".(int)$this->context->cookie->id_guest.",
                    `secure_key` = '".$this->context->customer->secure_key."',
                    `recyclable` = ".$this->context->cart->recyclable.",
                    `date_add` = '".$date_add."',
                    `date_upd` = '".$date_add."'";

            if(Db::getInstance()->execute($sql)){
                $id_cart = Db::getInstance()->Insert_ID();
            }
            else{
                die($sql);
            }
            $quote->id_quote = $this->context->cookie->__get('request_id');
            // get all products
            $all_products = array();
            list($products, $cart) = $quote->getProducts();
            foreach($products as $product) {
                $all_products[] = array(
                    'id'           => $product['id'],
                    'id_attribute' => $product['id_attribute'],
                    'quantity'     => $product['quantity'],
                );
                $sql = "INSERT INTO `"._DB_PREFIX_."cart_product` SET
                    `id_cart` = ".$id_cart.",
                    `id_product` = ".$product['id'].",
                    `id_address_delivery` = ".$id_address_delivery.",
                    `id_shop` = ".$this->context->shop->id.",
                    `id_product_attribute` = ".$product['id_attribute'].",
                    `quantity` = ".$product['quantity'].",
                    `date_add` = '".$date_add."'";
                Db::getInstance()->execute($sql);
            }

            $this->submit_quote->quote_name = "My quote #".quoteNum($this->context->customer->id);
            $this->submit_quote->id_cart = $id_cart;
            $this->submit_quote->id_lang = $this->context->language->id;
            $this->submit_quote->id_currency = $this->context->currency->id;
            $this->submit_quote->burgain_price = 0;
            $this->submit_quote->products = serialize($all_products);
            $this->submit_quote->date_add = $date_add;
            if($this->submit_quote->add()) {
                //generate new user session id
                $this->context->cookie->__set('request_id', uniqid());

                // Prepare email information for submited quote
                $subject = $this->module->l("New submited quote");
                $message = '
                            <html>
                            <head>
                              <title>'.$this->module->l("New submited quote").'</title>
                            </head>
                            <body>
                              <table>
                                <tr>
                                  <th>'.$this->module->l("Quote name").'</th><th>'.$this->module->l("Date").'</th><th>'.$this->module->l("Details").'</th>
                                </tr>
                                <tr>
                                  <td>'.$this->submit_quote->quote_name.'</td>
                                  <td>'.$this->submit_quote->date_add.'</td>
                                  <td><a href="'.$this->context->link->getModuleLink($this->module->name, 'SubmitedQuotes', array(), true).'">'.$this->module->l("See details in your shop profile").'</a></td>
                                </tr>
                              </table>
                            </body>
                            </html>
                ';
                // Send e-mail to customer
                quotesMailConfirm($this->context->customer->email, $message, $subject);

                $message = '
                            <html>
                            <head>
                              <title>'.$this->module->l("New submited quote").'</title>
                            </head>
                            <body>
                                <h2>'.$this->module->l("Quote information").'</h2>
                                  <table>
                                    <tr>
                                      <th>'.$this->module->l("Quote name").'</th><th>'.$this->module->l("Date").'</th>
                                    </tr>
                                    <tr>
                                      <td>'.$this->submit_quote->quote_name.'</td>
                                      <td>'.$this->submit_quote->date_add.'</td>
                                    </tr>
                                  </table>
                                <h2>'.$this->module->l("User information").'</h2>
                                  <table>
                                    <tr>
                                      <th>'.$this->module->l("user ID").'</th><th>'.$this->module->l("firstname").'</th><th>'.$this->module->l("lastname").'</th><th>'.$this->module->l("Email").'</th>
                                    </tr>
                                    <tr>
                                      <td>'.$this->context->customer->id.'</td>
                                      <td>'.$this->context->customer->firstname.'</td>
                                      <td>'.$this->context->customer->lastname.'</td>
                                      <td>'.$this->context->customer->email.'</td>
                                    </tr>
                                  </table>
                            </body>
                            </html>
                ';
                // Send e-mail to admin
                quotesMailConfirm(Configuration::get('PS_SHOP_EMAIL'), $message, $subject);

                // clear shop box
                return $quote->deleteAllProduct();
            }
            else
                return false;
        }
        else {
            return false;
        }
    }

	public function assign()
	{
        if ($this->context->customer->isLogged())
            $this->context->smarty->assign('isLogged', '1');
        else
            $this->context->smarty->assign('isLogged', '0');

        $this->context->smarty->assign('empty','true');
        $back = $this->context->link->getModuleLink($this->module->name, 'QuotesCart', array(), true);

        $tpl_path = $this->module->getLocalPath()."views/templates/front";

        $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES'))
            $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        else
            $countries = Country::getCountries($this->context->language->id, true);

        // If a rule offer free-shipping, force hidding shipping prices
        $free_shipping = false;
        foreach ($this->context->cart->getCartRules() as $rule)
            if ($rule['free_shipping'] && !$rule['carrier_restriction'])
            {
                $free_shipping = true;
                break;
            }

        if (Tools::getValue('userRegistry'))
            $this->context->smarty->assign('userRegistry', '1');

        $products = array();
        if ($this->context->cookie->__isset('request_id')) {
            $this->quote->id_quote = $this->context->cookie->__get('request_id');
            list($products, $cart) = $this->quote->getProducts();
        }
        $this->context->smarty->assign(array(
            'products' => $products,
            'cart'     => $cart,
            'tpl_path' => $tpl_path,
            'back' => $back,
            'PS_GUEST_QUOTES_ENABLED' => Configuration::get('PS_GUEST_QUOTES_ENABLED'),
            'ADDRESS_ENABLED' => Configuration::get('ADDRESS_ENABLED'),
            'isGuest' => isset($this->context->cookie->is_guest) ? $this->context->cookie->is_guest : 0,
            'countries' => $countries,
            'sl_country' => isset($selectedCountry) ? $selectedCountry : 0,
            'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
            'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
            'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
        ));

        /* Load guest informations */
        if ($this->context->cookie->is_guest)
            $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());

        $this->setTemplate('quotes_cart.tpl');
    }
    protected function deleteQuoteById($id) {
        $vals = explode('_',$id);
        $pid = $vals[0];
        $ipa = $vals[1];

        if (!$pid || !is_numeric($pid)) {
            die(Tools::jsonEncode(array('message' => Tools::displayError($this->module->l('Nothing to delete')),'hasError' => true)));
        }
        if ($this->context->cookie->__isset('request_id') AND $pid) {

            $this->quote->id_quote = $this->context->cookie->__get('request_id');
            $this->quote->id_product = $pid;
            $this->quote->id_guest = (int)$this->context->cookie->id_guest;
            $this->quote->id_customer = (int)$this->context->customer->id;
            $this->quote->quantity = 1;
            if($this->quote->deleteProduct($pid, $ipa)) {
                return true;
            }
            else {

                return false;
            }
        }
        else
            die(Tools::jsonEncode(array('pid' => $pid, 'ipa' => $ipa,'request' => $this->context->cookie->__get('request_id'))));
    }
    protected function ajaxAddToQuotesCart() {
        if (Tools::getValue('pqty') <= 0) {
            die(Tools::jsonEncode(array('message' => Tools::displayError($this->module->l('Null quantity!!')),'hasError' => true)));
        }
        elseif (!Tools::getValue('pid')) {
            die(Tools::jsonEncode(array('message' => Tools::displayError($this->module->l('Product not found')),'hasError' => true)));
        }

        $product = new Product((int)Tools::getValue('pid'));
        if (Validate::isLoadedObject($product)) {
            if (!$product->available_for_order || !$product->active) {
                die(Tools::jsonEncode(array('message' => Tools::displayError($this->module->l('This product is no longer available.')), 'hasError' => true)));
            }

            // update model if user is logged in system
            /* if ($this->context->customer->isLogged()) {
                 $this->quote->update();
             }*/
            if ($this->context->cookie->__isset('request_id')) {
                //add product to shop cart
                $this->quote->id_quote = $this->context->cookie->__get('request_id');
                $this->quote->id_shop = $this->context->shop->id;
                $this->quote->id_shop_group = $this->context->shop->id_shop_group;
                $this->quote->id_lang = $this->context->language->id;
                $this->quote->id_product = $product->id;
                $this->quote->id_product_attribute = pSQL(Tools::getValue('ipa')) ? pSQL(Tools::getValue('ipa')) : 0;
                $this->quote->id_guest = (int)$this->context->cookie->id_guest;
                $this->quote->id_customer = (int)$this->context->customer->id;
                $this->quote->quantity = 1;
                $this->quote->date_add = date('Y-m-d H:i:s', time());
                $operator = Tools::getIsset('operator') ? Tools::getValue('operator') : 'up';

                $this->quote->setOperator($operator);
                $this->quote->setQuantity(pSql(Tools::getValue('pqty')));
                $add = $this->quote->add();
            }
        }
        return true;
    }

    /**
     * Process submit on an account
     */
    protected function processSubmitAccount()
    {
        Hook::exec('actionBeforeSubmitAccount');
        $this->create_account = true;

        if (Tools::isSubmit('submitAccount'))
            $this->context->smarty->assign('email_create', 1);

        // New Guest customer
        if (!Tools::getValue('is_new_customer', 1) && !Configuration::get('PS_GUEST_QUOTES_ENABLED'))
            $this->errors[] = Tools::displayError('You cannot create a guest account..');

        if (!Tools::getValue('is_new_customer', 1))
            $_POST['passwd'] = md5(time()._COOKIE_KEY_);

        if (isset($_POST['guest_email']) && $_POST['guest_email'])
            $_POST['email'] = $_POST['guest_email'];

        // Checked the user address in case he changed his email address
        if (Validate::isEmail($email = Tools::getValue('email')) && !empty($email))
            if (Customer::customerExists($email))
                $this->errors[] = Tools::displayError('An account using this email address has already been registered.', false);

        // Preparing customer
        $customer = new Customer();
        $lastnameAddress = Tools::getValue('lastname');
        $firstnameAddress = Tools::getValue('firstname');
        $_POST['lastname'] = Tools::getValue('customer_lastname', $lastnameAddress);
        $_POST['firstname'] = Tools::getValue('customer_firstname', $firstnameAddress);
        $addresses_types = array('address');

//        if (!Configuration::get('PS_ORDER_PROCESS_TYPE') && Configuration::get('PS_GUEST_CHECKOUT_ENABLED') && Tools::getValue('invoice_address'))
//            $addresses_types[] = 'address_invoice';

        /*
        $error_phone = false;
        if (Configuration::get('PS_ONE_PHONE_AT_LEAST'))
        {
            if (Tools::isSubmit('submitGuestAccount') || !Tools::getValue('is_new_customer'))
            {
                if (!Tools::getValue('phone') && !Tools::getValue('phone_mobile'))
                    $error_phone = true;
            }
            elseif (((Configuration::get('PS_REGISTRATION_PROCESS_TYPE') && Configuration::get('PS_ORDER_PROCESS_TYPE'))
                    || (Configuration::get('PS_ORDER_PROCESS_TYPE') && !Tools::getValue('email_create'))
                    || (Configuration::get('PS_REGISTRATION_PROCESS_TYPE') && Tools::getValue('email_create')))
                && (!Tools::getValue('phone') && !Tools::getValue('phone_mobile')))
                $error_phone = true;
        }


        if ($error_phone)
            $this->errors[] = Tools::displayError('You must register at least one phone number.');
        */

        $this->errors = array_unique(array_merge($this->errors, $customer->validateController()));

        // Check the requires fields which are settings in the BO
        $this->errors = $this->errors + $customer->validateFieldsRequiredDatabase();


        // If simple rgistry without Address Delivery
        if (Tools::isSubmit('submitAccount') && !Tools::getValue('address_enabled'))
        {

            if (!count($this->errors))
            {
                if (Tools::isSubmit('newsletter'))
                    $this->processCustomerNewsletter($customer);

                $customer->firstname = Tools::ucwords($customer->firstname);
                $customer->birthday = (empty($_POST['years']) ? '' : (int)$_POST['years'].'-'.(int)$_POST['months'].'-'.(int)$_POST['days']);
                if (!Validate::isBirthDate($customer->birthday))
                    $this->errors[] = Tools::displayError('Invalid date of birth.');

                // New Guest customer
                $customer->is_guest = (Tools::isSubmit('is_new_customer') ? !Tools::getValue('is_new_customer', 1) : 0);
                $customer->active = 1;

                if (!count($this->errors))
                {
                    if ($customer->add())
                    {
                        if (!$customer->is_guest)
                            if (!$this->sendConfirmationMail($customer))
                                $this->errors[] = Tools::displayError('The email cannot be sent.');

                        $this->updateContext($customer);

                        $this->context->cart->update();

                        Hook::exec('actionCustomerAccountAdd', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer
                        ));

                        Tools::redirect($this->context->link->getModuleLink('quotes', 'QuotesCart')."?userRegistry=true");
                    }
                    else
                        $this->errors[] = Tools::displayError('An error occurred while creating your account.');
                }
            }
        }
        else // if address on or Guest account
        {
            $_POST['lastname'] = $lastnameAddress;
            $_POST['firstname'] = $firstnameAddress;
            $post_back = $_POST;
            // Preparing addresses
            foreach($addresses_types as $addresses_type)
            {
                $$addresses_type = new Address();
                $$addresses_type->id_customer = 1;

                if ($addresses_type == 'address_invoice')
                    foreach($_POST as $key => &$post)
                        if (isset($_POST[$key.'_invoice']))
                            $post = $_POST[$key.'_invoice'];

                $this->errors = array_unique(array_merge($this->errors, $$addresses_type->validateController()));
                if ($addresses_type == 'address_invoice')
                    $_POST = $post_back;

                if (!($country = new Country($$addresses_type->id_country)) || !Validate::isLoadedObject($country))
                    $this->errors[] = Tools::displayError('Country cannot be loaded with address->id_country');

                if (!$country->active)
                    $this->errors[] = Tools::displayError('This country is not active.');

                $postcode = Tools::getValue('postcode');
                /* Check zip code format */
                if ($country->zip_code_format && !$country->checkZipCode($postcode))
                    $this->errors[] = sprintf(Tools::displayError('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'), str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format))));
                elseif(empty($postcode) && $country->need_zip_code)
                    $this->errors[] = Tools::displayError('A Zip / Postal code is required.');
                elseif ($postcode && !Validate::isPostCode($postcode))
                    $this->errors[] = Tools::displayError('The Zip / Postal code is invalid.');

                if ($country->need_identification_number && (!Tools::getValue('dni') || !Validate::isDniLite(Tools::getValue('dni'))))
                    $this->errors[] = Tools::displayError('The identification number is incorrect or has already been used.');
                elseif (!$country->need_identification_number)
                    $$addresses_type->dni = null;

                if (Tools::isSubmit('submitAccount') || Tools::isSubmit('submitGuestAccount'))
                    if (!($country = new Country($$addresses_type->id_country, Configuration::get('PS_LANG_DEFAULT'))) || !Validate::isLoadedObject($country))
                        $this->errors[] = Tools::displayError('Country is invalid');
                $contains_state = isset($country) && is_object($country) ? (int)$country->contains_states: 0;
                $id_state = isset($$addresses_type) && is_object($$addresses_type) ? (int)$$addresses_type->id_state: 0;
                if ((Tools::isSubmit('submitAccount')|| Tools::isSubmit('submitGuestAccount')) && $contains_state && !$id_state)
                    $this->errors[] = Tools::displayError('This country requires you to choose a State.');
            }
        }

        if (!count($this->errors))
        {
            if (Customer::customerExists(Tools::getValue('email')))
                $this->errors[] = Tools::displayError('An account using this email address has already been registered. Please enter a valid password or request a new one. ', false);


            if (!count($this->errors))
            {
                $customer->active = 1;

                // New Guest customer
                if (Tools::isSubmit('is_new_customer'))
                    $customer->is_guest = !Tools::getValue('is_new_customer', 1);
                else
                    $customer->is_guest = 0;

                if (!$customer->add())
                    $this->errors[] = Tools::displayError('An error occurred while creating your account.');
                else
                {
                    foreach($addresses_types as $addresses_type)
                    {
                        $$addresses_type->id_customer = (int)$customer->id;
                        if ($addresses_type == 'address_invoice')
                            foreach($_POST as $key => &$post)
                                if (isset($_POST[$key.'_invoice']))
                                    $post = $_POST[$key.'_invoice'];

                        $this->errors = array_unique(array_merge($this->errors, $$addresses_type->validateController()));
                        if ($addresses_type == 'address_invoice')
                            $_POST = $post_back;
                        if (!count($this->errors) && (Tools::getValue('address_enabled') || $this->ajax || Tools::isSubmit('submitGuestAccount')) && !$$addresses_type->add())
                            $this->errors[] = Tools::displayError('An error occurred while creating your address.');
                    }
                    if (!count($this->errors))
                    {
                        if (!$customer->is_guest)
                        {
                            $this->context->customer = $customer;
                            $customer->cleanGroups();
                            // we add the guest customer in the default customer group
                            $customer->addGroups(array((int)Configuration::get('PS_CUSTOMER_GROUP')));
                            if (!$this->sendConfirmationMail($customer))
                                $this->errors[] = Tools::displayError('The email cannot be sent.');
                        }
                        else
                        {
                            $customer->cleanGroups();
                            // we add the guest customer in the guest customer group
                            $customer->addGroups(array((int)Configuration::get('PS_GUEST_GROUP')));
                        }
                        $this->updateContext($customer);
                        $this->context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)$customer->id);
                        $this->context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)$customer->id);
                        if (isset($address_invoice) && Validate::isLoadedObject($address_invoice))
                            $this->context->cart->id_address_invoice = (int)$address_invoice->id;

                        if ($this->ajax && Configuration::get('PS_ORDER_PROCESS_TYPE'))
                        {
                            $delivery_option = array((int)$this->context->cart->id_address_delivery => (int)$this->context->cart->id_carrier.',');
                            $this->context->cart->setDeliveryOption($delivery_option);
                        }

                        // If a logged guest logs in as a customer, the cart secure key was already set and needs to be updated
                        $this->context->cart->update();

                        // Avoid articles without delivery address on the cart
                        $this->context->cart->autosetProductAddress();

                        Hook::exec('actionCustomerAccountAdd', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer
                        ));

                        //$this->errors[] = Tools::displayError('My error.');

                        Tools::redirect($this->context->link->getModuleLink('quotes', 'QuotesCart')."?userRegistry=true");
                    }
                }
            }
        }



        if (count($this->errors))
        {
            //for retro compatibility to display guest account creation form on authentication page
            if (Tools::getValue('submitGuestAccount'))
                $_GET['display_guest_checkout'] = 1;

            if (!Tools::getValue('is_new_customer'))
                unset($_POST['passwd']);


            $this->context->smarty->assign(array(
                'authentification_error' => $this->errors,
                'post' => $_POST

            ));
        }
    }

    /**
     * Update context after customer creation
     * @param Customer $customer Created customer
     */
    protected function updateContext(Customer $customer)
    {
        $this->context->customer = $customer;
        $this->context->smarty->assign('confirmation', 1);
        $this->context->cookie->id_customer = (int)$customer->id;
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->logged = 1;
        // if register process is in two steps, we display a message to confirm account creation
        if (!Configuration::get('PS_REGISTRATION_PROCESS_TYPE'))
            $this->context->cookie->account_created = 1;
        $customer->logged = 1;
        $this->context->cookie->email = $customer->email;
        $this->context->cookie->is_guest = !Tools::getValue('is_new_customer', 1);
        // Update cart address
        $this->context->cart->secure_key = $customer->secure_key;
    }

    /**
     * sendConfirmationMail
     * @param Customer $customer
     * @return bool
     */
    protected function sendConfirmationMail(Customer $customer)
    {
        if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL'))
            return true;

        return Mail::Send(
            $this->context->language->id,
            'account',
            Mail::l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => Tools::getValue('passwd')),
            $customer->email,
            $customer->firstname.' '.$customer->lastname
        );
    }

    protected function _processAddressFormat()
    {
        $address_delivery = new Address((int)$this->context->cart->id_address_delivery);
        $address_invoice = new Address((int)$this->context->cart->id_address_invoice);

        $inv_adr_fields = AddressFormat::getOrderedAddressFields((int)$address_delivery->id_country, false, true);
        $dlv_adr_fields = AddressFormat::getOrderedAddressFields((int)$address_invoice->id_country, false, true);
        $requireFormFieldsList = AddressFormat::$requireFormFieldsList;

        // Add missing require fields for a new user susbscription form
        foreach ($requireFormFieldsList as $fieldName)
            if (!in_array($fieldName, $dlv_adr_fields))
                $dlv_adr_fields[] = trim($fieldName);

        foreach ($requireFormFieldsList as $fieldName)
            if (!in_array($fieldName, $inv_adr_fields))
                $inv_adr_fields[] = trim($fieldName);

        $inv_all_fields = array();
        $dlv_all_fields = array();

        foreach (array('inv', 'dlv') as $adr_type)
        {
            foreach (${$adr_type.'_adr_fields'} as $fields_line)
                foreach (explode(' ', $fields_line) as $field_item)
                    ${$adr_type.'_all_fields'}[] = trim($field_item);

            ${$adr_type.'_adr_fields'} = array_unique(${$adr_type.'_adr_fields'});
            ${$adr_type.'_all_fields'} = array_unique(${$adr_type.'_all_fields'});

            $this->context->smarty->assign($adr_type.'_adr_fields', ${$adr_type.'_adr_fields'});
            $this->context->smarty->assign($adr_type.'_all_fields', ${$adr_type.'_all_fields'});
        }
    }

    protected function _getGuestInformations()
    {
        $customer = $this->context->customer;
        $address_delivery = new Address($this->context->cart->id_address_delivery);

        $id_address_invoice = $this->context->cart->id_address_invoice != $this->context->cart->id_address_delivery ? (int)$this->context->cart->id_address_invoice : 0;
        $address_invoice = new Address($id_address_invoice);

        if ($customer->birthday)
            $birthday = explode('-', $customer->birthday);
        else
            $birthday = array('0', '0', '0');

        return array(
            'id_customer' => (int)$customer->id,
            'email' => Tools::htmlentitiesUTF8($customer->email),
            'customer_lastname' => Tools::htmlentitiesUTF8($customer->lastname),
            'customer_firstname' => Tools::htmlentitiesUTF8($customer->firstname),
            'newsletter' => (int)$customer->newsletter,
            'optin' => (int)$customer->optin,
            'id_address_delivery' => (int)$this->context->cart->id_address_delivery,
            'company' => Tools::htmlentitiesUTF8($address_delivery->company),
            'lastname' => Tools::htmlentitiesUTF8($address_delivery->lastname),
            'firstname' => Tools::htmlentitiesUTF8($address_delivery->firstname),
            'vat_number' => Tools::htmlentitiesUTF8($address_delivery->vat_number),
            'dni' => Tools::htmlentitiesUTF8($address_delivery->dni),
            'address1' => Tools::htmlentitiesUTF8($address_delivery->address1),
            'postcode' => Tools::htmlentitiesUTF8($address_delivery->postcode),
            'city' => Tools::htmlentitiesUTF8($address_delivery->city),
            'phone' => Tools::htmlentitiesUTF8($address_delivery->phone),
            'phone_mobile' => Tools::htmlentitiesUTF8($address_delivery->phone_mobile),
            'id_country' => (int)($address_delivery->id_country),
            'id_state' => (int)($address_delivery->id_state),
            'id_gender' => (int)$customer->id_gender,
            'sl_year' => $birthday[0],
            'sl_month' => $birthday[1],
            'sl_day' => $birthday[2],
            'company_invoice' => Tools::htmlentitiesUTF8($address_invoice->company),
            'lastname_invoice' => Tools::htmlentitiesUTF8($address_invoice->lastname),
            'firstname_invoice' => Tools::htmlentitiesUTF8($address_invoice->firstname),
            'vat_number_invoice' => Tools::htmlentitiesUTF8($address_invoice->vat_number),
            'dni_invoice' => Tools::htmlentitiesUTF8($address_invoice->dni),
            'address1_invoice' => Tools::htmlentitiesUTF8($address_invoice->address1),
            'address2_invoice' => Tools::htmlentitiesUTF8($address_invoice->address2),
            'postcode_invoice' => Tools::htmlentitiesUTF8($address_invoice->postcode),
            'city_invoice' => Tools::htmlentitiesUTF8($address_invoice->city),
            'phone_invoice' => Tools::htmlentitiesUTF8($address_invoice->phone),
            'phone_mobile_invoice' => Tools::htmlentitiesUTF8($address_invoice->phone_mobile),
            'id_country_invoice' => (int)($address_invoice->id_country),
            'id_state_invoice' => (int)($address_invoice->id_state),
            'id_address_invoice' => $id_address_invoice,
            'invoice_company' => Tools::htmlentitiesUTF8($address_invoice->company),
            'invoice_lastname' => Tools::htmlentitiesUTF8($address_invoice->lastname),
            'invoice_firstname' => Tools::htmlentitiesUTF8($address_invoice->firstname),
            'invoice_vat_number' => Tools::htmlentitiesUTF8($address_invoice->vat_number),
            'invoice_dni' => Tools::htmlentitiesUTF8($address_invoice->dni),
            'invoice_address' => $this->context->cart->id_address_invoice !== $this->context->cart->id_address_delivery,
            'invoice_address1' => Tools::htmlentitiesUTF8($address_invoice->address1),
            'invoice_address2' => Tools::htmlentitiesUTF8($address_invoice->address2),
            'invoice_postcode' => Tools::htmlentitiesUTF8($address_invoice->postcode),
            'invoice_city' => Tools::htmlentitiesUTF8($address_invoice->city),
            'invoice_phone' => Tools::htmlentitiesUTF8($address_invoice->phone),
            'invoice_phone_mobile' => Tools::htmlentitiesUTF8($address_invoice->phone_mobile),
            'invoice_id_country' => (int)($address_invoice->id_country),
            'invoice_id_state' => (int)($address_invoice->id_state),
        );
    }
    
}