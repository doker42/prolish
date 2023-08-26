
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectItems', params: {id: projectId}}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <input type="hidden" name="project_id" v-model="project.id">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.choose') }} {{ trans('custom.email') }}</label>
                                <v-select :options="emails" label="email" v-on:change="selectEmail">
                                    <template v-slot:option="option">
                                        <img class="img-responsive img-circle align-middle mr-2" :src="option.picture" />
                                        {{ option.email }}
                                    </template>
                                </v-select>
                            </div>

                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.email') }}</label>
                                <input name="email" type="text" v-model="contact.email" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.full_name') }}</label>
                                <input name="fullname" type="text" v-model="contact.fullname" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.position') }}</label>
                                <input name="position" type="text" v-model="contact.position" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.phone') }}</label>
                                <input name="phone" type="text" v-model="contact.phone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-success">{{ trans('custom.add') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.projectId = this.$route.params.id;
            this.companyId = this.$route.params.company_id;
            var app = this;
            axios.get('/api/v1/projects/' + this.projectId).then((resp) => {
                this.project = resp.data;
                this.contact.project_id = this.projectId;
            }).catch(() => {
                app.$dialog.alert("Could not load your project")
            });

            axios.get('/api/v1/projects/emails').then((resp) => {
                this.emails = resp.data;
            });
        },
        data: function () {
            return {
                projectId: null,
                companyId: null,
                project: {
                    title: '',
                    description: '',
                    status: 1,
                    image: ''
                },
                contact: {
                    fullname: '',
                    email: '',
                    phone: '',
                    project_id: null,
                    position: ''
                },
                emails: []
            }
        },
        methods: {
            saveForm() {
                event.preventDefault();
                let app = this,
                    formData = app.contact;

                axios.post('/api/v1/projects/' + app.projectId + '/contact', formData)
                    .then(function (resp) {
                        app.$router.push({path: '/' + app.companyId + '/projects/' + app.projectId + '/items'});
                    })
                    .catch(error => {
                        app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not create new contact:'), {'html':true});
                });
            },
            selectEmail(e) {
                if (e && e.email) {
                    this.contact.fullname = e.name;
                    this.contact.email = e.email;
                    this.contact.phone = e.phone;
                } else {
                    this.contact.fullname = '';
                    this.contact.email = '';
                }
            }
        }
    }
</script>
