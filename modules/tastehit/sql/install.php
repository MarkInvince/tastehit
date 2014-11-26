<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'quotes_product` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_quote` varchar(100) NOT NULL,
    `id_shop` int(11) unsigned NOT NULL DEFAULT "1",
    `id_shop_group` int(11) unsigned NOT NULL DEFAULT "1",
    `id_lang` int(11) unsigned NOT NULL DEFAULT "1",
    `id_product` int(10) unsigned NOT NULL,
    `id_product_attribute` int(10) unsigned NOT NULL,
    `id_currency` int(10) unsigned NOT NULL,
    `id_guest` int(11) unsigned NOT NULL DEFAULT "0",
    `id_customer` int(11) unsigned NOT NULL DEFAULT "0",
    `quantity` int(10) unsigned NOT NULL DEFAULT "0",
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL ,
    PRIMARY KEY (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'quotes_bargains` (
    `id_bargain` int(11) NOT NULL AUTO_INCREMENT,
    `id_quote` int(11) NOT NULL,
    `bargain_whos` varchar(100) NOT NULL,
    `bargain_text` text NOT NULL,
    `date_add` datetime NOT NULL,
    `bargain_price` decimal(20,6) NOT NULL,
    `bargain_price_text` varchar(250) NOT NULL,
    `bargain_customer_confirm` tinyint(1) NOT NULL DEFAULT "0",
    PRIMARY KEY (`id_bargain`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'quotes` (
    `id_quote` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NOT NULL,
    `reference` varchar(250) NOT NULL,
    `quote_name` varchar(250) NOT NULL,
    `burgain_price` int(20) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `id_shop_group` int(11) NOT NULL,
    `id_lang` int(11) NOT NULL,
    `id_currency` int(11) NOT NULL,
    `id_customer` int(11) NOT NULL,
    `products` text NOT NULL,
    `date_add` datetime NOT NULL,
    `submited` tinyint(1) NOT NULL,
    PRIMARY KEY (`id_quote`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query)
    if (Db::getInstance()->execute($query) == false)
        return false;
