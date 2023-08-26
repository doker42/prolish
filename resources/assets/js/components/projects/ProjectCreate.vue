
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">{{ trans('custom.new_project') }}</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectsIndex', params: {id: $route.params.id}}" class="btn back_btn">{{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.title') }}</label>
                                <input v-validate="'required'" name="title" type="text" v-model="project.title" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.description') }}</label>
                                <textarea v-model="project.description" class="form-control" rows="7"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.address') }}</label>
                                <places v-validate="'required'" type="text" v-model="project.address" class="form-control" required
                                        @change="changeAddress"
                                        :options="{ appId: 'plQCIBC0U0NA', apiKey: 'c4d78aefebb6e8c565275c6ec8aff1d2' }"
                                >
                                </places>
                                <div class="row mt-2">
                                    <div class="col-sm-12 col-md-2 form-group">
                                        <label class="control-label">Latitude</label>
                                        <input type="number" step="0.0001" v-model="geo.lat" class="form-control">
                                    </div>
                                    <div class="col-sm-12 col-md-2 form-group">
                                        <label class="control-label">Longitude</label>
                                        <input type="number" step="0.0001" v-model="geo.lng" class="form-control">
                                    </div>
                                </div>
                                <input type="hidden" v-model="project.geo_point"/>
                            </div>
                        </div>
                        <div class="row" v-if="companies.length > 1">
                            <div class="col-sm-5 form-group">
                                <label class="control-label">{{ trans('custom.belongs_to_company') }}</label>
                                <v-select
                                        v-model="project.company_id"
                                        :options="companies"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                ></v-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label cursor-pointer">
                                    {{ trans('custom.public') }}
                                    <a :href="publicUrl" target="_blank">({{ trans('custom.preview') }})</a>
                                </label>
                                <br>
                                <label class="switch">
                                    <input type="checkbox" v-model="project.public">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label class="control-label">{{ trans('custom.status') }}</label>
                                <select v-model="project.status" class="form-control">
                                    <option value="1">{{ trans('custom.published') }}</option>
                                    <option value="0">{{ trans('custom.hidden') }}</option>
                                </select>
                            </div>
                        </div>
                        <simple-file-upload @uploaded="imageUploaded"></simple-file-upload>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-success">{{ trans('custom.create') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Places from 'vue-places'

    export default {
        data: function () {
            return {
                project: {},
                companies: [],
                publicUrl: window.publicUrl,
                geo: {
                    lat: 0,
                    lng: 0
                }
            }
        },
        mounted() {

            axios.get('/api/v1/companies/verified').then((resp) => {
                if(typeof window.perosnal_permissions.is_super_user == 'undefined'){
                    var pers_admin_companies = [];
                    for (const id in resp.data) {
                        let comp_id = resp.data[id].id;
                        if(window.perosnal_permissions.company[comp_id] === 'administrator' || window.perosnal_permissions.company[comp_id] === 'manager'){
                            pers_admin_companies.push(resp.data[id]);
                        }
                    }
                    this.companies = pers_admin_companies;
                } else {
                    this.companies = resp.data;
                }
                this.project.company_id = this.companies[0].id;

            }).catch(() => {
                alert("Could not load companies");
            });
        },
        methods: {
            imageUploaded(file) {
                this.project.image = file;
            },
            saveForm() {
                event.preventDefault();

                this.project.geo_point = this.geo;

                axios.post('/api/v1/projects', this.project).then(() => {
                    this.$router.push({path: '/' + this.project.company_id + '/projects'});
                }).catch(() => {
                    alert("Could not create new project");
                });
            },
            changeAddress(e) {
                if (e.latlng) {
                    this.project.geo_point = e.latlng;
                    this.geo = e.latlng;
                }
            }
        },
        components: {
            Places
        }
    }
</script>
