<?php

use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$APPLICATION->SetTitle("Отчет по таблице &laquo;" . $arResult['DATA']['NAME'] . "&raquo;");
$arResult['GRID_MANAGER']->DisplayList();