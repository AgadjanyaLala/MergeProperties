<?php
class IexMergeProps {

    //массив $glossaryMergeProp заполняем смысловыми дублями из свойств торгового каталога и торговых предложений,
    public static $glossaryMergeProp = [
        "name_duplicate_property" => "name_main_property",
    ];
//comment 1
    //$ArrIBlocId массив с ID каталогов: торгового каталога и торговых предложений
    public static $arIBlocId = [
        "CATALOG_IBLOCK_ID",
        "OFFERS_IBLOCK_ID",
    ];
}