<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php');

// @todo Сделать ролевую модель и проверку доступа

$APPLICATION->IncludeComponent(
    "zylyov:standings.admin.standing.edit",
    ".default",
    array(
        "ID" => $_GET["ID"]
    ),
    false
);


require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');