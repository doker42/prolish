
<template>
    <div class = "project_user_index">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectsIndex', params: {id: companyId}}" class="btn back_btn">{{ trans('custom.back') }}</router-link>
                </div>
            </div>
            <div class="col-sm-12 col-md-9 customized_title">
                <h1 class="h2 mb-0 d-inline align-middle">{{ trans('custom.add_user') }}</h1>
            </div>

            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group" v-if="allowed_create">
                    <span class="btn btn-primary width-150" data-toggle="modal" data-target="#shareModal">{{ trans('custom.create_new') }}</span>
                </div>
            </div>
        </div>

        <div v-if="!visibility_data" class="text-center">{{ trans('custom.no_users_found') }}</div>

        <div v-if="visibility_data && roles">
            <div class="card my-3">
                <div class="card-body">
                    <form>
                        <table class="table table-striped table-responsive-md">
                            <thead>

                                <th>{{ trans('custom.name') }}</th>
                                <th>{{ trans('custom.email') }}</th>
                                <th>{{ trans('custom.company') }}</th>
                                <th class="width-200">{{ trans('custom.role') }}</th>
                                <th>{{ trans('custom.actions') }}</th>
                            </thead>
                            <tbody>
                                <tr v-for="item, index in visibility_data">
                                    <template v-if="item.user">
                                        <td class="align-middle name_cell">
                                            <img class="avatar" :src="item.user.picture"  />
                                            <div class="name_holder">
                                                <template v-if="item.user.job_title !=null">
                                                    <span class="name">{{ item.user.name }}</span>
                                                    <span v-if="item.user.job_title !=null" class="job_title">{{ item.user.job_title }}</span>
                                                </template>
                                                <template v-else>
                                                    <span class="name alone">{{ item.user.name }}</span>
                                                </template>
                                            </div>

                                        </td>
                                        <td class="align-middle">
                                            {{ item.user.email }}
                                        </td>
                                        <td class="align-middle">
                                            <span v-if="item.user.company">{{ item.user.company.title }}</span>
                                        </td>
                                        <td class="align-middle text-capitalize font-weight-bold">
                                            <v-select :options="roles_arr" v-model="item.role" label="name"  name="name" @input="changeRole($event,  item.user_id, item.company_id)" taggable push-tags>

                                            </v-select>
                                        </td>
                                        <td class="align-middle h4">
                                        <span class="btn_delete_user" v-on:click="deleteEntry(item.user_id, item.company_id, index)">
                                        </span>
                                        </td>
                                    </template>
                                    <template v-else-if="item.company">
                                        <td class="align-middle">
                                            <img class="avatar"  :src="item.company.logo"  />
                                        </td>
                                        <td class="align-middle">
                                            -
                                        </td>
                                        <td class="align-middle">
                                            <span>{{ item.company.title }}</span>
                                        </td>
                                        <td class="align-middle text-capitalize font-weight-bold">
                                            <v-select :options="roles_arr" v-model="item.role" label="name"  name="name" @input="changeRole($event,  item.user_id, item.company_id)" taggable push-tags>

                                            </v-select>
                                        </td>
                                        <td class="align-middle h4">
                                        <span class="btn_delete_user" v-on:click="deleteEntry(item.user_id, item.company_id, index)">
                                        </span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>

        <!-- Share Modal -->
        <div class="modal fade styled_modal" id="shareModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-top modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.create_new') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form v-on:submit="shareProject()">
                            <div class="row">
                                <div class="col-sm-12 form-group">

                                    <div class="btn-group btn-group-toggle customized_tabs" style="width: 100%">
                                        <label :class="'btn btn-outline-primary customized_tab btn-toggle ' + (share_type == 'users' ? 'active' : '')" for="type_users">
                                            <input type="radio" id="type_users" v-model="share_type" value="users"> {{ trans('custom.users') }}
                                        </label>
                                        <label :class="'btn btn-outline-primary customized_tab btn-toggle ' + (share_type == 'company' ? 'active' : '')"  for="type_company">
                                            <input type="radio" id="type_company" v-model="share_type" value="company"> {{ trans('custom.company') }}
                                        </label>
                                    </div>
                                </div>
                                <template v-if="share_type == 'users'">
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.email') }}</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <v-select :options="emails"  :clearable="true" label="email" v-model="share_email" name="email" @input="onChange" taggable push-tags>
                                                    <template v-slot:option="option">
                                                        <img class="img-responsive img-circle align-middle mr-2" v-if="option.picture" :src="option.picture" />
                                                        {{ option.email }}
                                                    </template>
                                                </v-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group scroll-thin modal_scroll_cont">
                                        <table class="table table-striped table-responsive-md" v-if="share_users.length > 0">
                                            <thead>
                                                <th>{{ trans('custom.email') }}</th>
                                                <th class="width-200">{{ trans('custom.role') }}</th>
                                                <th>{{ trans('custom.actions') }}</th>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item, index in share_users">
                                                    <td class="align-middle name_cell">
                                                        <img class="avatar" :src="item.picture"  />
                                                        <div class="name_holder">
                                                                <span class="name alone">{{ item.email }}</span>
                                                        </div>

                                                    </td>
                                                    <td class="align-middle text-capitalize font-weight-bold">
                                                        <v-select :options="roles_arr" v-model="item.role" label="name"  name="name"  required taggable push-tags>

                                                        </v-select>
                                                    </td>
                                                    <td class="align-middle h4">
                                                        <span class="btn_delete_user" v-on:click="deleteSharedEmail(index)"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="col-sm-12 form-group">
                                        <label class="control-label">{{ trans('custom.company') }}</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <v-select :options="companies" label="title" v-model="share_company" @input="onChangeCompany">
                                                    <template v-slot:option="option">
                                                        {{ option.title }}
                                                    </template>
                                                </v-select>
                                            </div>
                                        </div>
                                        <div class="mt-4 mb-4">
                                            {{trans('custom.in_the_field_below_select_an_administrator_from_the_company')}}
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 admins_wrapp">
                                                <v-select :options="company_admins" label="email" v-model="share_email"  @input="onChange">
                                                    <template v-slot:option="option">
                                                        <img class="img-responsive img-circle align-middle mr-2" v-if="option.picture" :src="option.picture" />
                                                        {{ option.email }}
                                                    </template>
                                                </v-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group modal_scroll_cont scroll-thin">
                                        <table class="table table-striped table-responsive-md" v-if="share_users.length > 0">
                                            <thead>
                                            <th>{{ trans('custom.email') }}</th>
                                            <th class="width-200">{{ trans('custom.role') }}</th>
                                            <th>{{ trans('custom.actions') }}</th>
                                            </thead>
                                            <tbody>
                                            <tr v-for="item, index in share_users">
                                                <td class="align-middle name_cell">
                                                    <img class="avatar" :src="item.picture"  />
                                                    <div class="name_holder">
                                                        <span class="name alone">{{ item.email }}</span>
                                                    </div>

                                                </td>
                                                <td class="align-middle text-capitalize font-weight-bold">
                                                    <v-select :options="no_visitor_roles_arr" v-model="item.role" label="name"  name="name"  required>

                                                    </v-select>
                                                </td>
                                                <td class="align-middle h4">
                                                    <span class="btn_delete_user" v-on:click="deleteSharedEmail(index)"></span>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <div class="col-sm-12 form-group text-left">
                                    <button class="btn btn-primary width-150">{{ trans('custom.confirm') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                visibility_data: [],
                default_role: {
                    name: 'visitor',
                },
                roles: [],
                roles_arr: [],
                no_visitor_roles_arr: [],
                companies: [],
                projectId: null,
                companyId: null,
                allowed_create: true,
                emails: [],
                share_users: [],
                share_emails_list:[],
                share_email: '',
                share_type: 'users',
                share_company: [],
                share_companies: [],
                company_admins: [],
            }
        },
        mounted() {
            this.projectId = this.$route.params.id;
            this.companyId = this.$route.params.company_id;
            var app = this;
            axios.get('/api/v1/user/roles?limit=share').then((resp) => {
                app.roles = resp.data;
                for (const role in resp.data) {
                    app.roles_arr.push(role);
                    if (role !== 'visitor') {
                        app.no_visitor_roles_arr.push(role);
                    }
                }
            }).catch((data) => {
                if (data.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    this.$dialog.alert("Could not load roles")
                }
            });

            axios.get('/api/v1/projects/' + this.projectId + '/visibility').then((resp) => {
                this.visibility_data = resp.data;
            }).catch((data) => {
                if (data.response.status !== 401) {
                    this.$dialog.alert("Could not load visibility")
                }
            });

            axios.get('/api/v1/projects/emails').then((resp) => {
                this.emails = resp.data;
            });

            $(this.$refs.vuemodal).on("hidden.bs.modal", () => {
                this.share_type = 'users';
                this.share_email = '';
                this.share_company = [];
                this.share_companies = [];
                this.share_users = [];
            });

            axios.get('/api/v1/user/companies?for_visibility=true').then(resp => {
                var company_data = [];
                for (let key in resp.data) {
                    if (resp.data[key].id != app.companyId) {
                        company_data.push(resp.data[key]);
                    }
                }
                app.companies = company_data;
            }).catch(() => {
                if (data.response.status !== 401) {
                    this.$dialog.alert("Could not load user companies");
                }
            });

            $('.customized_tab').on('click', function () {
                app.share_users = [];
                app.share_email = '';
                app.share_emails_list = [];
            });
        },
        methods: {
            onChange(input) {
                if(typeof input == 'object' && input.email || input.id){
                    this.addUser()
                } else {
                    if(input != '') {
                        this.share_email = {
                            'email': input
                        };
                        this.addUser();
                    }
                }
            },
            onChangeCompany(item) {
                for(let admin_key in item.admins){
                    item.admins[admin_key].role = 'manager';
                }
                this.company_admins = item.admins;
                setTimeout(function () {
                    $('.admins_wrapp .vs__actions .clear').click();
                }, 100);
                this.share_users = [];
                this.share_emails_list = [];
            },
            deleteSharedEmail(index) {
                this.share_users.splice(index, 1)
            },
            changeRole(value, user, company) {

                if (this.visibility_data)

                    if (value) {
                        axios.put('/api/v1/projects/' + this.projectId + '/visibility', {
                            user_id: user,
                            company_id: company,
                            role: value
                        }).then(() => {
                            this.$dialog.alert("Role changed successfully");
                        }).catch((data) => {
                            if (data.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                this.$dialog.alert("Could not edit your project");
                            }
                        });
                    }
            },
            addUser() {
                 if(this.share_emails_list.indexOf(this.share_email.email) == -1) {
                    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.share_email.email)) {
                        this.share_users.push(this.share_email);
                        this.share_emails_list.push(this.share_email.email);
                        this.share_email = ''
                    } else {
                        this.$dialog.alert(trans('custom.wrong_email'))
                    }
                }
            },

            deleteEntry(user, company, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + app.projectId + '/visibility', {
                            data: {
                                user_id: user,
                                company_id: company
                            }
                        }).then(() => {
                            app.visibility_data.splice(index, 1);
                        }).catch((data) => {
                            if (data.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert("Could not delete user");
                            }
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            shareProject() {
                event.preventDefault();

                const req = {
                    'type': 'users',
                    'data': this.share_users,
                    'id': this.projectId
                };

                axios.post('/api/v1/projects/' + this.projectId + '/visibility', req).then(() => {
                    $('#shareModal').modal('hide');
                    $('.modal-backdrop').remove();
                    this.company_admins = [];

                    axios.get('/api/v1/projects/' + this.projectId + '/visibility').then((resp) => {
                        this.visibility_data = resp.data;
                    }).catch(() => {
                        this.$dialog.alert("Could not load visibility")
                    });
                }).catch((data) => {
                    if (data.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        this.$dialog.alert("Could not add visibility");
                    }
                });
            },

        }
    }
</script>

<style scoped>
    #shareModal{
        overflow: hidden;
    }
</style>


