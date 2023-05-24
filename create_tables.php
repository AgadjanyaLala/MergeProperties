<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
/*  таблица  iex_queue
    таблица задач:
        по удалению дублей свойств,
        обновлению значений этих свойств у товаров
        изменению типа свойтств со списка на строку
*/

$sql = "CREATE TABLE `iex_queue` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `done` SMALLINT DEFAULT(0),
    `stage` SMALLINT,
    `action` VARCHAR(30) DEFAULT(NULL),
    `duplicate_prop_id` INT DEFAULT(NULL),
    `duplicate_prop_type` VARCHAR(15) DEFAULT(NULL),
    `main_prop_id` INT DEFAULT(NULL),
    `main_prop_type` VARCHAR(15) DEFAULT(NULL),
    `product_id` VARCHAR(100) DEFAULT(NULL),
    `duplicate_prop_enum_xml` VARCHAR(255) DEFAULT(NULL),
    `duplicate_prop_value` TEXT DEFAULT(NULL),
    `iblock_id` INT,
    `date_create` DATETIME,
    `date_update` DATETIME DEFAULT(NULL),PRIMARY KEY (`id`));";
try{
    $res_table1 = Bitrix\Main\Application::getConnection()->query($sql);
}catch(Exception $e){
    echo "Произошла ошибка при создании таблицы! ". $e->getMessage()."\n";
}
if (!empty($res_table1) ){
    echo     "Таблица iex_queue успешно создана. \n";
}

/*  таблица iex_replacement_xml
    будут хранится старые и замененные хмл и значения свойств
*/

$sql = "CREATE TABLE `iex_replacement_xml` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `xml` VARCHAR(100) ,
    `replaced_by` VARCHAR(100) ,
    `date_create` DATETIME DEFAULT(NULL),
    PRIMARY KEY (`id`));";

try{
    $res_table2 = Bitrix\Main\Application::getConnection()->query($sql);
}catch(Exception $e){
   echo  "Произошла ошибка при создании таблицы! ". $e->getMessage()."\n";
}
if (!empty($res_table2) ){
    echo    "Таблица iex_replacement_xml успешно создана. \n";
}

