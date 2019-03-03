<?php

namespace Zylyov\Standings\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Data\DataManager;

Loc::loadMessages(__FILE__);

class StandingTable extends DataManager
{
    public static function getTableName()
    {
        return 'zylyov_standings_standing';
    }

    public static function getMap()
    {
        return array(
            'ID' => new Fields\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID'
            )),
            'NAME' => new Fields\StringField('NAME', array(
                'title' => Loc::getMessage("ZS.FIELD_NAME"),
                'required' => true
            )),
            'DEPTH' => new Fields\IntegerField('DEPTH', array(
                'title' => Loc::getMessage("ZS.FIELD_DEPTH"),
                'required' => true
            )),
            'THIRD_PLACE_GAME' => new Fields\StringField('THIRD_PLACE_GAME', array(
                'title' => Loc::getMessage("ZS.FIELD_THIRD_PLACE_GAME"),
                'default_value' => 'Y'
            )),
        );
    }
}