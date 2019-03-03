<?php

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class ZylyovStandingsStandingListComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('zylyov.standings')) {
            ShowError(Loc::getMessage("ZS.MODULE_NOT_INSTALLED"));
            return;
        }
        $this->initGrid();
        $this->initColumns();
        $this->loadRecords();
        $this->initContextMenu();

        $this->includeComponentTemplate();
    }

    protected function initGrid()
    {
        $this->arResult['GRID_ID'] = 'zylyov_standings_admin_standing_list';
        $this->arResult['GRID_MANAGER'] = new \CAdminUiList(
            $this->arResult['GRID_ID'],
            new \CAdminSorting($this->arResult['GRID_ID'], "ID", "asc"));
    }

    protected function loadRecords()
    {

        $result = [];
        $rsStandings = \Zylyov\Standings\Internals\StandingTable::getList();
        while ($arStanding = $rsStandings->fetch(\Bitrix\Main\Text\Converter::getHtmlConverter())) {
            $arStanding['MAX_PARTICIPANTS_COUNT'] = pow(2, $arStanding['DEPTH']);
            $arStanding['THIRD_PLACE_GAME'] = $arStanding['THIRD_PLACE_GAME'] == 'Y' ? Loc::getMessage("ZS.THIRD_PLACE_GAME_YES") : "";
            $row = $this->arResult['GRID_MANAGER']->addRow(
                $arStanding['ID'],
                $arStanding
            );
            $row->AddViewField("NAME", '<a href="zylyov_standing_edit.php?ID=' . $arStanding['ID'] . '&lang=' . LANGUAGE_ID . '">' . $arStanding['NAME'] . '</a>');
        }
        // @todo Сделать пейджинг и сортировку
        // @todo Сделать удаление таблицы
        return $result;
    }

    protected function initColumns()
    {
        $fields = \Zylyov\Standings\Internals\StandingTable::getMap();
        $this->arResult['GRID_MANAGER']->addHeaders([
            [
                "id" => "ID",
                "content" => "ID",
                "default" => true,
            ],
            [
                "id" => "NAME",
                "content" => $fields['NAME']->getTitle(),
                "default" => true
            ],
            [
                "id" => "MAX_PARTICIPANTS_COUNT",
                "content" => Loc::getMessage("ZS.MAX_PARTICIPANTS_COUNT"),
                "default" => true
            ],
            [
                "id" => "THIRD_PLACE_GAME",
                "content" => $fields['THIRD_PLACE_GAME']->getTitle(),
                "default" => true
            ],
        ]);
    }

    protected function initContextMenu()
    {
        $this->arResult['GRID_MANAGER']->AddAdminContextMenu([
            [
                "TEXT" => Loc::getMessage("ZS.ADD_STANDING_BUTTON"),
                "LINK" => "zylyov_standing_edit.php?lang=" . LANGUAGE_ID,
                "TITLE" => Loc::getMessage("ZS.ADD_STANDING_BUTTON"),
                "ICON" => "btn_new"
            ]
        ]);
    }
}
