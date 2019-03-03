<?php

namespace Zylyov\Standings\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Data\DataManager;

Loc::loadMessages(__FILE__);

class TeamTable extends DataManager
{
    public static function getTableName()
    {
        return 'zylyov_standings_team';
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
                'title' => Loc::getMessage("ZS.TEAM_FIELD_NAME"),
                'required' => true
            ))
        );
    }

    //@todo Не давать удалять команды привязанные к турнирным таблицам
}