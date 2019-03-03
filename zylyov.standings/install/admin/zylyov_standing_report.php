<?
$relPath = "modules/zylyov.standings/admin/zylyov_standing_report.php";
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/" . $relPath)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/local/" . $relPath);
} else {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/" . $relPath);
}