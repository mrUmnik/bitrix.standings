<?php

namespace Zylyov\Standings\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Data\DataManager;

Loc::loadMessages(__FILE__);

class MatchTable extends DataManager
{
    public static function getTableName()
    {
        return 'zylyov_standings_match';
    }

    /**
     * Returns entity map definition.
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => new Fields\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID'
            )),
            'STAGING_ID' => new Fields\IntegerField('STAGING_ID', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_STAGING_ID"),
                'required' => true
            )),
            'LEFT_TEAM_ID' => new Fields\IntegerField('LEFT_TEAM_ID', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_LEFT_TEAM"),
            )),
            'RIGHT_TEAM_ID' => new Fields\IntegerField('RIGHT_TEAM_ID', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_RIGHT_TEAM"),
            )),
            'LEFT_SCORE' => new Fields\IntegerField('LEFT_SCORE', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_LEFT_SCORE"),
            )),
            'RIGHT_SCORE' => new Fields\IntegerField('RIGHT_SCORE', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_RIGHT_SCORE"),
            )),
            'POSITION' => new Fields\StringField('POSITION', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_POSITION"),
            )),
            'DEPTH' => new Fields\IntegerField('DEPTH', array(
                'title' => Loc::getMessage("ZS.MATCH_FIELD_DEPTH"),
            )),
        );
    }
}