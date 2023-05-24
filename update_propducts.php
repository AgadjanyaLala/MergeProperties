<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');
$time_start = time();

$sql = "SELECT `id`,`product_id`,`duplicate_prop_id`,`duplicate_prop_type`,`main_prop_id`,`duplicate_prop_enum_xml`,`duplicate_prop_value`,`iblock_id` FROM `iex_queue` WHERE  `stage`='3' AND `done`='0' ;";
$results = Bitrix\Main\Application::getConnection()->query($sql);
while ($task = $results->Fetch()) {
    if ((time() - $time_start) > 30) {
        break;
    }
    $res = CIBlockProperty::GetByID($task['main_prop_id'], $task['iblock_id']);
    if ($ar_res = $res->GetNext()) {
        $mainPropCode = $ar_res['CODE'];
    }
    $arSelect = array("ID", "IBLOCK_ID", "PROPERTY_" . $mainPropCode);
    $arFilter = array("ID" => $task['product_id']);//,"ACTIVE"=>"Y"
    $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
    if (!empty($product = $res->GetNext())) {

        if (!empty($product["PROPERTY_" . $mainPropCode . "_VALUE"])) {
            $sql = "UPDATE `iex_queue` SET `done` = 2, `date_update` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = '" . $task["id"] . "';";
            Bitrix\Main\Application::getConnection()->query($sql);
            continue;
        }

        if ($task["duplicate_prop_type"] === "L") {
            $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $task['iblock_id'], "CODE" => $mainPropCode, "XML_ID" => $task["duplicate_prop_enum_xml"], "VALUE" => $task["duplicate_prop_value"]));
            if (empty($enum_fields = $property_enums->GetNext())) {
                $ibpenum = new CIBlockPropertyEnum;
                if ($PropID = $ibpenum->Add(array('PROPERTY_ID' => $task["main_prop_id"], 'VALUE' => $task["duplicate_prop_value"], "XML_ID" => $task["duplicate_prop_enum_xml"]))) {
                    CIBlockElement::SetPropertyValuesEx($task['product_id'], $task['iblock_id'], [$mainPropCode => $task['iblock_id']]);
                    $sql = "UPDATE `iex_queue` SET `done` = 1, `date_update` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = '" . $task["id"] . "';";
                     Bitrix\Main\Application::getConnection()->query($sql);
                    continue;
                }
            }
            CIBlockElement::SetPropertyValuesEx($task['product_id'], $task['iblock_id'], [$mainPropCode => $enum_fields["ID"]]);
            $sql = "UPDATE `iex_queue` SET `done` = 1, `date_update` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = '" . $task["id"] . "';";
            Bitrix\Main\Application::getConnection()->query($sql);
            continue;
        }
        if ($task["duplicate_prop_type"] === "S" || $task["duplicate_prop_type"] === "N" ){
            CIBlockElement::SetPropertyValuesEx($task['product_id'], $task['iblock_id'], [$mainPropCode => $task["duplicate_prop_value"]]);
            $sql = "UPDATE `iex_queue` SET `done` = 1, `date_update` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = '" . $task["id"] . "';";
            Bitrix\Main\Application::getConnection()->query($sql);
        }

    }

}

$sql = "SELECT `id`,`product_id`,`duplicate_prop_id`,`duplicate_prop_type`,`main_prop_id`,`duplicate_prop_enum_xml`,`duplicate_prop_value`,`iblock_id` FROM `iex_queue` WHERE  `stage`='3' AND `done`='0' ;";
$results = Bitrix\Main\Application::getConnection()->query($sql);
if (!$results->Fetch()){
    echo "Все товары успешно обновлены.";
}