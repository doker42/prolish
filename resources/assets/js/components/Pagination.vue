<template>
    <ul class="pagination" v-if="pagination.last_page > 1">
        <li v-if="pagination.current_page > 1" class="page-item">
            <a class="page-link" href="javascript:void(0)" aria-label="Previous" v-on:click.prevent="changePage(pagination.current_page - 1)">
                <span aria-hidden="true">«</span>
            </a>
        </li>
        <li v-for="page in pagesNumber" :class="{'active': page == pagination.current_page}" class="page-item">
            <a class="page-link" href="javascript:void(0)" v-on:click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li v-if="pagination.current_page < pagination.last_page" class="page-item">
            <a class="page-link" href="javascript:void(0)" aria-label="Next" v-on:click.prevent="changePage(pagination.current_page + 1)">
                <span aria-hidden="true">»</span>
            </a>
        </li>
    </ul>
</template>
<script>
    export default{
        props: {
            pagination: {
                type: Object,
                required: true
            },
            offset: {
                type: Number,
                default: 4
            },
            scrolltop: false
        },
        computed: {
            pagesNumber() {
                if (!this.pagination.to) {
                    return [];
                }
                let from = this.pagination.current_page - this.offset;
                if (from < 1) {
                    from = 1;
                }
                let to = from + (this.offset * 2);
                if (to >= this.pagination.last_page) {
                    to = this.pagination.last_page;
                }
                let pagesArray = [];
                for (let page = from; page <= to; page++) {
                    pagesArray.push(page);
                }
                return pagesArray;
            }
        },
        methods : {
            changePage(page) {
                this.pagination.current_page = page;
                this.$emit('paginate');

                if (this.scrolltop) {
                    window.scroll({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    })
                }
            }
        }
    }
</script>

<style>
    .pagination {
        overflow: hidden;
    }
</style>
