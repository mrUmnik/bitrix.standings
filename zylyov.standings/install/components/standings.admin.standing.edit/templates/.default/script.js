// @todo Вынести тексты в языковые файлы
;(function () {
    "use strict";
    BX.namespace('BX.Zylyov.Standings');

    BX.Zylyov.Standings.standing = function (
        containerId,
        arParams
    ) {
        this.vm = null;
        this.scorePopup = null;
        this.errorPopup = null;
        this.confirmPopup = null;
        this.init(
            containerId,
            arParams
        );
    };

    BX.Zylyov.Standings.standing.prototype = {
        init: function (containerId, arParams) {
            [arParams.ROOT_ID, arParams.EXTRA_MATCH_ID, arParams.MATCH_TEAMS_LIST] = this.prepareMatchTeams(arParams.DEPTH, arParams.MATCHES);

            this.vm = BX.Vue.create({
                el: '#' + containerId,
                store: this.getStore(arParams),
                data: {
                    showExtraMatch: arParams.THIRD_PLACE_GAME,
                    depth: arParams.DEPTH
                },
                computed: {
                    ...BX.Vuex.mapGetters({
                        extraMatchId: 'getExtraMatchId',
                        rootId: 'getRootId'
                    }),
                    ...BX.Vuex.mapGetters(['hasSelectedTeams']),
                    labels() {
                        let result = [];
                        for (let i = this.depth - 1; i >= 0; i--) {
                            result.push({
                                class: i > 0 ? '' : 'zs-labels__label_wide',
                                title: i > 0 ? '1/' + Math.pow(2, i) + ' финала' : ''
                            });
                        }
                        for (let i = 1; i < this.depth; i++) {
                            result.push({
                                class: '',
                                title: '1/' + Math.pow(2, i) + ' финала'
                            });
                        }
                        return result;
                    }
                },
                template: `
                    <div class="zs-standing-outer" :class="['zs-standing-outer_depth-'+depth]">
                        <div class="zs-labels">
                            <div v-for="label in labels" class="zs-labels__label" :class="label.class">{{label.title}}</div>		    
                        </div>
                        <div class="zs-labels zs-labels_final">
                            <div class="zs-labels__label">Финал</div>
                        </div>
                       <zs-match :item-id="rootId" :root="true"></zs-match>
                       <div class="zs-match-3rd-place" v-if="showExtraMatch && extraMatchId">
                            <div class="zs-labels">
                                <div class="zs-labels__label">Игра за 3 место</div>
                            </div>
                            <zs-match :item-id="extraMatchId" :root="true"></zs-match>
                       </div>
                    </div>`,
                standing: this,
                methods: {
                    ...BX.Vuex.mapActions(['setMatchItems', 'setRootId', 'setExtraMatchId']),
                    setShowMatch(show) {
                        this.showExtraMatch = this.depth > 1 ? !!show : false;
                    },
                    setDepth(depth) {
                        let rootId;
                        let extraMatchId;
                        let matchTeams;
                        [rootId, extraMatchId, matchTeams] = this.$options.standing.prepareMatchTeams(depth, []);
                        this.depth = depth;
                        this.setRootId(rootId);
                        this.setMatchItems(matchTeams);
                        this.setExtraMatchId(extraMatchId);
                        if (depth <= 1) {
                            this.showExtraMatch = false;
                        }
                    },
                }
            });
            this.bindEvents();
        },
        prepareMatchTeams: function (maxDepth, existedMatches) {
            let existedMatchesHierarchy = {};
            for (let i = 0; i < existedMatches.length; i++) {
                let item = existedMatches[i];
                if (typeof existedMatchesHierarchy[item.DEPTH] == 'undefined') {
                    existedMatchesHierarchy[item.DEPTH] = {};
                }
                existedMatchesHierarchy[item.DEPTH][item.POSITION] = item;
            }
            let newMatchTeamIndex = existedMatches.length;
            let matchTeams = {};
            let rootId;
            let extraMatchId;
            let itemsByDepthAndPosition = {};
            for (let depth = maxDepth; depth >= 0; depth--) {
                itemsByDepthAndPosition[depth] = {};
                let maxPosition = Math.pow(2, (depth > 1 ? depth : (depth + 1))); // дополнительные элементы для 3 и 4 места
                for (let position = 0; position < maxPosition; position++) {
                    let id;
                    if (existedMatchesHierarchy[depth] && existedMatchesHierarchy[depth][position]) {
                        let existedItem = existedMatchesHierarchy[depth][position];
                        id = existedItem.ID;
                        matchTeams[id] = {
                            ID: id,
                            LEFT_CHILD: existedItem.LEFT_CHILD,
                            RIGHT_CHILD: existedItem.RIGHT_CHILD,
                            TEAM_ID: existedItem.TEAM_ID,
                            SCORE: existedItem.SCORE,
                            DEPTH: depth,
                            POSITION: position,
                            FINISHED: false,
                            READONLY: false,
                        };
                    } else {
                        id = 'm' + (newMatchTeamIndex++);
                        matchTeams[id] = {
                            ID: id,
                            LEFT_CHILD: false,
                            RIGHT_CHILD: false,
                            TEAM_ID: '',
                            SCORE: '',
                            DEPTH: depth,
                            POSITION: position,
                            FINISHED: false,
                            READONLY: false,
                        };
                    }

                    itemsByDepthAndPosition[depth][position] = id;
                    if (
                        itemsByDepthAndPosition.hasOwnProperty(depth + 1)
                        && itemsByDepthAndPosition[depth + 1].hasOwnProperty(position * 2)
                        && itemsByDepthAndPosition[depth + 1].hasOwnProperty(position * 2 + 1)
                    ) {
                        let leftChildId = itemsByDepthAndPosition[depth + 1][position * 2];
                        let rightChildId = itemsByDepthAndPosition[depth + 1][position * 2 + 1];
                        matchTeams[id].LEFT_CHILD = leftChildId;
                        matchTeams[id].RIGHT_CHILD = rightChildId;
                        matchTeams[id].READONLY = true;
                        if ( // если игра проведена
                            matchTeams[leftChildId] && matchTeams[leftChildId].SCORE.length &&
                            matchTeams[rightChildId] && matchTeams[rightChildId].SCORE.length
                        ) {
                            matchTeams[id].FINISHED = true;
                        }
                    }
                    if (!position && !depth) {
                        rootId = id;
                    }
                    if (position == 1 && !depth) { // матч за 3-4 место
                        extraMatchId = id;
                        if (matchTeams[extraMatchId].LEFT_CHILD) {
                            matchTeams[matchTeams[extraMatchId].LEFT_CHILD].READONLY = true;
                        }
                        if (matchTeams[extraMatchId].RIGHT_CHILD) {
                            matchTeams[matchTeams[extraMatchId].RIGHT_CHILD].READONLY = true;
                        }
                    }
                }
            }
            return [rootId, extraMatchId, matchTeams];
        },
        getStore: function (arParams) {
            return BX.Vuex.store({
                state: {
                    items: arParams.MATCH_TEAMS_LIST,
                    teams: arParams.TEAMS_LIST,
                    rootId: arParams.ROOT_ID,
                    extraMatchId: arParams.EXTRA_MATCH_ID
                },
                getters: {
                    getItemById: state => id => {
                        if (!id || !state.items.hasOwnProperty(id)) {
                            return false;
                        }
                        return state.items[id];
                    },
                    getTeamById: state => id => {
                        if (!id || !state.teams.hasOwnProperty(id)) {
                            return false;
                        }
                        return state.teams[id];
                    },
                    /**
                     * @returns список команд еще не являющихся участниками
                     */
                    getTeamsList: state => selectedTeamId => {
                        let result = {};
                        let usedTeams = [];
                        for (let itemId in state.items) {
                            let item = state.items[itemId];
                            if (item.TEAM_ID) {
                                usedTeams.push(item.TEAM_ID);
                            }
                        }
                        for (let teamId in state.teams) {
                            if (selectedTeamId && selectedTeamId == teamId) {
                                result[teamId] = state.teams[teamId];
                            } else if (usedTeams.indexOf(teamId) == -1) {
                                result[teamId] = state.teams[teamId];
                            }
                        }
                        return result;
                    },
                    getRootId(state) {
                        return state.rootId;
                    },
                    getExtraMatchId(state) {
                        return state.extraMatchId;
                    },
                    hasSelectedTeams(state) {
                        for (let itemId in state.items) {
                            let item = state.items[itemId];
                            if (item.TEAM_ID) {
                                return true;
                            }
                        }
                        return false;
                    }
                },
                actions: {
                    setMatchTeamId({commit, state}, {itemId, teamId}) {
                        state.items[itemId].TEAM_ID = teamId;
                    },
                    setMatchFinished({commit, state}, {vm, itemId, finished}) {
                        let leftChildId = state.items[itemId].LEFT_CHILD;
                        let rightChildId = state.items[itemId].RIGHT_CHILD;
                        if (!finished) {
                            state.items[leftChildId].SCORE = '';
                            state.items[rightChildId].SCORE = '';
                            state.items[itemId].FINISHED = false;
                            state.items[itemId].TEAM_ID = '';
                            if (state.items[itemId].DEPTH == 1) {
                                // @todo учитывать значеие галки "Игра за 3 место между проигравшими в 1/2 финала"
                                let isLeft = (itemId == state.items[state.rootId].LEFT_CHILD);
                                let extraMatchChildId;
                                if (isLeft) {
                                    extraMatchChildId = state.items[state.extraMatchId].LEFT_CHILD;
                                } else {
                                    extraMatchChildId = state.items[state.extraMatchId].RIGHT_CHILD;
                                }
                                state.items[extraMatchChildId].TEAM_ID = '';

                            }
                            return;
                        }

                        let leftTeamId = state.items[leftChildId].TEAM_ID;
                        let rightTeamId = state.items[rightChildId].TEAM_ID;

                        vm.$root.$options.standing // @todo такой способ достучаться до standing выглядит как то костыльно
                            .setScore(state.teams[leftTeamId], state.teams[rightTeamId])
                            .then(
                                ({leftScore, rightScore}) => {
                                    state.items[leftChildId].SCORE = leftScore;
                                    state.items[rightChildId].SCORE = rightScore;
                                    state.items[itemId].FINISHED = true;
                                    state.items[itemId].TEAM_ID = leftScore > rightScore ? leftTeamId : rightTeamId;
                                    if (state.items[itemId].DEPTH == 1) {
                                        // @todo учитывать значеие галки "Игра за 3 место между проигравшими в 1/2 финала"
                                        let isLeft = (itemId == state.items[state.rootId].LEFT_CHILD);
                                        let extraMatchChildId;
                                        if (isLeft) {
                                            extraMatchChildId = state.items[state.extraMatchId].LEFT_CHILD;
                                        } else {
                                            extraMatchChildId = state.items[state.extraMatchId].RIGHT_CHILD;
                                        }
                                        state.items[extraMatchChildId].TEAM_ID = leftScore > rightScore ? rightTeamId : leftTeamId;
                                    }
                                },
                                () => {
                                    state.items[itemId].FINISHED = false;
                                }
                            );
                    },
                    setMatchItems({commit, state}, items) {
                        state.items = items;
                    },
                    setRootId({commit, state}, rootId) {
                        state.rootId = rootId;
                    },
                    setExtraMatchId({commit, state}, extraMatchId) {
                        state.extraMatchId = extraMatchId;
                    }
                },
                mutations: {}
            });
        },
        bindEvents: function () {
            BX.bind(BX('standing_third_place_game'), 'change', BX.proxy(function (event) {
                this.vm.setShowMatch(event.target.checked);
            }, this));
            BX.bind(BX('standing_depth'), 'change', BX.proxy(function (event) {
                if (!this.vm.hasSelectedTeams) {
                    this.vm.setDepth(event.target.value);
                    return;
                }
                this.showConfirmMessage('Все текущие настройки матчей будут утеряны. Вы действительно хотите продолжить?')
                    .then(
                        () => {
                            this.vm.setDepth(event.target.value);
                        },
                        () => {
                            event.target.value = this.vm.depth;
                        });
            }, this));
        },
        setScore: function (leftTeamName, rightTeamName) {
            let _this = this;
            let promise = new BX.Promise();
            if (!this.scorePopup) {
                this.scorePopup = new BX.PopupWindow(
                    "standing_score_window",
                    null,
                    {
                        overlay: {opacity: 50},
                        content: '',
                        autoHide: false,
                        titleBar: 'Результат игры',
                        closeByEsc: false
                    }
                );
            }
            this.scorePopup.setContent(this.getScorePopupContent(leftTeamName, rightTeamName));
            this.scorePopup.setButtons([
                new BX.PopupWindowButtonLink({
                    text: "Отмена",
                    events: {
                        click: function () {
                            _this.scorePopup.close();
                            promise.reject();
                        }

                    }
                }),
                new BX.PopupWindowButton({
                    text: "Сохранить",
                    className: 'popup-window-button-accept',
                    events: {
                        click: function () {

                            let leftScore = BX('zs-score-left-team').value;
                            let rightScore = BX('zs-score-right-team').value;

                            let leftScoreNum = parseInt(leftScore);
                            let rightScoreNum = parseInt(rightScore);

                            if (
                                isNaN(leftScoreNum) ||
                                isNaN(rightScoreNum) ||
                                leftScoreNum != leftScore ||
                                leftScoreNum != leftScore
                            ) {
                                _this.showErrorMessage('Некорректное значение счета');
                            } else if (leftScoreNum == rightScoreNum) {
                                _this.showErrorMessage('В игре на выбывание счет не может быть равным');
                            } else {
                                _this.scorePopup.close();
                                promise.fulfill({
                                    leftScore: leftScoreNum + '',
                                    rightScore: rightScoreNum + ''
                                });
                            }
                        }
                    }
                })
            ]);
            this.scorePopup.show();
            return promise;
        },
        getScorePopupContent: function (leftTeamName, rightTeamName) {
            return `
                <table class="zs-score-table">
                    <tr>
                       <td class="zs-score-table__team zs-score-table__team_left">${leftTeamName}</td>
                       <td class="zs-score-table__team zs-score-table__team_space"></td>
                       <td class="zs-score-table__team zs-score-table__team_right">${rightTeamName}</td>
                    </tr>
                    <tr>
                        <td class="zs-score-table__value zs-score-table__value_left"><input class="zs-field" id="zs-score-left-team" value="0" /></td>
                        <td class="zs-score-table__value">:</td>
                        <td class="zs-score-table__value zs-score-table__value_right"><input class="zs-field" id="zs-score-right-team" value="0" /></td>
                    </tr>
                </table>
            `;
        },
        showErrorMessage: function (errorMessage) {
            let _this = this;
            if (!this.errorPopup) {
                this.errorPopup = new BX.PopupWindow(
                    "standing_score_error_window",
                    null,
                    {
                        autoHide: false,
                        closeByEsc: true,
                        overlay: {opacity: 50},
                        zIndex: 1000,
                        buttons: [
                            new BX.PopupWindowButton({
                                text: "Закрыть",
                                className: 'popup-window-button-accept',
                                events: {
                                    click: function () {
                                        _this.errorPopup.close();
                                    }
                                }
                            })
                        ]
                    }
                );
            }
            this.errorPopup.setContent(errorMessage);
            this.errorPopup.show();
        },
        showConfirmMessage: function (confirmMessage) {
            let _this = this;
            let promise = new BX.Promise();
            if (!this.confirmPopup) {
                this.confirmPopup = new BX.PopupWindow(
                    "standing_score_confirm_window",
                    null,
                    {
                        autoHide: false,
                        closeByEsc: true,
                        overlay: {opacity: 50},
                        zIndex: 1000
                    }
                );
            }
            this.confirmPopup.setButtons([
                new BX.PopupWindowButtonLink({
                    text: "Отмена",
                    events: {
                        click: function () {
                            _this.confirmPopup.close();
                            promise.reject();
                        }
                    }
                }),
                new BX.PopupWindowButton({
                    text: "Продолжить",
                    className: 'popup-window-button-accept',
                    events: {
                        click: function () {
                            _this.confirmPopup.close();
                            promise.fulfill();
                        }
                    }
                })
            ]);
            this.confirmPopup.setContent(confirmMessage);
            this.confirmPopup.show();
            return promise;
        }
    };
})();