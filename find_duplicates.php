<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__.'/glossary_of_names.php');
/** @global  $glossaryMergeProp array */
 /** @global  $ArrIBlocId array */

foreach (IexMergeProps::$ArrIBlocId as $IBLOCK_ID){

$properties = CIBlockProperty::GetList(array("name" => "asc", "id" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID));
$arPropsName = [];
while ($propFields = $properties->GetNext()) {
    $name = (array_key_exists($propFields["NAME"], IexMergeProps::$glossaryMergeProp)) ? IexMergeProps::$glossaryMergeProp[$propFields["NAME"]] : $propFields["NAME"];
    $arPropsName[$name][$propFields["ID"]] = $propFields["PROPERTY_TYPE"];
}

foreach ($arPropsName as $propertyName => $properties) {
    $count = count($properties);
    if ($count === 1) {
        unset($arPropsName[$propertyName]);
        continue;
    }
    if (count(array_unique($arPropsName[$propertyName])) !== 1) {
        unset($arPropsName[$propertyName]);
        continue;
    }
    ksort($arPropsName[$propertyName]);

}

$arDeleteProps = [];
foreach ($arPropsName as $propertyName => $properties) {
    foreach ($properties as $propertyId => $propertyType) {
        $listValue = [];
        //свойство с кодом CML2_ARTICLE - всегда основной
        $property_change = CIBlockProperty::GetByID($propertyId, $IBLOCK_ID);
        if ($ar_res = $property_change->GetNext()) {
            if ($ar_res["CODE"] === "CML2_ARTICLE") {
                $arPropsName[$propertyName]["main_prop"] = $propertyId;
                break;
            }
        }

       /*$arSelect = array("ID", "IBLOCK_ID", "PROPERTY_" . $propertyId);
        $arFilter = array("!PROPERTY_" . $propertyId => false);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        ----------поиск первого непустого свойства - основное свойство------------//
        if ($res->SelectedRowsCount() > 5) {
        }*/

        $arPropsName[$propertyName]["main_prop"] = $propertyId;
                    break;
    }
}
print_r($arPropsName);
foreach ($arPropsName as $propertyName => $properties) {

    foreach ($properties as $propertyId => $propertyType) {
        if ($propertyId === "main_prop") {
            continue;
        }
        if ($propertyId === $properties["main_prop"]) {
            continue;
        }

        $sql = "INSERT INTO `iex_queue` (`stage`, `action`, `duplicate_prop_id`,`main_prop_id`,`iblock_id`,`date_create`) VALUES (1, 'delete','" . $propertyId . "','" . $properties["main_prop"] . "','" .$IBLOCK_ID . "','" . date("Y-m-d H:i:s") . "');";
        Bitrix\Main\Application::getConnection()->query($sql);

        $arSelect = array("ID", "IBLOCK_ID", "PROPERTY_" . $propertyId);
        $arFilter = array("!PROPERTY_" . $propertyId => false);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        //-------------------------обработка типов--------------//
        while ($product = $res->GetNext()) {

            if (!isset($product["~PROPERTY_" . $propertyId . "_VALUE"])) {
                continue;
            }

            if (isset($product["~PROPERTY_" . $propertyId . "_ENUM_ID"])) {
                $db_enum_list = CIBlockProperty::GetPropertyEnum($propertyId, array(), array("IBLOCK_ID" => $IBLOCK_ID, "ID" => $product["~PROPERTY_" . $propertyId . "_ENUM_ID"]));
                if ($ar_enum_list = $db_enum_list->GetNext()) {
                    $sql = "INSERT INTO `iex_queue` (`stage`, `action`, `duplicate_prop_id`,`main_prop_id`,`duplicate_prop_type`,`main_prop_type`,`product_id`,`duplicate_prop_enum_xml`,`duplicate_prop_value`,`iblock_id`,`date_create`) VALUES (3, 'update','" . $propertyId . "','" . $properties["main_prop"] . "','" . $propertyType . "','" . $propertyType . "','" . $product["ID"] . "','" . $ar_enum_list["XML_ID"] . "','" . $ar_enum_list["VALUE"] . "','" .$IBLOCK_ID ."','" . date("Y-m-d H:i:s") . "');";
                    Bitrix\Main\Application::getConnection()->query($sql);
                    continue;
                }
            }
            $sql = "INSERT INTO `iex_queue` (`stage`, `action`, `duplicate_prop_id`,`main_prop_id`,`duplicate_prop_type`,`main_prop_type`,`product_id`,`duplicate_prop_value`,`iblock_id`,`date_create`) VALUES (3, 'update','" . $propertyId . "','" . $properties["main_prop"] . "','" . $propertyType . "','" . $propertyType . "','" . $product["ID"] . "','" . $product["~PROPERTY_" . $propertyId . "_VALUE"] . "','" .$IBLOCK_ID . "','" . date("Y-m-d H:i:s") . "');";
            Bitrix\Main\Application::getConnection()->query($sql);

        }
    }
}
}
