<template>
    <div>
        <div class="top_desc">
            {{ title }}
        </div>
        <div class="progress my-1">
            <div class="progress-bar" role="progressbar" :style="{width: percentage + '%'}" :aria-valuenow="percentage" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <div class="bottom_desc">
        <template v-if="is_owner">
          <a class="billing_link" href="/#/settings#billing">{{trans('custom.upgrade') }}  </a>
              {{ used }}GB / {{ total }}GB
        </template><template v-else>
            {{ trans('custom.used') }}: {{ used }}GB / {{ total }}GB
        </template>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                used: 0,
                total: 0,
                is_owner: 0,
                percentage: 0,
                title:'',
            }
        },

        mounted() {
            this.update();

            if (window.env != 'local') {
                setInterval(() => {
                    this.update()
                }, 7500);
            }
        },

        methods: {
            update() {
                axios.get('/api/v1/storage')
                    .then((response) => {
                        this.used = response.data.used;
                        this.title = response.data.title;
                        this.total = response.data.total;
                        this.is_owner = response.data.is_owner;

                        this.percentage = this.used/this.total*100;
                    });
            }
        }
    }
</script>

<style scoped>
    .progress {
        height: 3px;
    }
</style>
