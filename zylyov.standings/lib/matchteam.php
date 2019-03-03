<?php

namespace Zylyov\Standings;

use Bitrix\Main\Result;
use Zylyov\Standings\Internals\MatchTeamTable;

class MatchTeam
{
    protected $collection;
    protected $data = [
        'ID' => null,
        'LEFT_CHILD_ID' => null,
        'RIGHT_CHILD_ID' => null,
        'DEPTH' => null,
        'POSITION' => null,
        'TEAM_ID' => null,
        'SCORE' => null,
        'PLACE' => null
    ];


    public function __construct(MatchTeamCollection $collection, int $depth, int $position)
    {
        $this->collection = $collection;

        $this->data['DEPTH'] = $depth;
        $this->data['POSITION'] = $position;
    }

    public function setLeftChild(MatchTeam $child)
    {
        $this->data['LEFT_CHILD_ID'] = $child;
    }

    public function setRightChild(MatchTeam $child)
    {
        $this->data['RIGHT_CHILD_ID'] = $child;
    }

    public function setId($id)
    {
        $this->data['ID'] = $id;
    }

    public function setTeamId($teamId)
    {
        $this->data['TEAM_ID'] = $teamId;
    }

    public function getTeamName()
    {
        if (!$this->data['TEAM_ID']) {
            return '';
        }
        return $this->collection->getTeamName($this->data['TEAM_ID']);
    }

    public function setScore($score)
    {
        $this->data['SCORE'] = $score;
    }

    public function setPlace($place)
    {
        $this->data['PLACE'] = $place;
    }

    public function getId()
    {
        return $this->data['ID'];
    }

    public function getLeftChild()
    {
        return $this->data['LEFT_CHILD_ID'];
    }

    public function getRightChild()
    {
        return $this->data['RIGHT_CHILD_ID'];
    }

    public function getTeamId()
    {
        return $this->data['TEAM_ID'];
    }

    public function getScore()
    {
        return $this->data['SCORE'];
    }

    public function save()
    {
        // @todo Обновлять существующий элемент если в нем дествительно чтото-изменилось
        $result = new Result();
        $arFields = [
            'DEPTH' => $this->data['DEPTH'],
            'POSITION' => $this->data['POSITION'],
            'TEAM_ID' => $this->data['TEAM_ID'],
            'SCORE' => $this->data['SCORE'],
            //   'LEFT_CHILD_ID' => $this->data['LEFT_CHILD_ID'] ? $this->data['LEFT_CHILD_ID']->getId() : null,
            //   'RIGHT_CHILD_ID' => $this->data['RIGHT_CHILD_ID'] ? $this->data['RIGHT_CHILD_ID']->getId() : null,
            // @todo удалить эти поля из базы
            'STANDING_ID' => $this->collection->getStandingId(),
            'PLACE' => $this->data['PLACE'],
        ];
        $new = !strlen($this->data['ID']) || (substr($this->data['ID'], 0, 1) == 'n');
        if ($new) {
            if ($arFields['TEAM_ID']) { // запись добавляется только если указана команда
                $result = MatchTeamTable::add($arFields);
                if ($result->isSuccess()) {
                    $this->setId($result->getId());
                }
            }

        } else {
            if ($arFields['TEAM_ID']) {
                $result = MatchTeamTable::update($this->data['ID'], $arFields);
            } else { // если команда не указана, запись удаляется
                $result = MatchTeamTable::delete($this->data['ID']);
                if ($result->isSuccess()) {
                    $this->setId(null);
                }
            }
        }
        return $result;
    }
}