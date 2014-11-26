<?php

if (!defined('_PS_VERSION_'))
    exit;

class QuotesObj
{
    public function getQuotesByCustomer($id_customer)
    {
        if (!$id_customer)
            return false;

        $sql = "SELECT * FROM `"._DB_PREFIX_."quotes` WHERE `id_customer` = ".$id_customer." ORDER BY `id_quote` DESC";

        return Db::getInstance()->executeS($sql);

    }

    public function getQuoteInfo($id_quote = false) {
        if (!$id_quote)
            return false;

        $sql = "SELECT * FROM `"._DB_PREFIX_."quotes` WHERE `id_quote` = ".$id_quote;

        return Db::getInstance()->executeS($sql);
    }

    public function renameQuote($id_quote = false, $quoteName) {
        if (!$id_quote)
            return false;

        $sql = "UPDATE `"._DB_PREFIX_."quotes` SET
                    `quote_name` = '".$quoteName."'
                        WHERE `id_quote`=".$id_quote;

        return Db::getInstance()->execute($sql);
    }

    public function getBargains($id_quote = false) {
        if (!$id_quote)
            return false;
        $sql = "SELECT * FROM `"._DB_PREFIX_."quotes_bargains` WHERE `id_quote`=".$id_quote." ORDER BY `id_bargain` DESC";

        return $result = Db::getInstance()->executeS($sql);
    }

    public function addQuoteBargain($id_quote = false, $text, $whos = 'customer', $price = 0, $price_text = '') {
        if (!$id_quote)
            return false;
        $date_add = date('Y-m-d H:i:s', time());
        $sql = "INSERT INTO `"._DB_PREFIX_."quotes_bargains` SET
                    `id_quote` = ".$id_quote.",
                    `bargain_whos` = '".$whos."',
                    `bargain_text` = '".$text."',
                    `date_add` = '".$date_add."',
                    `bargain_price` = ".$price.",
                    `bargain_price_text` = '".$price_text."',
                    `bargain_customer_confirm` = 0
        ";

        $result = Db::getInstance()->execute($sql);

        if ($result) {
            $sql = "UPDATE `"._DB_PREFIX_."quotes` SET `burgain_price` = ".$price." WHERE `id_quote`=".$id_quote;
            if(Db::getInstance()->execute($sql))
                return true;
        }

        return $result;
    }

    public function deleteBargain($id_bargain = false) {
        if (!$id_bargain)
            return false;
        $sql = "DELETE FROM `"._DB_PREFIX_."quotes_bargains` WHERE `id_bargain`=".$id_bargain;
        return Db::getInstance()->execute($sql);
    }

    public function submitBargain($id_bargain = false, $action, $id_quote) {
        if (!$id_bargain)
            return false;

        if($action == 'reject') {
            $sql = "UPDATE `"._DB_PREFIX_."quotes_bargains` SET
                    `bargain_customer_confirm` = 2
                        WHERE `id_bargain`=".$id_bargain;
            return Db::getInstance()->execute($sql);
        }
        elseif($action == 'accept') {
            $sql = "UPDATE `"._DB_PREFIX_."quotes_bargains` SET
                `bargain_customer_confirm` = 1
                    WHERE `id_bargain`=".$id_bargain;
            if(Db::getInstance()->execute($sql)) {
                $sql = "UPDATE `"._DB_PREFIX_."quotes` SET
                `submited` = 1
                    WHERE `id_quote`=".$id_quote;
                if(Db::getInstance()->execute($sql))
                    return true;
            }
        }
        return false;
    }

    public function submitTransformQuote($id_quote) {
        if (!$id_quote)
            return false;
        $sql = "UPDATE `"._DB_PREFIX_."quotes` SET
                `submited` = 2
                    WHERE `id_quote`=".$id_quote;
        if(Db::getInstance()->execute($sql))
            return true;

        return false;
    }
}