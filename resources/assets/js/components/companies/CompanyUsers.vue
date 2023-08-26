
<template>
    <div v-if="company" class="user_index">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <h1 class="h2">{{ company.title }} {{ trans('custom.users') }}</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <a href="#" data-toggle="modal" data-target="#addUserModal" class="btn btn-primary width-200">{{ trans('custom.invite_user') }}</a>
                </div>
            </div>
        </div>

        <div v-if="!company.users" class="text-center">{{ trans('custom.no_users_found') }}</div>

        <div v-if="company.users">
            <div class="card my-3 pb-5">
                <div class="card-body">
                    <table class="table table-striped table-responsive-md">
                        <thead>
                        <th>{{ trans('custom.name') }}</th>
                        <th>{{ trans('custom.email') }}</th>
                        <th class="width-200">{{ trans('custom.role') }}</th>
                        <th width="150px" v-if="allowedDisplay('company_create', 'company', company_id)">{{ trans('custom.actions') }}</th>
                        </thead>
                        <tbody>
                        <tr v-for="user, index in company.users">
                            <td class="align-middle name_cell">
                                <img class="avatar" :src="user.picture"  />
                                <div class="name_holder">
                                    <template v-if="user.job_title !=null">
                                        <span class="name">{{ user.name }}</span>
                                        <span v-if="user.job_title !=null" class="job_title">{{ user.job_title }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="name alone">{{ user.name }}</span>
                                    </template>
                                </div>

                            </td>
                            <td class="align-middle">
                                {{ user.email }}
                            </td>
                            <td class="align-middle text-capitalize font-weight-bold">
                                <template v-if="!allowedDisplay('company_create', 'company', company_id) || (user.role == 'super_user' && !is_super_user)">
                                    <span v-if="user.role !='super_user'">
                                        {{ roles[user.role].name }}
                                    </span>
                                    <span v-else>
                                        Super User
                                    </span>
                                </template>
                                <template v-else>
                                    <v-select :options="roles_arr" v-model="user.role" @input="changeRole($event, user.id)" label="name"  name="name"  required taggable push-tags>

                                    </v-select>
                                </template>
                            </td>
                            <td class="align-middle h4">
                                <template v-if="(allowedDisplay('company_create', 'company', company_id) || is_super_user)">
                                <a v-if="user.company_id == company_id || is_super_user" href="#" v-on:click.prevent="openEditUser(user)" class="btn_edit_user" data-toggle="modal" data-target="#editUserModal"></a>
                                <span v-else class="invisible btn_edit_user"></span>
                                <span class="btn_delete_user" v-on:click="deleteEntry(user.id, index)">
                                </span>
                                </template>
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- Add User Modal -->
        <div class="modal fade styled_modal" id="addUserModal" role="dialog" ref="addUserModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.invite_user') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form v-on:submit="submitInvite()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.email') }}</label>
                                    <input name="email" type="text" v-model="new_user.email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.role') }}</label>
                                    <v-select :options="roles_arr" v-model="new_user.role" label="name"  class="text-capitalize" name="name"  required taggable push-tags>

                                    </v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group text-left">
                                    <button class="btn btn-primary width-150">{{ trans('custom.create') }}</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade styled_modal" id="editUserModal" role="dialog" ref="editUserModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.edit_user') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form v-on:submit="saveUserForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.company') }}</label>
                                    <v-select
                                            v-model="user.company_id"
                                            :options="companies"
                                            label="title"
                                            index="id"
                                            :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.name') }}</label>
                                    <input name="name" type="text" v-model="user.name" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.email') }}</label>
                                    <input name="email" type="text" v-model="user.email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.phone') }}</label>
                                    <input name="phone" type="text" v-model="user.phone" class="form-control">
                                </div>
                            </div>
                          <!--  <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.password') }}</label>
                                    <input name="password" type="password" v-model="user.password" class="form-control" min="5">
                                </div>
                            </div>-->
                            <div class="row text-capitalize">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.role') }}</label>
                                    <v-select :options="roles_arr"  v-model="user.role" label="name"  name="name" taggable push-tags>

                                    </v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group text-left">
                                    <button class="btn btn-primary width-150">{{ trans('custom.save') }}</button>
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
        data() {
            return {
                company_id: this.$route.params.id,
                roles: [],
                roles_arr: [],
                display_roles: [],
                display_roles_arr:[],
                allowedDisplay: function(action, model = false, id= false) {
                    return window.allowedDisplay(action, model, id)
                },
                is_super_user : typeof window.perosnal_permissions.is_super_user == 'undefined'? false:true,
                new_user: {
                    email: null,
                    company_id: this.$route.params.id,
                    role: 'visitor'
                },
                company: [],
                companies: [],
                user: {
                    name: null,
                    email: null,
                    role: 'visitor',
                    password: null,
                    company_id: null,
                    phone: null,
                },
            }
        },
        mounted() {
            var app = this;
            axios.get('/api/v1/companies/' + this.company_id + '/edit').then((resp) => {
                this.company = resp.data;
            }).catch(function (error) {
                if (error.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert("Could not load your company")
                }
            });


            axios.get('/api/v1/user/roles?limit=invite').then((resp) => {
                app.roles = resp.data;
                for (const role  in resp.data) {
                    app.roles_arr.push(role)
                }
            }).catch((error) => {
                if (error.response.status !== 401) {
                    app.$dialog.alert("Could not load roles")
                }
            });

            axios.get('/api/v1/user/roles').then((resp) => {
                app.display_roles = resp.data;
                for (const role  in resp.data) {
                    app.display_roles_arr.push(role)
                }
                if(app.is_super_user){
                    app.roles_arr = app.display_roles_arr;
                }
            }).catch(() => {
                if (error.response.status !== 401) {
                    app.$dialog.alert("Could not load roles")
                }
            });

            axios.get('/api/v1/companies').then(resp => {
                for(let key in resp.data){
                    app.companies.push({
                        'id':resp.data[key].id,
                        'title':resp.data[key].title
                    });
                }
            }).catch(() => {
                if (error.response.status !== 401) {
                    app.$dialog.alert(window.trans('custom.error_load_company'));
                }
            });



        },
        methods: {
            openEditUser(user){
                this.user = user;
            },
            changeRole(value, id) {
                var app = this;
                if (value) {
                    axios.put('/api/v1/companies/' + app.company_id + '/user/' + id, {role: value}).then(() => {
                        this.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.role_changed'), {'html':true, okText:'ok'});
                    }).catch((error) => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.error_update_role'));
                        }
                    });
                }
            },


            submitInvite() {
                event.preventDefault();
                var app = this;
                axios.post('/api/v1/companies/' + app.new_user.company_id + '/invite', app.new_user)
                    .then(() => {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + trans('custom.data_saved_successfully'), {'html':true, okText:'ok'}).then((dialog) =>
                        {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not invite user:'), {'html': true});
                        }
                    });
            },

            deleteEntry(id, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/companies/' + app.company_id + '/user/' + id).then(() => {
                            app.company.users.splice(index, 1);
                        }).catch((error) => {
                            if (error.response.status === 401) {
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
            saveUserForm() {
                event.preventDefault();
                var app = this;
                var newUser = app.user;
                axios.put('/api/v1/user/' + newUser.id, newUser)
                    .then(function (resp) {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + trans('custom.data_saved_successfully'), {
                            'html': true,
                            okText: 'ok'
                        }).then((dialog) => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not edit user:'), {'html': true});
                        }
                    });

            }
        }
    }
</script>
