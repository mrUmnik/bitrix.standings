BX.Vue.component('zs-match', {
    props: ['itemId', 'root', 'reverse'],
    computed: {
        ...BX.Vuex.mapGetters(['getItemById', 'getRootId', 'getExtraMatchId']),
        matchTeam() {
            return this.getItemById(this.itemId);
        },
        checkboxVisible() {
            if (!this.matchTeam.LEFT_CHILD || !this.matchTeam.RIGHT_CHILD) {
                return false;
            }
            let leftChild = this.getItemById(this.matchTeam.LEFT_CHILD);
            let rightChild = this.getItemById(this.matchTeam.RIGHT_CHILD);
            if (!leftChild || !leftChild.TEAM_ID) {
                return false;
            }
            if (!rightChild || !rightChild.TEAM_ID) {
                return false;
            }
            return true;
        },
        checkboxDisabled() {
            if (this.matchTeam.DEPTH == 1 && this.getExtraMatchId) {
                // првоерка, проведен ли маьч за 3 место
                // @todo учитывать значеие галки "Игра за 3 место между проигравшими в 1/2 финала"
                let isLeft = (this.itemId == this.getItemById(this.getRootId).LEFT_CHILD);
                let extraMatchChildId;
                if (isLeft) {
                    extraMatchChildId = this.getItemById(this.getExtraMatchId).LEFT_CHILD;
                } else {
                    extraMatchChildId = this.getItemById(this.getExtraMatchId).RIGHT_CHILD;
                }
                return !!this.matchTeam.SCORE || !!this.getItemById(extraMatchChildId).SCORE;

            }
            return !!this.matchTeam.SCORE;
        },
    },
    methods: {
        ...BX.Vuex.mapActions(['setMatchFinished']),
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
		<div :class="{'zs-standing': root, 'zs-match__children' : !root}">
            <zs-match-team :item-id="matchTeam.LEFT_CHILD" :type="root ? 'left' : 'top'" :reverse="root ? false : reverse"></zs-match-team>
            <div :class="{'zs-standing__center': root, 'zs-match__complete' : !root}">
                <div v-if="root" class="zs-match__connector"></div>
                <label v-show="checkboxVisible" :class="{'zs-standing__complete': root}">
                    <template v-if="!root">Игра проведена</template>
                    <input :checked="matchTeam.FINISHED" :disabled="checkboxDisabled"  type="checkbox" @change="setFinished" />
                </label>
            </div>
            <zs-match-team :item-id="matchTeam.RIGHT_CHILD" :type="root ? 'right' : 'bottom'" :reverse="root ? true : reverse"></zs-match-team>
        </div>
	`
});
