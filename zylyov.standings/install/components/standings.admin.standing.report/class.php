<?php

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class ZylyovStandingsStandingReportComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('zylyov.standings')) {
            ShowError(Loc::getMessage("ZS.MODULE_NOT_INSTALLED"));
            return;
        }
        $this->arResult['DATA'] = $this->loadData($this->arParams['ID']);
        if (!$this->arResult['DATA']) {
            ShowError('Таблица не найдена');
            return;
        }

        $this->initGrid();
        $this->initColumns();
        $this->loadRecords();

        $this->includeComponentTemplate();
    }

    protected function initGrid()
    {
        $this->arResult['GRID_ID'] = 'zylyov_standings_admin_standing_report';
        $this->arResult['GRID_MANAGER'] = new \CAdminUiList(
            $this->arResult['GRID_ID'],
            new \CAdminSorting($this->arResult['GRID_ID'], "PLACE", "desc"));
    }

    protected function loadRecords()
    {

        $result = [];

        $rsItems = \Zylyov\Standings\Internals\MatchTeamTable::query()
            ->where('STANDING_ID', $this->arResult['DATA']['ID'])
            ->whereNotNull('SCORE')
            ->addGroup('TEAM_ID')
            ->addSelect("TEAM_ID")
            ->addSelect("TEAM.NAME", 'NAME')
            ->addSelect(\Bitrix\Main\ORM\Query\Query::expr()->min('PLACE'), "BEST_PLACE")
            ->addSelect(\Bitrix\Main\ORM\Query\Query::expr()->sum('SCORE'), "TOTAL_SCORE")
            ->addSelect(\Bitrix\Main\ORM\Query\Query::expr()->avg('SCORE'), "AVG_SCORE")
            ->addSelect(\Bitrix\Main\ORM\Query\Query::expr()->max('SCORE'), "MAX_SCORE")
            ->setOrder(['SMART_BEST_PLACE'])
            ->registerRuntimeField(
                new \Bitrix\Main\ORM\Fields\ExpressionField(
                    'SMART_BEST_PLACE',
                    "(CASE WHEN MIN(PLACE) IS NULL then 999 ELSE MIN(PLACE) END)"
                ))
            ->exec();

        while ($arItem = $rsItems->fetch(\Bitrix\Main\Text\Converter::getHtmlConverter())) {
            $this->arResult['GRID_MANAGER']->addRow(
                $arItem['TEAM_ID'],
                [
                    'ID' => $arItem['TEAM_ID'],
                    'NAME' => $arItem['NAME'],
                    'BEST_PLACE' => $arItem['BEST_PLACE'],
                    'TOTAL_SCORE' => $arItem['TOTAL_SCORE'],
                    'AVG_SCORE' => $arItem['AVG_SCORE'],
                    'MAX_SCORE' => $arItem['MAX_SCORE'],
                ]
            );
        }
        return $result;
    }

    protected function initColumns()
    {
        $this->arResult['GRID_MANAGER']->addHeaders([
            [
                "id" => "ID",
                "content" => "ID",
                "default" => true,
            ],
            [
                "id" => "NAME",
                "content" => "Команда",
                "default" => true
            ],
            [
                "id" => "BEST_PLACE",
                "content" => "Место в турнире",
                "default" => true
            ],
            [
                "id" => "TOTAL_SCORE",
                "content" => "Общая результативность за турнир",
                "default" => true
            ],
            [
                "id" => "AVG_SCORE",
                "content" => "Средняя результативность за игру",
                "default" => true
            ],
            [
                "id" => "MAX_SCORE",
                "content" => "Лучшая результативность за игру",
                "default" => true
            ],
        ]);
    }

    public function loadData($id)
    {
        $id = (int)$id;
        return \Zylyov\Standings\Internals\StandingTable::getById($id)->fetch(\Bitrix\Main\Text\Converter::getHtmlConverter());
    }
}
