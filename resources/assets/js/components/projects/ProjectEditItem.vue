
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectItems', params: {id: projectId, company_id: companyId}}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="submitForm()">
                        <input type="hidden" name="project_id" v-model="project.id">
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.title') }}</label>
                                <input name="title" type="text" v-model="item.title" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label class="control-label">{{ trans('custom.description') }}</label>
                                <textarea v-model="item.description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 form-group mb-2">
                                <label class="control-label">{{ trans('custom.job_done_at') }}</label>
                                <v-date-picker v-model="item.job_done_at" is-inline></v-date-picker>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label class="control-label">{{ trans('custom.type') }}</label><br/>
                                {{ trans('custom.file_names.' + item.type) }}
                                <input type="hidden" v-model="item.type" />
                            </div>
                        </div>

                        <div v-if="selectedType !== null">
                            <div class="row">
                                 <div class="col-sm-12 form-group">
                                    <template v-if="item.url.length > 0">
                                        <template v-for="url in item.url">
                                            <div class="card shadow-none">
                                                <div class="card-horizontal">
                                                    <div class="img-square-wrapper bg-light position-relative" style="width: 100px;">
                                                        <i class="fa fa-file absolute-center" style="font-size: 50px"></i>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text"><b>{{ trans('custom.filename') }}: </b><a :href="url.url" target="_blank">{{ url.filename }}</a></p>
                                                        <p class="card-text"><b>{{ trans('custom.size') }}: </b> {{ url.size }}mb</p>

                                                        <span class="mr-2 cursor-pointer text-danger position-absolute" style="right:0; top:5%" v-on:click="deleteEntry(url.type)">
                                                                <i class="fa fa-trash-o" style="font-size: 20px"></i>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <small class="text-muted">{{ trans('custom.created_at') }}: {{ url.created_at }}</small>
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                     <template v-else>
                                         <div class="" v-for="type, type_index in this.mergeTypes(selectedType.types)">
                                             <template v-if="simpleInput(type.join(', '))">
                                                 <label v-if="type.join(', ') === 'youtube'" class="control-label"><b>YouTube/Vimeo</b>
                                                     shareable link</label>
                                                 <label v-if="type.join(', ') === 'view_url'" class="control-label">View
                                                     url</label>

                                                 <input :name="'url[' + item.type + ']'" placeholder="https://www.youtube.com/embed/ye-C-OOFsX8"
                                                        type="text" v-model="urls_tmp[type]" class="form-control"/>
                                             </template>
                                             <template v-else>
                                                 <tabs :options="{ useUrlFragment: false }" ref="itemsTabs">
                                                     <tab :name="trans('custom.upload_file')">
                                                         <label class="control-label">{{ trans('custom.choose') }} <b>
                                                             {{type.join(', ')}}</b> {{ trans('custom.file_to_upload')
                                                             }}</label>
                                                         <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                                       v-on:vdropzone-processing="fileProcessing"
                                                                       v-on:vdropzone-success="showSuccess"
                                                                       v-on:vdropzone-error="uploadErrorHandle"
                                                                       :ref="'dropzone_' + type_index"
                                                                       :id="'dropzoneId_' + type"
                                                                       :options="dropzoneOptions(type.join(','), type_index)"></vue-dropzone>
                                                     </tab>

                                                     <tab name="Google Drive">
                                                         <label class="control-label">{{
                                                             trans('custom.google_drive_url') }}</label>
                                                         <input type="url" v-model="urls_tmp[type]" class="form-control"
                                                                placeholder="https://drive.google.com/file/d/XoChevujOfacRe3lcrav/view"/>
                                                     </tab>
                                                 </tabs>
                                             </template>

                                         </div>
                                     </template>
                                 </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <div v-show="submitted" class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0" aria-valuemax="100" :style="'width:' + uploadPercentage + '%'"></div>
                                </div>

                                <button class="btn btn-success" v-if="!submitted">
                                    <span>{{ trans('custom.save') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            vueDropzone: vue2Dropzone
        },
        mounted() {
            this.itemId = this.$route.params.id;
            this.companyId = this.$route.params.company_id;
            this.projectId = this.$route.params.project_id;

            axios.get('/api/v1/projects/' + this.projectId + '/item/' + this.itemId).then(resp => {
                    this.item = resp.data;
                    this.item.upload = [];
                    this.urls_tmp = JSON.parse(JSON.stringify(this.item.urls));

                    this.item.job_done_at = resp.data.job_done_at ? new Date(resp.data.job_done_at) : new Date();

                    axios.get('/api/v1/potree/types').then(resp => {
                        this.types = resp.data['files'];
                        for (const type_name in resp.data) {
                            if(resp.data[type_name][this.item.type]){
                                this.types = resp.data[type_name];
                            }
                        }
                        this.selectedType = this.types[this.item.type];
                    }).catch(function(){
                        alert("Could not load types")
                    });
                }).catch(function () {
                    alert("Could not load your item")
                });

            axios.get('/api/v1/projects/' + this.projectId).then(resp => {
                    this.project = resp.data;
                }).catch(function () {
                    alert("Could not load your project")
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
                itemId: null,
                item: {
                    project_id: '',
                    title: '',
                    description: '',
                    type: '',
                    url: [],
                    urls: [],
                    upload: [],
                    job_done_at: new Date()
                },
                types: null,
                selectedType: null,
                submitted: false,
                uploadPercentage: 0,
                totalFiles: 0,
                uploadProgresses: [],
                urls_tmp: []
            }
        },
        methods: {
            simpleInput(type) {
                return type == 'youtube' || type == 'view_url';
            },
            mergeTypes(types) {
                var main_row_arr = [];
                var merged_types = [];
                if (types.length == 0) {
                    merged_types.push(['']);
                } else {
                    for (let index in types) {
                        let type = types[index];
                        if (type == 'youtube' || type == 'view_url') {
                            merged_types.push([type]);
                        } else {
                            main_row_arr.push('.' + type);
                        }
                    }
                    merged_types.push(main_row_arr);
                }
                return merged_types;
            },
            showSuccess(file) {
                let response = JSON.parse(file.xhr.response);
                this.item.upload[response.index] = response.path + response.name;
                this.totalFiles--;

                if (this.totalFiles === 0) {
                    this.saveForm()
                }
            },
            saveForm() {
                let formData = new FormData();

                this.item.urls = this.urls_tmp;
                for (let key in this.item) {
                    if (this.item[key] && (this.item[key].constructor === Array || this.item[key].constructor === Object)) {
                        for (let array_key in this.item[key]) {
                            if (this.item[key][array_key] || key == 'urls') {
                                formData.append(key + '[' + array_key + ']', this.item[key][array_key]);
                            }
                        }
                    } else if (this.item[key] != null) {
                        if (key == 'job_done_at') {
                            formData.append(key, this.item[key].toDateString());
                        } else {
                            formData.append(key, this.item[key]);
                        }
                    }
                }

                formData.append('_method', 'PUT');
                axios.post('/api/v1/projects/' + this.projectId + '/item/' + this.itemId, formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(() => {
                    this.$router.back();
                }).catch(error => {
                    this.resetUpload();

                    if (error.response.data && error.response.data.errors) {
                        alert("Error:\n\n" + JSON.stringify(error.response.data.errors));
                    } else if (error.response.data) {
                        alert("Error:\n\n" + JSON.stringify(error.response.data));
                    }
                });
            },
            resetUpload() {
                this.submitted = false;
                this.uploadPercentage = 0;

                if (this.selectedType.types) {
                    let merged_types = this.mergeTypes(this.selectedType.types);
                    for (let index in merged_types) {
                        if (this.$refs['dropzone_' + index].length > 0) {
                            this.$refs['dropzone_' + index][0].removeAllFiles()
                        }
                    }
                }
            },
            uploadErrorHandle(file, error) {
                this.resetUpload();
                alert("Error:\n\n" + JSON.stringify(error.message));
            },
            fileProcessing() {
                this.submitted = true;
                this.totalFiles++
            },
            uploadProgress(file, progress) {
                this.uploadProgresses[file.upload.uuid] = progress;
                this.resetProgress()
            },
            resetProgress() {
                let min = 101;
                for (let index in this.uploadProgresses) {
                    if (this.uploadProgresses[index] < min) {
                        min = this.uploadProgresses[index];
                    }
                }

                if (min < 101) this.uploadPercentage = min
            },
            deleteEntry(type) {
                var app = this;
                app.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + app.projectId + '/item/' + app.itemId + '/file/' + type).then(() => {
                            app.$router.go(0)
                        }).catch(() => {
                            app.$dialog.alert(window.trans('custom.delete_error'));
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });

            },
            dropzoneOptions(types, index) {
                return {
                    url: '/api/v1/upload-chunk?index=' + index + '&project_id=' + this.projectId,
                    paramName: 'file',
                    headers: {
                        "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                    },
                    acceptedFiles: types,
                    dictDefaultMessage: trans('custom.drop_upload_text'),
                    chunking: true,
                    createImageThumbnails: false,
                    maxFiles: 1,
                    autoProcessQueue: false,
                    addRemoveLinks: true,
                    forceChunking: true,
                    chunkSize: 1000000,
                    maxFilesize: null
                }
            },
            submitForm() {
                event.preventDefault();

                if (!this.submitted) {
                    if (this.selectedType && this.selectedType.types) {
                        let merged_types = this.mergeTypes(this.selectedType.types);
                        for (let index in merged_types) {
                            if (!this.simpleInput(merged_types[index])) {
                                if (this.$refs['dropzone_' + index] && this.$refs['dropzone_' + index].length > 0) {
                                    this.$refs['dropzone_' + index][0].processQueue();
                                }
                            }
                        }

                        if (this.totalFiles < 1) {
                            this.saveForm();
                        }
                    }
                }
            }
        }
    }
</script>
