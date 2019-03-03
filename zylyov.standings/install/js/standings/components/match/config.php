<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

return [
    'js' => [
        '/bitrix/js/zylyov/standings/components/match/zylyov.standings.components.match.js',
    ],
    'css' => [
        '/bitrix/js/zylyov/standings/components/match/zylyov.standings.components.match.css',
    ],
    'rel' => [
        'zylyov.standings.components.match_team'
    ],
    'skip_core' => true,
];