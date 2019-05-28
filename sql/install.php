<?php
/**
* 2007-2019 PrestaShop
*
*  @author    Farmalisto <alejandro.villegas@farmalisto.com.co>
*  @copyright 2007-2019 Farmalisto
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `ps_ct_transactions_history` (
    `id_transaction`  int(11) NOT NULL AUTO_INCREMENT ,
`Country__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`Contact_Num__c`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`Payment_Type__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`Comment__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X1_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`X1_Quantity__c`  int(11) NOT NULL ,
`X1_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X1_Price__c`  decimal(20,6) NOT NULL ,
`X1_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`X1_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`X2_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X2_Quantity__c`  int(11) NULL DEFAULT NULL ,
`X2_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X2_Price__c`  decimal(20,6) NULL DEFAULT NULL ,
`X2_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X2_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`X3_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X3_Quantity__c`  int(11) NULL DEFAULT NULL ,
`X3_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X3_Price__c`  decimal(20,6) NULL DEFAULT NULL ,
`X3_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X3_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`X4_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X4_Quantity__c`  int(11) NULL DEFAULT NULL ,
`X4_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X4_Price__c`  decimal(20,6) NULL DEFAULT NULL ,
`X4_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X4_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`X5_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X5_Quantity__c`  int(11) NULL DEFAULT NULL ,
`X5_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X5_Price__c`  decimal(20,6) NULL DEFAULT NULL ,
`X5_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X5_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`X6_Product__c`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X6_Quantity__c`  int(11) NULL DEFAULT NULL ,
`X6_Lot__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X6_Price__c`  decimal(20,6) NULL DEFAULT NULL ,
`X6_SKU__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`X6_IVA__c`  decimal(20,6) NULL DEFAULT NULL ,
`Delivery_Time__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`Transaction_Date__c`  date NULL DEFAULT NULL ,
`Comment_Alternative_Address__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`Comment_Other__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`Delivered__c`  tinyint(1) NOT NULL ,
`Vendor_Order_Id__c`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`Order_Value__c`  decimal(20,6) NOT NULL ,
`Discount_Applied__c`  decimal(20,6) NULL DEFAULT NULL ,
`Origin__c`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`Loyalty_Points__c`  int(11) NULL DEFAULT NULL ,
`created_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id_transaction`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=3
ROW_FORMAT=COMPACT;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
