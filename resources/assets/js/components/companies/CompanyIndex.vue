
<template>
    <div class ="company_index">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center  mb-2">
            <span class="special_title">{{ trans('custom.companies') }}</span>
        </div>

        <div class="row mb-4">
            <div class="col-sm-5">
                <div class="input-group search_input">
                    <input type="text" class="form-control" placeholder="Search" v-model="search">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="search_icon"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-5"></div>
            <div class="col-sm-2  text-center">
                        <router-link :to="{name: 'companyCreate'}" class="btn w-125 btn-primary">{{ trans('custom.create_new') }}</router-link>
            </div>

        </div>

        <div v-if="!companies" class="text-center">{{ trans('custom.no_companies_found') }}</div>

        <div v-if="companies">
            <div class="cards_list">
                    <table class="table table-striped table-responsive-md">

                        <tbody>
                        <tr v-for="company, index in filteredCompanies">
                            <div class="card company_card mb-3">
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-12 text-md-left text-center card-img-wrapper">
                                                <img :src="company.logo" class="img-fluid img-thumbnail" />
                                            </div>
                                            <div class="col-sm-12 col-md-6 text-md-left text-center pl-4">
                                                <router-link class="company_title" :to="{name: 'companyProfile', params: {id: company.id}}">{{company.title}}</router-link>
                                                <div class="row mt-3 ml-0">
                                                    <template v-if="company.company_address != null">
                                                        <div class="address_icon cont-icon"></div><span class="text-dark"><b>{{ company.company_address }}</b></span>
                                                    </template>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-12 mt-3">
                                                        <button v-for="specialization, index in company.specializations" type="button" class="btn mr-1 mb-1 spec-item btn-outline-dark disabled">{{specialization.title}}</button>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="col-sm-12 col-md-3 text-md-right text-center action_buttons">
                                                    <div class="company_actions_holder">

                                                        <router-link :to="{name: 'companyUsers', params: {id: company.id}}" v-if="allowedDisplay('all') || allowedDisplay('company_edit', 'company', company.id)" class="btn btn-xs btn-success">
                                                            <i class="plus_icon"></i> {{ trans('custom.add_user') }}
                                                        </router-link>

<!--                                                        <div class="dropdown" v-if="allowedDisplay('all') || allowedDisplay('company_edit', 'company', company.id) || !company.is_member>-->
                                                        <div class="dropdown" v-if="company.can_delete">
                                                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"
                                                                    aria-haspopup="true" aria-expanded="false">
                                                                {{ trans('custom.actions') }}
                                                            </button>
                                                            <div class="dropdown-menu">

<!--                                                                <a v-on:click.prevent="deleteVisibility(company.id, index)"-->
<!--                                                                   v-if="!company.is_member && !allowedDisplay('all') "><i class="leave_icon"></i>{{ trans('custom.leave') }}</a>-->
<!--                                                                <router-link :to="{name: 'companyEdit', params: {id: company.id}}" v-if="allowedDisplay('all') || allowedDisplay('company_edit', 'company', company.id)"-->
<!--                                                                             class=""><i class="edit_icon"></i> {{ trans('custom.edit') }}-->
<!--                                                                </router-link>-->

                                                                <a v-if="company.can_delete"
                                                                   v-on:click.prevent="deleteEntry(company.id)" class="" href="#"><i class="delete_icon"></i>
                                                                    {{ trans('custom.delete') }}
                                                                </a>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </tr>
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                companies: [],
                roles: [],
                allowedDisplay: function(action, model = false, id = false) {
                    return window.allowedDisplay(action, model , id)
                },
                search: ''
            }
        },
        mounted() {
            var app = this;
            axios.get('/api/v1/companies')
                .then(function (resp) {
                    app.companies = resp.data;
                })
                .catch(function (resp) {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.trans('custom.error_load_company'));
                    }
                });
        },
        methods: {
            deleteEntry(id, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/companies/' + id)
                            .then(function (resp) {
                                app.companies.splice(index, 1);
                            })
                            .catch(function (resp) {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert(window.trans('custom.error_delete_company'));
                                }
                            });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            deleteVisibility(company_id, index){
                var app = this;
                app.$dialog.confirm(window.trans('custom.are_you_sure_you_want_to_permanently_leave_this_company'))
                    .then(function () {
                        axios.delete('/api/v1/companies/' + company_id + '/visibility_leave').then((response) => {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>'
                                + response.data.description, {
                                'html': true,
                                okText: 'ok'
                            }).then(() => {
                                app.companies.data.splice(index, 1);
                            });

                        }).catch((resp) => {
                            if (resp.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert('Error occured. Try Again later.');
                            }
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
        },

        computed: {
            filteredCompanies() {
                return this.companies.filter(company => {
                    return company.title.toLowerCase().indexOf(this.search.toLowerCase()) > -1
                })
            }
        }
    }
</script>
