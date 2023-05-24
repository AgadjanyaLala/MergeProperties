<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
$time_start = time();

$sql = "SELECT `id`,`duplicate_prop_id`,`action`,`iblock_id` FROM `iex_queue` WHERE  `stage`='1' AND `done`='0';";
$results = Bitrix\Main\Application::getConnection()->query($sql);
while ($task = $results->Fetch()) {
    if ((time() - $time_start) > 30) {
        break;
    }
    $str = '';
    if ($task["action"] == "delete") {
        $res = CIBlockProperty::GetByID((int)$task["duplicate_prop_id"],$task["iblock_id"],false);

        if($ar_res = $res->GetNext()){
            $str ='Удалено свойство с ID: '.$task['duplicate_prop_id'].' и названием : '.$ar_res['NAME']."\n";
            file_put_contents('/app/www/tools/iexMergeProps/logs/deleted_props.txt', date('Y-m-d H:i:s').' deleted property with id: '.$task['duplicate_prop_id'].' and name: '.$ar_res['NAME']."\n", FILE_APPEND);
        }
        CIBlockProperty::Delete((int)$task["duplicate_prop_id"]);
        echo $str;
        $sql = "UPDATE `iex_queue` SET `done` = 1, `date_update` = '" . date("Y-m-d H:i:s") . "' WHERE `id` = '" . $task["id"] . "';";
        Bitrix\Main\Application::getConnection()->query($sql);
    }
}
