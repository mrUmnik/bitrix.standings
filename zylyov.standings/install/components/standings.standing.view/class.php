<?php

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class ZylyovStandingsStandingViewComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        global $APPLICATION;
        if (!Loader::includeModule('zylyov.standings')) {
            ShowError(Loc::getMessage("ZS.MODULE_NOT_INSTALLED"));
            return;
        }
        $data = $this->loadData();
        if (!$data) {
            ShowError("Таблица не найдена");
            return;
        }

        $this->arResult['DATA'] = $data;
        $this->includeComponentTemplate();
    }

    protected function getTeamsList()
    {
        $result = [];
        $rsTeams = \Zylyov\Standings\Internals\TeamTable::getList([]);
        while ($arTeam = $rsTeams->fetch(\Bitrix\Main\Text\Converter::getHtmlConverter())) {
            $result[$arTeam['ID']] = $arTeam['NAME'];
        }
        return $result;
    }

    protected function loadData()
    {
        $arStanding = \Zylyov\Standings\Internals\StandingTable::getById($this->arParams['ID'])->fetch(\Bitrix\Main\Text\Converter::getHtmlConverter());
        if (!$arStanding) {
            return false;
        }
        $result = [
            'ID' => $arStanding['ID'],
            'NAME' => $arStanding['NAME'],
            'DEPTH' => min(ZS_MAX_STANDING_DEPTH, $arStanding['DEPTH']),
            'THIRD_PLACE_GAME' => $arStanding['THIRD_PLACE_GAME'],
        ];
        $matchTeamCollection = new \Zylyov\Standings\MatchTeamCollection(
            $result['DEPTH'],
            $result['THIRD_PLACE_GAME'] == 'Y'
        );
        $matchTeamCollection->setTeamsList($this->getTeamsList());
        $matchTeamCollection->load($result['ID']);
        $result['MATCH_TEAM_COLLECTION'] = $matchTeamCollection;
        return $result;
    }
}
