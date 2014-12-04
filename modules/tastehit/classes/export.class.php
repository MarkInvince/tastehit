<?php
class Export
{
    public static function getProducts() {
        $sql = 'SELECT p.id_product
                FROM `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'category` c
                    ON c.id_category = p.id_category_default
                WHERE p.active = 1 AND c.active = 1';
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function getBuyingHistory() {
        $sql = 'SELECT od.product_id, o.id_customer
                FROM `'._DB_PREFIX_.'order_detail` od
                LEFT JOIN `'._DB_PREFIX_.'orders` o
                    ON o.id_order = od.id_order';
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function clearDescription($description) {
        $description=preg_replace('!<[^>]*?>!', ' ',$description);
        $description=str_replace(array('&nbsp;','&','"','>','<','`'), array(' ', '&amp;', '&quot;', '&gt;', '&lt;', '&apos;'),$description);
        return $description;
    }
}