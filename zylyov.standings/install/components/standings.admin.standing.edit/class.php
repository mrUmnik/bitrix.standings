<?php

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class ZylyovStandingsStandingEditComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if ((int)$arParams['ID'] <= 0) {
            $arParams['NEW'] = true;
            $arParams['ID'] = null;
        } else {
            $arParams['NEW'] = false;
            $arParams['ID'] = (int)$arParams['ID'];
        }
        return $arParams;
    }

    public function executeComponent()
    {
        global $APPLICATION;
        if (!Loader::includeModule('zylyov.standings')) {
            ShowError(Loc::getMessage("ZS.MODULE_NOT_INSTALLED"));
            return;
        }
        if ($this->arParams['NEW']) {
            $data = $this->getDefaultData();
        } else {
            $data = $this->loadData();
            if (!$data) {
                ShowError("Таблица не найдена");
                return;
            }
        }

        $this->arResult['DATA'] = $data;
        $this->arResult['FIELD_NAMES'] = $this->getFieldNames();
        $this->arResult['TEAMS_LIST'] = $this->getTeamsList();
        $this->arResult['MAX_USERS_LIST'] = $this->getMaxUsersList();

        $this->arResult['ERRORS'] = [];
        if ($this->request->isPost()) {
            $this->processPostAction();
            if (empty($this->arResult['ERRORS'])) {
                if ($this->request->get('apply')) {
                    $url = "zylyov_standing_edit.php?ID=" . $this->arParams['ID'] . "&lang=" . LANGUAGE_ID;
                } else {
                    $url = "zylyov_standing_list.php?lang=" . LANGUAGE_ID;
                }
                LocalRedirect($url);
                return;
            }
        }

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

    protected function getMaxUsersList()
    {
        $result = range(0, ZS_MAX_STANDING_DEPTH);
        array_walk($result, function (&$value) {
            $value = pow(2, $value);
        });
        unset($result[0]);
        return $result;
    }

    protected function getDefaultData()
    {
        return [
            'NAME' => '',
            'DEPTH' => min(ZS_MAX_STANDING_DEPTH, 3),
            'THIRD_PLACE_GAME' => 'Y',
            'MATCHES' => []
        ];
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
        $result['MATCHES'] = $matchTeamCollection->getAsArray();

        return $result;
    }

    protected function getFieldNames()
    {
        $result = [];
        $fields = \Zylyov\Standings\Internals\StandingTable::getMap();
        foreach ($fields as $entity) {
            $result[$entity->getName()] = $entity->getTitle();
        }
        $result['MAX_PARTICIPANTS_COUNT'] = Loc::getMessage("ZS.MAX_PARTICIPANTS_COUNT");
        return $result;
    }

    protected function processPostAction()
    {
        $arFields = [
            'NAME' => $this->request->get('NAME'),
            'DEPTH' => $this->request->get('DEPTH'),
            'THIRD_PLACE_GAME' => $this->request->get('THIRD_PLACE_GAME') == 'Y' ? 'Y' : 'N',
        ];
        $this->arResult['DATA'] = array_merge($this->arResult['DATA'], $arFields);
        $valid = $this->validateFields($arFields);
        $matchTeamValid = true;
        try {
            $matchTeamCollection = new \Zylyov\Standings\MatchTeamCollection(
                (int)$arFields['DEPTH'],
                $arFields['THIRD_PLACE_GAME'] == 'Y'
            );
            if (!$this->arParams['NEW']) {
                $matchTeamCollection->setStandingId($this->arParams['ID']);
            }
            $matchTeamCollection->setTeamsList($this->arResult['TEAMS_LIST']);
            $matchTeamCollection->initFormArray($this->request->get('MATCH_TEAM'));
            $this->arResult['DATA']['MATCHES'] = $matchTeamCollection->getAsArray();
        } catch (\Zylyov\Standings\Exception\MatchTeamCollectionException $e) {
            $this->arResult['ERRORS'][] = $e->getMessage();
            $matchTeamValid = false;
        }
        if (!$valid || !$matchTeamValid) {
            return;
        }

        $connection = \Bitrix\Main\Application::getConnection();
        $connection->startTransaction();
        if ($this->arParams['NEW']) {
            $saveResult = \Zylyov\Standings\Internals\StandingTable::add($arFields);
        } else {
            $saveResult = \Zylyov\Standings\Internals\StandingTable::update($this->arParams['ID'], $arFields);
        }
        if (!$saveResult->isSuccess()) {
            $this->arResult['ERRORS'] += $saveResult->getErrorMessages();
            $connection->rollbackTransaction();
        } else {
            if ($this->arParams['NEW']) {
                $matchTeamCollection->setStandingId($saveResult->getId());
            }
            $saveCollectionResult = $matchTeamCollection->save();
            if (!$saveCollectionResult->isSuccess()) {
                $this->arResult['ERRORS'] += $saveCollectionResult->getErrorMessages();
                $connection->rollbackTransaction();
            } else {
                $connection->commitTransaction();
                $this->arParams['ID'] = $saveResult->getId();
            }
        }

    }

    protected function validateFields($arFields)
    {
        $errors = [];
        if (!strlen($arFields['NAME'])) {
            $errors[] = Loc::getMessage("ZS.FIELD_NAME_EMPTY");
        }
        if (!array_key_exists($arFields['DEPTH'], $this->arResult['MAX_USERS_LIST'])) {
            $errors[] = Loc::getMessage("ZS.FIELD_DEPTH_WRONG");
        }
        $this->arResult['ERRORS'] += $errors;
        return empty($errors);
    }
}
