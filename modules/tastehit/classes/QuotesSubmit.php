<?php

if (!defined('_PS_VERSION_'))
    exit;

include_once(_PS_MODULE_DIR_ . 'quotes/classes/QuotesTools.php');
class QuotesSubmitCore extends ObjectModel
{
    public $id_quote;
    public $id_cart;
    public $reference;
    public $quote_name;
    public $id_shop;
    public $id_shop_group;
    public $id_lang;
    public $id_currency;
    public $id_customer;
    public $products;
    public $burgain_price;
    public $date_add;
    public $submited;

    public static $definition = array(
        'table' => 'quotes',
        'primary' => 'id_quote',
        'fields' => array(
            'id_cart'       => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'quote_name'    => 	array('type' => self::TYPE_STRING,  'validate' => 'isAnything'),
            'reference'    => 	array('type' => self::TYPE_STRING,  'validate' => 'isAnything'),
            'id_shop'       => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'id_shop_group' => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'id_lang'       => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'id_currency'   => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'id_customer'   => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'products'      => 	array('type' => self::TYPE_STRING,  'validate' => 'isAnything'),
            'burgain_price' => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
            'date_add'      => 	array('type' => self::TYPE_DATE,    'validate' => 'isDateFormat'),
            'submited'      => 	array('type' => self::TYPE_INT,     'validate' => 'isUnsignedId'),
        ),
    );

    public function __construct($id_quote = null, $id_lang = null)
    {
        parent::__construct($id_quote);

        $this->context = Context::getContext();
        if (!is_null($id_lang))
            $this->id_lang = (int)(Language::getLanguage($id_lang) !== false) ? $id_lang : Configuration::get('PS_LANG_DEFAULT');
        $this->id_shop = (int)$this->context->shop->id;
        $this->id_shop_group = (int)$this->context->shop->id_shop_group;
        $this->id_customer = (int)$this->context->customer->id;
        $this->date_add = date('Y-m-d H:i:s', time());
        $this->reference = strtoupper(Tools::passwdGen(9, 'NO_NUMERIC'));
        $this->submited = 0;
    }

    public function add($autodate = true, $null_values = false )
    {
        return parent::add($autodate);
    }

    public function update($null_values = false)
    {
        $return = parent::update();
        return $return;
    }

    public function delete()
    {
        if (!Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'quotes` WHERE `id_quote` = '.(int)$this->id_quote))
            return false;

        return parent::delete();
    }
    public function deleteQuoteById($id_quote = false, $id_customer = false)
    {
        if($id_quote AND $id_customer) {
            if (Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'quotes` WHERE `id_quote` = '.(int)$id_quote.' AND `id_customer` = '.(int)$id_customer)){
                if (Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'quotes_bargains` WHERE `id_quote` = '.(int)$id_quote))
                    return true;
            }
            return false;
        }
        else
            return false;
    }
    public function getAllQuotes() {

        $quotes = array();
        $result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'quotes`');
        if (empty($result))
            return array();

        foreach ($result as $row) {
            $quote = array();
            $quote['id_quote'] = $row['id_quote'];
            $quote['quote_name'] = $row['quote_name'];
            $quote['id_shop'] = $row['id_shop'];
            $quote['id_shop_group'] = $row['id_shop_group'];
            $quote['id_lang'] = $row['id_lang'];
            $customer = new Customer($row['id_customer']);
            $quote['customer'] = array(
                'id' => $customer->id,
                'name' => $customer->firstname.' '.$customer->lastname,
                'link' => 'index.php?tab=AdminCustomers&addcustomer&id_customer='.$customer->id.'&token='.Tools::getAdminTokenLite('AdminCustomers'),
            );
            $quote['products'] = unserialize($row['products']);
            $quote['date_add'] = $row['date_add'];
            $quote['submited'] = $row['submited'];
            $quotes[] = $quote;
        }
        return $quotes;
    }
    public function getQuoteById($id_quote, $id_customer) {
        global $currentIndex;
        $result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'quotes` WHERE `id_quote` = '.$id_quote.' AND `id_customer` = '.$id_customer);
        if (empty($result))
            return array();

        $customer = new Customer($id_customer);
        $out['customer'] = array(
            'id'       => $customer->id,
            'name'     => $customer->firstname.' '.$customer->lastname,
            'gender'   => $customer->id_gender,
            'email'    => $customer->email,
            'birthday' => $customer->birthday,
            'addresses'=> $customer->getAddresses($this->context->language->id),
            'date_add' => $customer->date_add,
            'link'     => 'index.php?tab=AdminCustomers&addcustomer&id_customer='.$customer->id.'&token='.Tools::getAdminTokenLite('AdminCustomers'),
        );

        $product_arr = array();
        $products = unserialize($result[0]['products']);
        $price = 0;
        foreach($products as $item) {
            $itemp = new Product($item['id'], true, $this->context->language->id);
            $attr = new Attribute($item['id_attribute'], $this->context->language->id);
            $image_id = getProductAttributeImage($item['id'], $item['id_attribute'], $this->context->language->id) ? getProductAttributeImage($item['id'], $item['id_attribute'], $this->context->language->id) : $itemp->id;
            $product_arr[] = array(
                'id' => $itemp->id,
                'attr' => $attr->name,
                'name' => $itemp->name,
                'image' => $this->context->link->getImageLink($itemp->link_rewrite[$this->context->language->id], $image_id, 'cart_default'),
                'link' => $this->context->link->getProductLink($itemp, $itemp->link_rewrite, $itemp->category, null, null, $itemp->id_shop, $item['id_product_attribute']),
                'quantity' => $item['quantity'],
                'unit_price' => Tools::displayPrice(Tools::ps_round($itemp->getPriceStatic($itemp->id, true, $item['id_attribute'], 6),2), $this->context->currency),
                'total' => Tools::displayPrice(Tools::ps_round(($itemp->getPriceStatic($itemp->id, true, $item['id_attribute'], 6) * (int)$item['quantity']),2), $this->context->currency),
            );
            $price = $price + (float)($itemp->getPriceStatic($itemp->id, true, $item['id_attribute'], 6) * (int)$item['quantity']);
        }
        $out['quote_total'] = array(
            'quote_static' => $price,
            'quote_normal' => Tools::displayPrice(Tools::ps_round($price,2), $this->context->currency)
        );

        $out['products'] = $product_arr;
        $out[] = $result[0];
        return $out;
    }
    
}