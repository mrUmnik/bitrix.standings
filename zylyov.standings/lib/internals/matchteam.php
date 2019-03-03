<?php

namespace Zylyov\Standings\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class MatchTeamTable extends DataManager
{
    public static function getTableName()
    {
        return 'zylyov_standings_match_team';
    }

    public static function getMap()
    {
        return array(
            'ID' => new Fields\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID'
            )),
            'STANDING_ID' => new Fields\IntegerField('STANDING_ID', array(
                'required' => true
            )),
            'TEAM_ID' => new Fields\IntegerField('TEAM_ID'),
            'SCORE' => new Fields\IntegerField('SCORE'),
            'DEPTH' => new Fields\IntegerField('DEPTH'),
            'POSITION' => new Fields\IntegerField('POSITION'),
            'PLACE' => new Fields\IntegerField('PLACE'),
            'TEAM' => new Fields\Relations\Reference(
                'TEAM',
                TeamTable::class,
                Join::on('this.TEAM_ID', 'ref.ID')
            )
        );
    }
}