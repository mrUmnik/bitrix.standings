<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<?
$APPLICATION->IncludeComponent(
    "zylyov:standings.standing.view",
    ".default",
    array(
        "ID" => 1
    ),
    false
);
//@todo настроить визуальный режим выбора ID турнирной таблицы
?><? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>