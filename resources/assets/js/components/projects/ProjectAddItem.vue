<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'projectItems', params: {id: projectId, company_id: companyId}}"
                                 class="btn back_btn"> {{ trans('custom.back') }}
                    </router-link>
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
                                <label class="control-label">{{ trans('custom.type') }}</label>
                                <select v-model="item.type" class="form-control" @change="typeChange">
                                    <option value="">------</option>
                                    <option v-for="type, name in types" :value="name">{{ trans('custom.file_names.' +
                                        name) }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div v-if="selectedType !== null">
                            <div class="row" v-for="type, type_index in this.mergeTypes(selectedType.types)">
                                <div class="col-sm-12 form-group">
                                    <hr />
                                    <template v-if="simpleInput(type.join(', '))">
                                        <label v-if="type.join(', ') === 'youtube'" class="control-label"><b>YouTube/Vimeo</b>
                                            shareable link</label>
                                        <label v-if="type.join(', ') === 'view_url'" class="control-label">View url</label>

                                        <input :name="'url[' + item.type + ']'"
                                               placeholder="https://www.youtube.com/embed/ye-C-OOFsX8" type="text"
                                               v-model="item.urls[type.join(', ')]" class="form-control"/>
                                    </template>
                                    <template v-else>
                                        <tabs :options="{ useUrlFragment: false }" ref="itemsTabs">
                                            <tab :name="trans('custom.upload_file')">
                                                <label class="control-label">{{ trans('custom.choose') }} <b> {{type.join(', ')}} </b> {{
                                                    trans('custom.file_to_upload') }}</label>
                                                <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                              v-on:vdropzone-processing="fileProcessing"
                                                              v-on:vdropzone-success="showSuccess"
                                                              v-on:vdropzone-error="uploadErrorHandle"
                                                              :ref="'dropzone_' + type_index" :id="'dropzoneId_' + type.join('_')"
                                                              :options="dropzoneOptions(type.join(','), type_index)"></vue-dropzone>
                                            </tab>

                                            <tab name="Google Drive">
                                                <label class="control-label">{{ trans('custom.google_drive_url') }}</label>
                                                <input type="url" v-model="item.urls[type]" class="form-control" placeholder="https://drive.google.com/file/d/XoChevujOfacRe3lcrav/view" />
                                            </tab>
                                        </tabs>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <div v-show="submitted" class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                         role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0"
                                         aria-valuemax="100" :style="'width:' + uploadPercentage + '%'"></div>
                                </div>

                                <button class="btn btn-success" v-if="!submitted">
                                    <span>{{ trans('custom.add') }}</span>
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
            this.projectId = this.$route.params.id;
            this.companyId = this.$route.params.company_id;

            axios.get('/api/v1/projects/' + this.projectId)
                .then((resp) => {
                    this.project = resp.data;
                    this.item.project_id = this.projectId;
                })
                .catch(function () {
                    alert("Could not load your project")
                });

            axios.get('/api/v1/potree/types')
                .then((resp) => {
                    this.types = (this.$route.params.type) ? resp.data[this.$route.params.type] : resp.data['files'];
                })
                .catch(function () {
                    alert("Could not load types")
                });
        },
        data() {
            return {
                projectId: null,
                companyId: null,
                project: {
                    title: '',
                    description: '',
                    status: 1,
                    image: ''
                },
                item: {
                    project_id: '',
                    title: '',
                    description: '',
                    type: '',
                    urls: [],
                    upload: [],
                    job_done_at: new Date()
                },
                types: null,
                selectedType: null,
                submitted: false,
                uploadPercentage: 0,
                totalFiles: 0,
                uploadProgresses: []
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

                for (let key in this.item) {
                    if (this.item[key].constructor === Array) {
                        for (let array_key in this.item[key]) {
                            if (this.item[key][array_key]) {
                                formData.append(key + '[' + array_key + ']', this.item[key][array_key]);
                            }
                        }
                    } else {
                        if (key == 'job_done_at') {
                            formData.append(key, this.item[key].toDateString());
                        } else {
                            formData.append(key, this.item[key]);
                        }
                    }
                }

                axios.post('/api/v1/projects/' + this.projectId + '/item', formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(() => {
                    this.$router.push({path: '/' + this.companyId + '/projects/' + this.projectId + '/items'});
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
            submitForm() {
                event.preventDefault();

                if (!this.submitted) {
                    if (this.selectedType && this.selectedType.types) {
                        let merged_types = this.mergeTypes(this.selectedType.types);
                        for (let index in merged_types) {
                            if (!this.simpleInput(merged_types[index])) {
                                if (this.$refs['dropzone_' + index].length > 0) {
                                    this.$refs['dropzone_' + index][0].processQueue();
                                }
                            }
                        }

                        if (this.totalFiles < 1) {
                            this.saveForm();
                        }
                    }
                }
            },
            typeChange(e) {
                this.item.urls = [];
                this.selectedType = (this.types[e.target.value]) ? this.types[e.target.value] : null;

                if (this.selectedType.types) {
                    let merged_types = this.mergeTypes(this.selectedType.types);
                    for (let index in merged_types) {
                        const type = merged_types[index].join(',');
                        if (this.$refs['dropzone_' + index] && this.$refs['dropzone_' + index].length > 0) {
                            this.$refs['dropzone_' + index][0].removeAllFiles()
                            this.$refs['dropzone_' + index][0].setOption('acceptedFiles', type)
                            this.$refs['dropzone_' + index][0].dropzone.hiddenFileInput.accept = type
                        }
                    }
                }
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
                    chunkSize: 5120000, // 5mb
                    timeout: 3600000, // 1 hour
                    maxFilesize: null
                }
            }
        }
    }
</script>
