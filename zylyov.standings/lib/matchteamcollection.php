<?php /** @noinspection PhpMethodParametersCountMismatchInspection */

namespace Zylyov\Standings;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;
use Zylyov\Standings\Exception\MatchTeamCollectionException;
use Zylyov\Standings\Internals\MatchTeamTable;

class MatchTeamCollection
{
    protected $standingId;
    protected $depth;
    protected $thirdPlaceMatch;
    protected $arTeams = [];
    protected $arMatchTeams = [];

    public function __construct(int $depth, bool $thirdPlaceMatch)
    {
        $this->depth = $depth;
        $this->thirdPlaceMatch = $thirdPlaceMatch;
        $this->initEmptyCollection();
    }

    public function setStandingId(int $standingId)
    {
        $this->standingId = $standingId;
    }

    public function getStandingId()
    {
        return $this->standingId;
    }

    public function setTeamsList(array $arTeamsList)
    {
        $this->arTeams = $arTeamsList;
    }

    public function getTeamName($teamId)
    {
        return $this->arTeams[$teamId];
    }

    public function initFormArray(array $arMatchTeams)
    {
        for ($depth = $this->depth; $depth >= 0; $depth--) {
            $maxPosition = $this->getMaxPosition($depth);
            for ($position = 0; $position < $maxPosition; $position++) {
                if (isset($arMatchTeams[$depth][$position])) {
                    /**
                     * @var $matchTeam MatchTeam
                     */
                    $matchTeam = $this->arMatchTeams[$depth][$position];
                    $arMatchTeam = $arMatchTeams[$depth][$position];
                    if (strlen($arMatchTeam['SCORE'])) {
                        $matchTeam->setScore((int)$arMatchTeam['SCORE']);
                    }
                    if ($arMatchTeam['TEAM_ID'] > 0) {
                        if (array_key_exists($arMatchTeam['TEAM_ID'], $this->arTeams)) {
                            $matchTeam->setTeamId($arMatchTeam['TEAM_ID']);
                        } else {
                            throw new MatchTeamCollectionException(Loc::getMessage("ZS.MATCHTEAM_COLLECTION_EXCEPTION_WRONG_TEAM_ID"));
                        }
                    }
                    // @todo доплнительная валидация ID
                    if (intval($arMatchTeam['ID']) > 0) {
                        $matchTeam->setId($arMatchTeam['ID']);
                    }
                }
            }
        }
    }

    public function load(int $standingId)
    {
        $this->setStandingId($standingId);
        $arMatchItems = [];
        $savedItems = $this->getExistedItems();
        foreach ($savedItems as $arItem) {
            $arMatchItems[$arItem['DEPTH']][$arItem['POSITION']] = $arItem;
        }
        $this->initFormArray($arMatchItems);
    }

    protected function getExistedItems()
    {
        $result = [];
        $rsMatchTeams = MatchTeamTable::getList(['filter' => ['STANDING_ID' => (int)$this->getStandingId()]]);
        while ($arMatchItem = $rsMatchTeams->fetch()) {
            $result[$arMatchItem['ID']] = $arMatchItem;
        }
        return $result;
    }

    public function save()
    {
        $result = new Result();
        if (!$this->getStandingId()) {
            $result->addError(new Error(Loc::getMessage("ZS.EMPTY_STANDING_ID")));
            return $result;
        }
        $this->setPlaces(); // подсчет призовых мест пл турнирной таблице
        // необходимо обходить по убыванию depth для корректного сохранения связей
        $arMatchTeams = $this->arMatchTeams;
        ksort($arMatchTeams, SORT_NUMERIC);
        $arMatchTeams = array_reverse($arMatchTeams, true);

        $existedItems = $this->getExistedItems();

        foreach ($arMatchTeams as $depth => $matchTeamsByDepth) {
            foreach ($matchTeamsByDepth as $position => $matchTeam) {
                if ($depth == 0) { // корневые элементы, т.е. победитель и 3 место не сохраняются
                    continue;
                }
                /**
                 * @var $matchTeam MatchTeam
                 */
                $saveResult = $matchTeam->save();
                if (!$saveResult->isSuccess()) {
                    $result->addErrors($saveResult->getErrors());
                    return $result;
                }
                unset($existedItems[$matchTeam->getId()]);
            }
        }
        // удаляются записи, выпавшие из турнирной таблицы
        foreach ($existedItems as $arOldItem) {
            MatchTeamTable::delete($arOldItem['ID']);
        }
        return $result;
    }

    public function getAsArray()
    {
        $result = [];
        // необходимо обходить по убыванию depth для корректного сохранения связей
        $arMatchTeams = $this->arMatchTeams;
        ksort($arMatchTeams, SORT_NUMERIC);
        $arMatchTeams = array_reverse($arMatchTeams, true);

        foreach ($arMatchTeams as $depth => $matchTeamsByDepth) {
            foreach ($matchTeamsByDepth as $position => $matchTeam) {
                if ($depth == 0) { // корневые элементы, т.е. победитель и 3 место не сохраняются
                    continue;
                }
                /**
                 * @var $matchTeam MatchTeam
                 */
                $teamId = $matchTeam->getTeamId();
                if (!$teamId) {
                    continue;
                }
                $leftChild = $matchTeam->getLeftChild();
                $rightChild = $matchTeam->getRightChild();
                $result[] = [
                    'ID' => $matchTeam->getId(),
                    'LEFT_CHILD' => $leftChild ? $leftChild->getId() : false,
                    'RIGHT_CHILD' => $rightChild ? $rightChild->getId() : false,
                    'DEPTH' => $depth,
                    'POSITION' => $position,
                    'TEAM_ID' => $teamId,
                    'SCORE' => $matchTeam->getScore(),
                ];
            }
        }
        return $result;
    }

    protected function initEmptyCollection()
    {
        $id = 0;
        for ($depth = $this->depth; $depth >= 0; $depth--) {
            $maxPosition = $this->getMaxPosition($depth);
            for ($position = 0; $position < $maxPosition; $position++) {
                $matchTeam = new MatchTeam($this, $depth, $position);
                $matchTeam->setId('n' . ($id++));
                $this->arMatchTeams[$depth][$position] = $matchTeam;
                if (
                    isset($this->arMatchTeams[$depth + 1][$position * 2]) &&
                    isset($this->arMatchTeams[$depth + 1][$position * 2 + 1])
                ) {
                    $matchTeam->setLeftChild($this->arMatchTeams[$depth + 1][$position * 2]);
                    $matchTeam->setRightChild($this->arMatchTeams[$depth + 1][$position * 2 + 1]);
                }
            }
        }
    }

    protected function getMaxPosition($depth)
    {
        if ($this->thirdPlaceMatch) {
            return pow(2, ($depth > 1 ? $depth : ($depth + 1))); // дополнительные элементы для 3 и 4 места
        } else {
            return pow(2, $depth);
        }
    }

    public function setPlaces()
    {
        $rootMatchTeam = $this->getRootMatchTeam();
        if ($rootMatchTeam) { // определено первое место
            $leftChild = $rootMatchTeam->getLeftChild();
            $rightChild = $rootMatchTeam->getRightChild();
            if ($leftChild && $rightChild) {
                $this->setPlacesForTwoTeams($leftChild, $rightChild, 1, 2);
            }
        }
        $thirdPlaceMatchTeam = $this->get3PlaceMatchTeam();
        if ($this->thirdPlaceMatch && $thirdPlaceMatchTeam) { // определено третье место
            $leftChild = $thirdPlaceMatchTeam->getLeftChild();
            $rightChild = $thirdPlaceMatchTeam->getRightChild();
            if ($leftChild && $rightChild) {
                $this->setPlacesForTwoTeams($leftChild, $rightChild, 3, null);
            }
        }
    }

    protected function setPlacesForTwoTeams(MatchTeam $team1, MatchTeam $team2, $place1, $place2)
    {
        $team1Score = $team1->getScore();
        $team2Score = $team2->getScore();
        if (!strlen($team1Score) || !strlen($team2Score)) {
            return;
        }
        $team1->setPlace($team1Score > $team2Score ? $place1 : $place2);
        $team2->setPlace($team2Score > $team1Score ? $place1 : $place2);
    }

    public function getRootMatchTeam()
    {
        return $this->arMatchTeams[0][0];
    }

    public function get3PlaceMatchTeam()
    {
        return $this->arMatchTeams[0][1];
    }
}