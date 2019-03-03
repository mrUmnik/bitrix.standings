<?php

use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$APPLICATION->SetTitle($arParams['NEW'] ? Loc::getMessage("ZS.TITLE_NEW") : Loc::getMessage("ZS.TITLE_EXISTED"));
\Bitrix\Main\UI\Extension::load(['ui.vue', 'ui.vue.vuex']);
\CJSCore::init(["popup", "zylyov.standings.components.match"]);
$jsParams = [
    'TEAMS_LIST' => $arResult['TEAMS_LIST'],
    'DEPTH' => $arResult['DATA']['DEPTH'],
    'THIRD_PLACE_GAME' => ($arResult['DATA']['THIRD_PLACE_GAME'] == 'Y'),
    'MATCHES' => $arResult['DATA']['MATCHES']
];
?>
<form method="POST" action="">
    <?
    if (!$arParams['NEW']) {
        $aMenu[] = array(
            "TEXT" => Loc::getMessage("ZS.REPORT_LINK"),
            "TITLE" => Loc::getMessage("ZS.REPORT_LINK"),
            'LINK' => "zylyov_standing_report.php?ID={$arParams['ID']}&lang=" . LANGUAGE_ID
        );
        $menuPanel = new CAdminContextMenu($aMenu);
        $menuPanel->Show();
    }
    ?>
    <?
    if (!empty($arResult['ERRORS'])) {
        CAdminMessage::ShowMessage(implode('<br>', $arResult['ERRORS']));
    }
    ?>
    <?= bitrix_sessid_post() ?>
    <?
    $tabControl = new CAdminTabControl("tabControl", array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("ZS.TABLE_TITLE"), "TITLE" => Loc::getMessage("ZS.TABLE_TITLE")),
    ));
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr class="adm-detail-required-field">
        <td><?= $arResult['FIELD_NAMES']['NAME'] ?>:</td>
        <td><input type="text" name="NAME" value="<?= $arResult['DATA']['NAME'] ?>" size="50"></td>
    </tr>
    <tr class="adm-detail-required-field">
        <td><?= $arResult['FIELD_NAMES']['MAX_PARTICIPANTS_COUNT'] ?>:</td>
        <td>
            <select name="DEPTH" id="standing_depth">
                <? foreach ($arResult['MAX_USERS_LIST'] as $value => $title): ?>
                    <option value="<?= $value ?>"<? if ($value == $arResult['DATA']['DEPTH']): ?> selected<? endif; ?>>
                        <?= $title ?>
                    </option>
                <? endforeach; ?>
            </select>
        </td>
    </tr>
    <tr class="adm-detail-required-field">
        <td><?= $arResult['FIELD_NAMES']['THIRD_PLACE_GAME'] ?>:</td>
        <td><input type="checkbox" id="standing_third_place_game"
                   name="THIRD_PLACE_GAME" <? if ($arResult['DATA']['THIRD_PLACE_GAME'] == 'Y'): ?> checked<? endif; ?>
                   value="Y"></td>
    </tr>
    <tr>
        <td colspan="2">
            <div id="standing-app"></div>
        </td>
    </tr>

    <?
    $tabControl->EndTab();
    $tabControl->Buttons(array(
        "back_url" => "/bitrix/admin/zylyov_standing_list.php?lang=" . LANGUAGE_ID
    ));
    $tabControl->End();
    ?>

</form>

<script>
    BX.ready(function () {
        new BX.Zylyov.Standings.standing('standing-app', <?=CUtil::PhpToJSObject($jsParams)?>);
    });
</script>