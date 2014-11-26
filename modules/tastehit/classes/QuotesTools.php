<?php

function getProductAttributeImage($id_product, $id_product_attribute, $id_lang) {
    $mysql = '  SELECT pa.`id_product_attribute` , pa.`id_product` , pa.`price` , pac.`id_attribute` , al.`name` , paimg.`id_image`
                FROM  `ps_product_attribute` pa
                LEFT JOIN  `ps_product_attribute_combination` pac ON ( pa.`id_product_attribute` = pac.`id_product_attribute` )
                LEFT JOIN  `ps_product_attribute_image` paimg ON ( pac.`id_product_attribute` = paimg.`id_product_attribute` )
                LEFT JOIN  `ps_attribute` a ON ( pac.`id_attribute` = a.`id_attribute` )
                LEFT JOIN  `ps_attribute_lang` al ON ( al.`id_attribute` = a.`id_attribute` )
                WHERE pa.`id_product_attribute` = '.pSQL($id_product_attribute).'
                AND pa.`id_product` = '.pSQL($id_product).'
                AND  `id_lang` = '.pSQL($id_lang).' ORDER BY pa.`id_product_attribute` LIMIT 1';
    $row = Db::getInstance()->executeS($mysql);
    if(!$row)
        return array();
    return $row[0]['id_image'];
}

function quoteNum($id_customer) {
    $sql = "SELECT COUNT(`id_quote`) FROM `ps_quotes` WHERE `id_customer`=".$id_customer;
    $result = Db::getInstance()->getValue($sql);
    if($result)
        $result++;
    else
        $result = 1;
    return $result;
}

function quotesMailConfirm($to, $message, $subject){
    $headers   = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=utf-8";
    $headers[] = "From: ".Configuration::get('PS_SHOP_NAME')." <info@admin.com>";
    //$headers[] = "Reply-To: Recipient Name <receiver@domain3.com>";

//    $headers   = array();
//    $headers[] = "MIME-Version: 1.0";
//    $headers[] = "Content-type: text/plain; charset=iso-8859-1";
//    $headers[] = "From: ".Configuration::get('PS_SHOP_NAME')." <sender@domain.com>";
//    $headers[] = "Subject: ".$subject;
//    $headers[] = "X-Mailer: PHP/".phpversion();

    if(mail($to, $subject, $message, implode("\r\n", $headers)))
        return true;
    return false;
}