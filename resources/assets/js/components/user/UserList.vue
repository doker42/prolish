
<template>
    <div class ="user_index">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-2">
            <span class="special_title"><b>{{ trans('custom.users') }}</b></span>
        </div>

        <div class="row mb-4">
            <div class="col-sm-4">
                <div class="input-group search_input">
                    <input type="text" class="form-control" placeholder="Search" v-model="search" @keyup="searchProject">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="search_icon"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 select_holder">
                <span class="select_top_title">{{ trans('custom.belongs_to') }}</span>
                <v-select :options="companies_filt"
                          label="title"
                          index="id"
                          v-if="companies_filt.length > 1"
                          v-model="belongs_to"
                          taggable push-tags
                          @input="filterUsers()">
                </v-select>
            </div>
            <div class="col-sm-2"></div>
            <div class="col-sm-2">
                <a href="#" v-on:click.prevent="openCreateUser()" class="btn w-125 float-right btn-primary" data-toggle="modal" data-target="#createUserModal">{{ trans('custom.create_user') }}</a>
            </div>
        </div>

        <div v-if="!users.data.length" class="text-center">{{ trans('custom.no_users_found') }}</div>

        <div v-if="users.data.length && roles">
            <div class="card my-3">
                <div class="card-body">
                    <table class="table table-responsive-md">
                        <thead>
                            <th>{{ trans('custom.name') }}<i style="cursor:pointer" v-on:click="changeOrder('name')" :class="{ 'sort' : typeof order_data.field !== 'name','sort-asc' : order_data.field === 'name' && order_data.dir === 'ASC' , 'sort-desc' : order_data.field === 'name' && order_data.dir === 'DESC'}" class="order-name"></i></th>
                            <th>{{ trans('custom.email') }}</th>
                            <th>{{ trans('custom.add_date')}}<i style="cursor:pointer" v-on:click="changeOrder('created_at')" :class="{ 'sort' : typeof order_data.created_at !== 'created_at','sort-asc' : order_data.field === 'created_at' && order_data.dir === 'ASC', 'sort-desc' : order_data.field === 'created_at' && order_data.dir === 'DESC'}" class="order-date"></i></th>
                            <th>{{ trans('custom.company') }}</th>
                            <th>{{ trans('custom.role') }}</th>
                            <th width="150px">{{ trans('custom.actions') }}</th>
                        </thead>
                        <tbody>
                        <tr v-for="user, index in users.data">
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
                            <td class="align-middle">
                                {{formatDate(user.created_at)}}
                            </td>
                            <td class="align-middle">
                                <span v-if="user.company">{{ user.company.title }}</span>
                            </td>
                            <td class="align-middle text-capitalize font-weight-bold">
                                {{ display_roles[user.role].name }}
                            </td>
                            <td class="align-middle h4">
                                <template v-if="user.can_update">
                                    <a href="#" v-on:click.prevent="openEditUser(user)" class="btn_edit_user" data-toggle="modal" data-target="#createUserModal"></a>
                                    <span class="btn_delete_user" v-on:click="deleteEntry(user.id)">
                                    </span>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <vue-pagination  :pagination="users"
                                 @paginate="load()"
                                 :offset="5">
                </vue-pagination>
            </div>
        </div>
        <!-- Create User Modal -->
        <div class="modal fade styled_modal" id="createUserModal" role="dialog" ref="createUserModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="user.id == null" class="modal-title">{{ trans('custom.create_user') }}</h5>
                        <h5 v-else class="modal-title">{{ trans('custom.edit_user') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form v-on:submit="saveUserForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.company') }}*</label>
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
                                    <label class="control-label">{{ trans('custom.name') }}*</label>
                                    <input name="name" type="text" v-model="user.name" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.email') }}*</label>
                                    <input name="email" type="text" v-model="user.email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.phone') }}*</label>
                                    <input name="phone" type="text" v-model="user.phone" class="form-control">
                                </div>
                            </div>
                            <div class="row" v-if="user.id == null">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.password') }}*</label>
                                    <input name="password" type="password" v-model="user.password" class="form-control" min="5">
                                </div>
                            </div>
                            <div class="row text-capitalize">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.role') }}*</label>
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
    import VuePagination from '../Pagination';

    export default {
        data() {
            return {
                users: {
                    total: 0,
                    from: 1,
                    to: 0,
                    current_page: 1,
                    data: []
                },
                roles: [],
                roles_arr:[],
                display_roles:[],
                display_roles_arr:[],
                search: '',
                order_data:{
                    'field' : 'created_at',
                    'dir' : 'DESC',
                },
                companies: [],
                companies_filt:[],
                belongs_to: 0,
                searchMemory: '',
                searchDelay: null,
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
        components: {
            VuePagination
        },
        mounted() {
            axios.get('/api/v1/usersettings', {
                params: {
                    'settings_key': 'users_page_settings',
                }
            }).then(resp => {
                this.order_data.field = (typeof resp.data.order_settings.field !== 'undefined') ? resp.data.order_settings.field : 'created_at';
                this.order_data.dir  = (typeof resp.data.order_settings.dir !== 'undefined') ? resp.data.order_settings.dir : 'DESC';
                this.belongs_to = (typeof resp.data.belongs_to !== 'undefined') ? resp.data.belongs_to : 0;
                this.load();
            }).catch(() => {
                this.load();
                console.log("Could not load settings")
            });
            var app = this;
            axios.get('/api/v1/user/roles?limit=invite').then((resp) => {
                app.roles = resp.data;
                for (const role  in resp.data) {
                    app.roles_arr.push(role)
                }
            }).catch((error) => {
                if (error.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert("Could not load roles")
                }
            });

            axios.get('/api/v1/user/roles').then((resp) => {
                app.display_roles = resp.data;
                for (const role  in resp.data) {
                    app.display_roles_arr.push(role)
                }
            }).catch((error) => {
                if (error.response.status !== 401) {
                    app.$dialog.alert("Could not load roles")
                }
            });

            axios.get('/api/v1/companies').then(resp => {
                app.companies_filt.push({
                    'id':'0',
                    'title':'All companies'
                });
                for(let key in resp.data){
                    let new_elem = {
                        'id':resp.data[key].id,
                        'title':resp.data[key].title
                    };
                    app.companies.push(new_elem);
                    app.companies_filt.push(new_elem);

                }
            }).catch((error) => {
                if (error.response.status !== 401) {
                    app.$dialog.alert(window.trans('custom.error_load_company'));
                }
            });
        },
        methods: {
            load() {
                var app = this;
                axios.get('/api/v1/user/list', {
                    params: {
                        'page': this.users.current_page,
                        'query': this.search,
                        'belongs': this.belongs_to,
                        'order_field': this.order_data.field,
                        'order_dir':this.order_data.dir,
                    }
                }).then(resp => {
                    this.users = resp.data;
                }).catch((error) => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not load users");
                    }
                });
            },
            filterUsers()
            {
                this.load();
                this.updateUserSettings();
            },
            openEditUser(user){
                this.user = user;
            },
            openCreateUser(){
                this.user = {
                    name: null,
                    email: null,
                    role: 'visitor',
                    password: null,
                    company_id: null,
                    phone: null,
                };
            },

            updateUserSettings() {
                axios.put('/api/v1/usersettings', {
                    'settings_key': 'users_page_settings',
                    'settings_value': {
                        'order_settings': this.order_data,
                        'belongs_to': this.belongs_to,
                    },
                }).then(function (resp) {
                    console.log('View settings updated');
                }).catch(function (resp) {
                    console.log('Failed to update View settings');
                });
            },
            searchProject() {
                if (this.searchDelay) {
                    clearTimeout(this.searchDelay);
                    this.searchDelay = null;
                }
                this.searchDelay = setTimeout(() => {
                    if (this.search != this.searchMemory) {
                        this.searchMemory = this.search;
                        this.users.current_page = 0;
                        this.load()
                    }
                }, 800);
            },
            formatDate(string) {
                var d = new Date(string),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;

                return [year, month, day].join('-');
            },
            changeOrder(field_name){
                if (this.order_data.field !== field_name) {
                    this.order_data.field = field_name;
                    this.order_data.dir = 'DESC';
                } else {
                    if (this.order_data.dir === "DESC"){
                        this.order_data.dir = "ASC";
                    } else {
                        this.order_data.dir = "DESC";
                    }
                }
                this.updateUserSettings();
                this.load();
            },
            deleteEntry(id) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/user/' + id).then(() => {
                            app.users.data = app.users.data.filter(function (user) {
                                return user.id != id;
                            })
                        })
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            saveUserForm() {
                event.preventDefault();
                var app = this;
                var newUser = app.user;

                if (newUser.id > 0) {
                    axios.put('/api/v1/user/' + newUser.id, newUser)
                        .then(function (resp) {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + trans('custom.data_saved_successfully'), {'html':true, okText:'ok'}).then((dialog) =>
                            {
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
                } else {
                    axios.post('/api/v1/user', newUser)
                        .then(function (resp) {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + trans('custom.data_saved_successfully'), {'html':true, okText:'ok'}).then((dialog) =>
                            {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            if (error.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not create user:'), {'html': true});
                            }
                        });
                }
            }
        }
    }
</script>
