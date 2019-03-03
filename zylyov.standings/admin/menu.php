<?php

namespace Zylyov\Standings;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class Menu
{
    public static function OnBuildGlobalMenu(&$arGlobalMenu, &$arModuleMenu)
    {
        $arModuleMenu[] = array(
            "parent_menu" => "global_menu_content",
            "section" => "zylyov_standings",
            "sort" => 999,
            "text" => "Турнирные таблицы",
            "title" => "Турнирные таблицы",
            "items_id" => "menu_zylyov_standings",
            "items" => array(
                array(
                    "text" => "Турнирные таблицы",
                    "url" => "zylyov_standing_list.php?lang=" . LANGUAGE_ID,
                    "items_id" => "menu_zylyov_standings_list",
                    "module_id" => "zylyov.standings",
                    "more_url" => Array("zylyov_standing_edit.php", "zylyov_standing_report.php"),
                    "items" => array()
                ),
                array(
                    "text" => "Команды",
                    "url" => "perfmon_table.php?table_name=zylyov_standings_team&lang=" . LANGUAGE_ID,
                    "items_id" => "menu_zylyov_standings_list",
                    "module_id" => "zylyov.standings",
                    "items" => array()
                )
            )
        );
        // @todo CRUD для справочника команд
    }
}