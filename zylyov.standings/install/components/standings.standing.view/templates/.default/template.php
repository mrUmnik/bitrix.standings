<?php

use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$labels = [];
for ($i = $arResult['DATA']['DEPTH'] - 1; $i >= 0; $i--) {
    $labels[] = [
        'class' => $i > 0 ? '' : 'zs-labels__label_wide',
        'title' => $i > 0 ? '1/' . pow(2, $i) . ' финала' : ''
    ];
}
for ($i = 1; $i < $arResult['DATA']['DEPTH']; $i++) {
    $labels[] = [
        'class' => '',
        'title' => '1/' . pow(2, $i) . ' финала'
    ];
}
if (!function_exists("renderStandingMatch")) {
    function renderStandingMatch(\Zylyov\Standings\MatchTeam $matchTeam, $isRoot, $isReverse)
    {
        ?>
        <div class="<?= ($isRoot ? 'zs-standing' : 'zs-match__children') ?>">
            <?
            renderStandingMatchTeam($matchTeam->getLeftChild(), $isRoot ? 'left' : 'top', $isRoot ? false : $isReverse) ?>
            <?
            if ($isRoot): ?>
                <div class="zs-standing__center">
                    <div class="zs-match__connector"></div>
                </div>
            <?endif ?>
            <?
            renderStandingMatchTeam($matchTeam->getRightChild(), $isRoot ? 'right' : 'bottom', $isRoot ? true : $isReverse) ?>
        </div>
        <?
    }
}
if (!function_exists("renderStandingMatchTeam")) {
    function renderStandingMatchTeam(\Zylyov\Standings\MatchTeam $matchTeam, $type, $isReverse)
    {
        ?>
        <div class="zs-match<?
        if ($isReverse): ?> zs-match_reversed<?endif ?>">
            <?
            $hasChildren = ($matchTeam->getLeftChild() && $matchTeam->getRightChild());
            if ($hasChildren) {
                renderStandingMatch($matchTeam, false, $isReverse);
            }
            ?>
            <?
            if (!$isReverse && $hasChildren): ?>
                <div class="zs-match__connector zs-match__connector_tail-left"></div>
            <?endif ?>
            <div class="zs-match__team">
                <?
                if ($isReverse):
                    $score = $matchTeam->getScore();
                    ?>
                    <div class="zs-score zs-score_reversed<?
                    if (strlen($score)):?> zs-score_active<?endif ?>">
                        <?= $score ?>
                    </div>
                <?endif ?>
                <span class="zs-field"><?= $matchTeam->getTeamName() ?></span>
                <?
                if (!$isReverse):
                    $score = $matchTeam->getScore();
                    ?>
                    <div class="zs-score <?
                    if (strlen($score)):?> zs-score_active<?endif ?>">
                        <?= $score ?>
                    </div>
                <?endif ?>
            </div>
            <?
            if ($isReverse && $hasChildren): ?>
                <div class="zs-match__connector zs-match__connector_tail-right"></div>
            <?endif ?>
            <div class="zs-match__connector zs-match__connector_<?= $type ?>"></div>
        </div>
        <?
    }
}
?>
<div class="zs-standing-outer zs-standing-outer_depth-<?= $arResult['DATA']['DEPTH'] ?>">
    <div class="zs-labels">
        <? foreach ($labels as $arLabel): ?>
            <div class="zs-labels__label <?= $arLabel['class'] ?>"><?= $arLabel['title'] ?></div>
        <? endforeach ?>
    </div>
    <div class="zs-labels zs-labels_final">
        <div class="zs-labels__label">Финал</div>
    </div>
    <?
    renderStandingMatch($arResult['DATA']['MATCH_TEAM_COLLECTION']->getRootMatchTeam(), true, false);
    ?>
    <zs-match :item-id="rootId" :root="true"></zs-match>
    <? if ($arResult['DATA']['THIRD_PLACE_GAME']):
        $thirdPlaceMatchTeam = $arResult['DATA']['MATCH_TEAM_COLLECTION']->get3PlaceMatchTeam();
        if ($thirdPlaceMatchTeam):
            ?>
            <div class="zs-match-3rd-place">
                <div class="zs-labels">
                    <div class="zs-labels__label">Игра за 3 место</div>
                </div>
                <?
                renderStandingMatch($thirdPlaceMatchTeam, true, false);
                ?>
            </div>
        <?
        endif;
    endif ?>
</div>