<?php

if (!defined('_PS_VERSION_'))
    exit;

include_once(_PS_MODULE_DIR_ . 'quotes/classes/QuotesTools.php');
class QuotesProductCart extends ObjectModel
{
    public $id;
    public $id_quote;
    public $id_shop;
    public $id_shop_group;
    public $id_lang;
    public $id_guest;
    public $id_customer;
    public $id_product;
    public $id_product_attribute;
    public $quantity;
    public $date_add;
    public $date_upd;

    private $operator = 'up';
    private $addquantity = 1;

    public function setOperator($operator) {
        $this->operator = $operator;
    }
    public function setQuantity($quantity) {
        $this->addquantity = $quantity;
    }
    public static $definition = array(
        'table' => 'quotes_product',
        'primary' => 'id',
        'fields' => array(
            'id_quote'      => 	array('type' => self::TYPE_STRING, 'required' => true),
            'id_shop'       => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_shop_group' => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_lang'       => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_product'    => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_product_attribute'    => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_guest'      => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'id_customer'   => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'quantity'      => 	array('type' => self::TYPE_INT,  'validate' => 'isUnsignedId'),
            'date_add'      => 	array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'      => 	array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id);
        if (!is_null($id_lang))
            $this->id_lang = (int)(Language::getLanguage($id_lang) !== false) ? $id_lang : Configuration::get('PS_LANG_DEFAULT');

        $this->context = Context::getContext();

    }

    public function add($autodate = true, $null_values = false )
    {
        if (!$this->id_lang)
            $this->id_lang = Configuration::get('PS_LANG_DEFAULT');
        if (!$this->id_shop)
            $this->id_shop = $this->context->shop->id;
        if(!$this->checkForContains())
            $return = parent::add($autodate);
        else {
            $return = $this->recountProduct();
        }
        return $return;
    }

    public function update($null_values = false)
    {
        $return = parent::update();
        return $return;
    }

    public function delete()
    {
        if (!Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'quotes_product` WHERE `id` = '.(int)$this->id.' AND `id_quote` LIKE "'.$this->id_quote.'"'))
            return false;

        return parent::delete();
    }

    public function checkForContains() {
        if (!$this->id_quote)
            return false;
        $result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'quotes_product` qp
			WHERE qp.`id_product` = '.(int)$this->id_product.' AND qp.`id_quote` LIKE "'.$this->id_quote.'" AND qp.`id_product_attribute` = '.$this->id_product_attribute
        );
        if(!empty($result))
            return true;
        else
            return false;
    }
    public function recountProductByValue($id_product, $id_product_attribute, $value, $operator = 'add', $id_quote = false) {
        if (!$id_quote) {
            return false;
        }

        $row = Db::getInstance()->getRow('
			SELECT qp.`quantity`, qp.`id_product_attribute`
			FROM `'._DB_PREFIX_.'quotes_product` qp
			WHERE qp.`id_product` = '.(int)$id_product.' AND qp.`id_quote` LIKE "'.$id_quote.'" AND qp.`id_product_attribute` = '.$id_product_attribute
        );

        $current_qty = (int)$row['quantity'];
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));

        if (!Validate::isLoadedObject($product))
            die(Tools::displayError());

        if ((int)$current_qty < 0)
            return $this->deleteProduct($id_product, $id_product_attribute);
        elseif (!$product->available_for_order || !$product->active)
            return false;
        else
        {
            switch($operator) {
                case "add":
                    $current_qty = $current_qty + (int)$value;
                    break;
                case "remove":
                    $current_qty = $current_qty - (int)$value;
                    break;
                case "set":
                    $current_qty = (int)$value;
                    break;
                default:
                    break;
            }

            if ((int)$current_qty < 0)
                return $this->deleteProduct($id_product, $row['id_product_attribute']);

            //update current product in cart
            $update = Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'quotes_product`
					SET `quantity` = '.(int)$current_qty.', `date_upd` = "'.date('Y-m-d H:i:s', time()).'"
					WHERE `id_product` = '.(int)$id_product. ' AND `id_quote` LIKE "'.$id_quote.'" AND `id_product_attribute` = '.$id_product_attribute.'
					LIMIT 1'
            );
            return $update;
        }
    }
    public function recountProduct() {
        if (!$this->id_product || !$this->id_quote) {
            return false;
        }

        $row = Db::getInstance()->getRow('
			SELECT qp.`quantity`, qp.`id_product_attribute`
			FROM `'._DB_PREFIX_.'quotes_product` qp
			WHERE qp.`id_product` = '.(int)$this->id_product.' AND qp.`id_quote` LIKE "'.$this->id_quote.'" AND qp.`id_product_attribute` = '.$this->id_product_attribute
        );

        $current_qty = (int)$row['quantity'];
        $id_product = (int)$this->id_product;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'));

        if (!Validate::isLoadedObject($product))
            die(Tools::displayError());

        if ((int)$current_qty < 0)
            return $this->deleteProduct($id_product, $row['id_product_attribute']);
        elseif (!$product->available_for_order || !$product->active)
            return false;
        else
        {
            switch($this->operator) {
                case 'up':
                    $current_qty = $current_qty + (int)$this->addquantity;
                    break;
                case 'down':
                    $current_qty = $current_qty - (int)$this->addquantity;
                    break;
                default:
                    break;
            }

            if ((int)$current_qty < 0)
                return $this->deleteProduct($id_product, $row['id_product_attribute']);

            //update current product in cart
            $update = Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'quotes_product`
					SET `quantity` = '.(int)$current_qty.', `date_upd` = "'.date('Y-m-d H:i:s', time()).'"
					WHERE `id_product` = '.(int)$this->id_product. ' AND `id_quote` LIKE "'.$this->id_quote.'" AND `id_product_attribute` = '.$this->id_product_attribute.'
					LIMIT 1'
            );
            return $update;
        }
    }

    /**
     * Return cart products
     *
     * @result array Products
     */
    public function getProducts()
    {
        if (!$this->id_quote)
            return array();

        $products_ids = array();
        $products = array();
        $result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'quotes_product` WHERE `id_quote` LIKE "'.$this->id_quote.'"');
        if (empty($result))
            return array();
        $order_total = 0;
        foreach ($result as $key => $row) {
            if(is_numeric($key)) {
                $products_ids[] = $row['id_product'];
                $product = array();
                $p_obj = new Product($row['id_product'], true, $this->context->language->id);
                $link = new Link;
                if (Validate::isLoadedObject($p_obj)) {
                    $product['id'] = $p_obj->id;
                    $product['title'] = $p_obj->name;
                    $product['id_shop'] = $this->id_shop;
                    $product['category'] = $p_obj->category;
                    $product['id_attribute'] = $row['id_product_attribute'];
                    $product['link'] = $link->getProductLink($p_obj, $p_obj->link_rewrite, $p_obj->category, null, null, $p_obj->id_shop, $this->id_product_attribute);
                    $product['link_rewrite'] = $p_obj->link_rewrite;

                    if($row['id_product_attribute']!= 0) {
                        $id_image = getProductAttributeImage($p_obj->id, $row['id_product_attribute'], $this->context->language->id);
                        if (!$id_image){
                            $image = $p_obj->getCover($p_obj->id);
                            $id_image = $image['id_image'];
                        }
                    }
                    else {
                        $image = $p_obj->getCover($p_obj->id);
                        $id_image = $image['id_image'];
                    }

                    $product['id_image'] = $id_image;
                    $product['quantity'] = $row['quantity'];
                    $product['unit_price'] = Tools::displayPrice(Tools::ps_round(Product::getPriceStatic($p_obj->id, true, NULL, 6),2), $this->context->currency);
                    $product['total_price'] = Tools::displayPrice(Tools::ps_round((Product::getPriceStatic($p_obj->id, true, NULL, 6) * $row['quantity']),2), $this->context->currency);
                    $products[] = $product;

                    $order_total += Tools::ps_round((Product::getPriceStatic($p_obj->id, true, NULL, 6) * $row['quantity']),2);
                }
            }
        }
        $cart = array('total' => Tools::displayPrice($order_total, $this->context->currency));
        if(!empty($products_ids))
            Product::cacheProductsFeatures($products_ids);

        return array($products, $cart);
    }

    public function deleteProduct($id_product, $id_product_attribute = 0)
    {
        /* Product deletion */
        $result = Db::getInstance()->execute('
		DELETE FROM `' . _DB_PREFIX_ . 'quotes_product`
		WHERE `id_product` = ' . (int)$id_product . ' AND `id_quote` LIKE "' . $this->id_quote.'" AND `id_product_attribute` = '.(int)$id_product_attribute);

        if ($result) {
            //$this->update(true);
            return true;
        }
        return false;
    }
    public function deleteAllProduct()
    {
        /* Product deletion */
        $result = Db::getInstance()->execute('
		DELETE FROM `' . _DB_PREFIX_ . 'quotes_product`
		WHERE `id_quote` LIKE "' . $this->id_quote.'"');

        if ($result) {
            return true;
        }
        return false;
    }
}