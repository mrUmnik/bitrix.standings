BX.Vue.component('zs-match-team', {
    props: ['itemId', 'type', 'reverse'],
    computed: {
        ...BX.Vuex.mapGetters(['getItemById', 'getTeamById', 'getTeamsList']),
        teamsList() {
            return this.getTeamsList(this.matchTeam.TEAM_ID);
        },
        matchTeam() {
            return this.getItemById(this.itemId);
        },
        matchTeamName() {
            return this.getTeamById(this.matchTeam.TEAM_ID);
        },
        inputName() {
            return 'MATCH_TEAM[' + this.matchTeam.DEPTH + '][' + this.matchTeam.POSITION + ']';
        }
    },
    methods: {
        ...BX.Vuex.mapActions(['setMatchTeamId', 'setMatchFinished']),
        setTeam(teamId) {
            this.setMatchTeamId({
                itemId: this.itemId,
                teamId: teamId
            });
        },
        setFinished(event) {
            this.setMatchFinished({
                vm: this,
                itemId: this.itemId,
                finished: event.target.checked
            });
            event.target.checked = false;
        }
    },
    template: `
		<div class="zs-match" :class="{'zs-match_reversed': reverse}">
		    <template v-if="matchTeam.LEFT_CHILD && matchTeam.RIGHT_CHILD">
		        <zs-match :item-id="itemId" :reverse="reverse"></zs-match>
            </template>
			<div v-if="!reverse && matchTeam.LEFT_CHILD && matchTeam.RIGHT_CHILD" class="zs-match__connector zs-match__connector_tail-left"></div>
			<div class="zs-match__team">
			    <div v-if="reverse" class="zs-score zs-score_reversed" :class="{'zs-score_active': matchTeam.SCORE.length}">
			        {{matchTeam.SCORE}}
                </div>
			    <template v-if="!matchTeam.READONLY">
			        <select class="zs-field" @change="setTeam($event.target.value)" :disabled="!!(matchTeam.SCORE.length)">
			            <option value=""></option>
			            <option v-for="(teamName, teamId) in teamsList" :value="teamId" :selected="teamId==matchTeam.TEAM_ID">{{teamName}}</option>
                    </select>
                </template>
                <template v-else-if="matchTeam.TEAM_ID">
                    <span class="zs-field">{{matchTeamName}}</span>
                </template>
                <template v-else>
                  <span class="zs-field"></span>
                </template>
                <div v-if="!reverse" class="zs-score" :class="{'zs-score_active': matchTeam.SCORE.length}">
			        {{matchTeam.SCORE}}
                </div>
			</div>
			<div v-if="reverse && matchTeam.LEFT_CHILD && matchTeam.RIGHT_CHILD" class="zs-match__connector zs-match__connector_tail-right"></div>
			<div class="zs-match__connector" :class="['zs-match__connector_'+type]"></div>
			
			<input type="hidden" :name="inputName+'[ID]'" :value="matchTeam.ID">
			<input type="hidden" :name="inputName+'[TEAM_ID]'" :value="matchTeam.TEAM_ID">
			<input type="hidden" :name="inputName+'[SCORE]'" :value="matchTeam.SCORE">
		</div>
	`
});
