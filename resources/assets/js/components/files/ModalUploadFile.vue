<template>
    <div>
        <!-- Upload File Modal -->
        <div class="modal fade styled_modal" id="uploadModalFileModal"  role="dialog" ref="uploadModalFileModal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="this.empty_project" class="modal-title">{{ trans('custom.add_to_project') }}</h5>
                        <h5 v-else-if="this.item_operation_type=='files'" class="modal-title">{{ trans('custom.add_file') }}</h5>
                        <h5 v-else-if="this.item_operation_type=='documents'" class="modal-title">{{ trans('custom.add_document') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitFileForm()">
                            <div v-if="this.empty_project" class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.project') }}*</label>
                                    <v-select
                                        v-model="adding_file_project_id"
                                        :options="projects_filt"
                                        label="title"
                                        @input="function(val) {
                                               new_item.project_id = val;
                                            }"
                                        :autocomplete="is_super_user?'on':'off'"
                                        index="id"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div v-if="this.empty_project" class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label>
                                    <v-select
                                        v-model="item_operation_type"
                                        :options="type_options"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.title') }}*</label>
                                    <input v-validate="'required'" v-model="new_item.title" name="title" type="text"  class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.description') }}</label>
                                    <textarea class="form-control" v-model="new_item.description" rows="2d-inline align-text-top"></textarea>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-sm-6 form-group date_picker_styled">
                                    <label class="control-label">{{ trans('custom.job_done_at') }}*</label>
                                    <v-date-picker v-model="new_item.job_done_at"></v-date-picker>
                                </div>
                                <div v-if="item_operation_type=='files'" class="col-sm-6 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label>
                                    <v-select
                                        v-model="new_item.type"
                                        :options="files_types_arr"
                                        label="name"
                                        index='value'
                                        @input="typeChange"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                                <div v-if="item_operation_type=='documents'" class="col-sm-6 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label>
                                    <v-select
                                        v-model="new_item.type"
                                        :options="doc_types_arr"
                                        label="name"
                                        index='value'
                                        @input="typeChange"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="upload_div" v-if="selectedType !== null">
                                <div class="row styled_tabs" v-for="type, type_index in this.mergeTypes(selectedType.types)">
                                    <div class="col-sm-12 form-group">
                                        <template v-if="simpleInput(type.join(', '))">
                                            <label v-if="type.join(', ') === 'youtube'" class="control-label mt-4"><b>YouTube/Vimeo</b>
                                                shareable link</label>
                                            <label v-if="type.join(', ') === 'view_url'" class="control-label mt-4">View url</label>

                                            <input :name="'url[' + new_item.type + ']'"
                                                   placeholder="https://www.youtube.com/embed/ye-C-OOFsX8" type="text"
                                                   v-model="new_item.urls[type.join(', ')]" class="form-control"/>
                                        </template>
                                        <template v-else>
                                            <tabs :options="{ useUrlFragment: false }" ref="itemsTabs">
                                                <tab :name="trans('custom.upload_file')">
                                                    <label class="control-label">{{ trans('custom.choose') }} <b> {{type.join(', ')}} </b> {{
                                                            trans('custom.file_to_upload') }}</label>
                                                    <template :class="is_loading()?'d-none':''">
                                                        <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                                      v-on:vdropzone-processing="fileProcessing"
                                                                      v-on:vdropzone-success="showSuccess"
                                                                      v-on:vdropzone-error="uploadErrorHandle"
                                                                      v-on:vdropzone-file-added="fileAdded"
                                                                      v-on:vdropzone-removed-file="removedFile"
                                                                      :useCustomSlot=true
                                                                      :include-styling="false"
                                                                      :ref="'dropzone_' + type_index" :id="'dropzoneId_' + type.join('_')"
                                                                      :options="dropzoneOptions(type.join(','), type_index)">
                                                            <span>{{trans('custom.drop_upload_text')}}</span>
                                                        </vue-dropzone>

                                                    </template>
                                                </tab>

                                                <tab name="Google Drive">
                                                    <label class="control-label">{{ trans('custom.google_drive_url') }}</label>
                                                    <input type="url" v-model="new_item.urls[type]" class="form-control" v-on:click="update_type_url(false)" placeholder="https://drive.google.com/file/d/XoChevujOfacRe3lcrav/view" />
                                                </tab>
                                                <tab name="Url">
                                                    <label class="control-label">URL</label>
                                                    <input type="url" v-model="new_item.urls[type]" v-on:click="update_type_url(true)" class="form-control"
                                                           placeholder="https://URL_to_your_file"/>
                                                </tab>
                                            </tabs>
                                        </template>
                                        <div class="file_info_warning"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group dropzone-progress text-left">

                                    <button class="btn btn-primary">
                                        <span>{{ trans('custom.upload_file') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade styled_modal custom_bar" id="uploadModalShortBar" role="dialog" ref="uploadModalShortBar"
             data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.loading_file') }}</h5>
                        <button type="button" class="close" v-on:click.prevent="resetUpload()" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">{{ trans('custom.cancel') }}</span>
                        </button>
                    </div>

                    <div class="modal-body loading_file">
                        <div class="row">
                            <div class="col-sm-12 dropzone-progress text-left">
                                <div class="file-name">{{new_item.title}}</div>
                                <div class="file-icon"></div>
                                <div v-show="submitted" class="progress">
                                    <div class="progress-bar"
                                         role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0"
                                         aria-valuemax="100" :style="'width:' + uploadPercentage + '%'">
                                        {{uploadPercentage}}%
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade styled_modal" id="editModalFileModal"  role="dialog" ref="editModalFileModal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="this.item_operation_type=='files'" class="modal-title">{{ trans('custom.edit_file') }}</h5>
                        <h5 v-if="this.item_operation_type=='documents'" class="modal-title">{{ trans('custom.edit_document') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitFileForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.title') }}*</label>
                                    <input v-validate="'required'" v-model="editing_item.title" name="title" type="text"  class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.description') }}</label>
                                    <textarea class="form-control" v-model="editing_item.description" rows="2d-inline align-text-top"></textarea>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-sm-6 form-group date_picker_styled required">
                                    <label class="control-label">{{ trans('custom.job_done_at') }}*</label>
                                    <v-date-picker v-model="editing_job_done_at"></v-date-picker>
                                </div>

                                <div class="col-sm-6 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label><br/>
                                    {{ trans('custom.file_names.' + editing_item.type) }}
                                    <input type="hidden" v-model="editing_item.type" />
                                </div>

                            </div>
                            <div class="upload_div mt-3" v-if="selectedType !== null">
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <template v-if="editing_item.url.length > 0">
                                            <template v-for="url in editing_item.url">
                                                <div class="card shadow-none">
                                                    <div class="card-horizontal">
                                                        <div class="img-square-wrapper position-relative" style="width: 100px;">
                                                            <i class="fa fa-file-other absolute-center w-50 h-50" style="font-size: 50px"></i>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text mb-0"><b>{{ trans('custom.filename') }}: </b><a :href="url.url" target="_blank">{{ url.filename }}</a></p>
                                                            <p class="card-text mb-0"><b>{{ trans('custom.size') }}: </b> {{ url.size }}mb</p>

                                                            <span class="mr-2 cursor-pointer text-danger position-absolute" style="right: 2%; top: 10%;" v-on:click="deleteItemEntryFile(url.type)">
                                                                <i class="fa fa-trash-o bucket_icon" style="font-size: 20px"></i>
                                                            </span>
                                                        </div>
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

                                                    <input :name="'url[' + editing_item.type + ']'" placeholder="https://www.youtube.com/embed/ye-C-OOFsX8"
                                                           type="text" v-model="urls_tmp[type]" class="form-control"/>
                                                </template>
                                                <template v-else>
                                                    <tabs :options="{ useUrlFragment: false }" ref="itemsTabs">
                                                        <tab :name="trans('custom.upload_file')">
                                                            <label class="control-label">{{ trans('custom.choose') }} <b>
                                                                {{type.join(', ')}}</b> {{ trans('custom.file_to_upload')
                                                                }}</label>
                                                            <template :class="is_loading()?'d-none':''">
                                                                <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                                              v-on:vdropzone-processing="fileProcessing"
                                                                              v-on:vdropzone-file-added="fileAdded"
                                                                              v-on:vdropzone-success="showSuccess"
                                                                              v-on:vdropzone-removed-file="removedFile"
                                                                              v-on:vdropzone-error="uploadErrorHandle"
                                                                              :ref="'dropzone_edit_' + type_index"
                                                                              :include-styling="false"
                                                                              :id="'dropzoneId_edit_' + type"
                                                                              :useCustomSlot=true
                                                                              :options="dropzoneOptions(type.join(','), type_index)">
                                                                    <span>{{trans('custom.drop_upload_text')}}</span>
                                                                </vue-dropzone>
                                                            </template>
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
                                <div class="col-sm-12 form-group dropzone-progress text-left">


                                    <button class="btn btn-primary">
                                        <span>{{ trans('custom.update') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade styled_modal" id="uploadModalTempStorageModal"  role="dialog" ref="uploadModalTempStorageModal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.upload_file') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitUploadForm()">

                            <div class="upload_div">
                                <label class="control-label mt-4">{{ trans('custom.choose') }} {{
                                        trans('custom.file_to_upload') }}</label>
                                <template :class="is_loading()?'d-none':''">
                                    <vue-dropzone v-on:vdropzone-upload-progress="uploadFileProgress"
                                                  v-on:vdropzone-processing="fileUploadProcessing"
                                                  v-on:vdropzone-success="showFileSuccess"
                                                  v-on:vdropzone-file-added="fileUploadAdded"
                                                  v-on:vdropzone-error="uploadFileErrorHandle"
                                                  v-on:vdropzone-removed-file="removedFileFile"
                                                  :include-styling="false"
                                                  :useCustomSlot=true
                                                  :ref="'dropzone_temp_storage'"
                                                  :id="'dropzoneId_temp_storage'"
                                                  :options="dropzoneTempSpaceOptions()">
                                        <span>{{trans('custom.drop_upload_text')}}</span>
                                    </vue-dropzone>
                                </template>
                            </div>
                            <div class="row mt-4">
                                <div class="col-sm-12 form-group dropzone-progress text-left">

                                    <button class="btn btn-primary">
                                        <span>{{ trans('custom.upload_file') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade styled_modal custom_bar" id="uploadTempModalShortBar" role="dialog" ref="uploadTempModalShortBar"
             data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.loading_file') }}</h5>
                        <button type="button" class="close" v-on:click.prevent="resetFileUpload()" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">{{ trans('custom.cancel') }}</span>
                        </button>
                    </div>

                    <div class="modal-body loading_file">
                        <div class="row">
                            <div class="col-sm-12 dropzone-progress text-left">
                                <div class="file-name">{{new_file.title}}</div>
                                <div class="file-icon"></div>
                                <div v-show="submitted" class="progress">
                                    <div class="progress-bar"
                                         role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0"
                                         aria-valuemax="100" :style="'width:' + uploadPercentage + '%'">
                                        {{uploadPercentage}}%
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade styled_modal" id="multiLoadingComingSoonModal" role="dialog" ref="multiLoadingComingSoonModal">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mt-4"><img src="/images/popup/feature_coming_soon.png" style="width: 55%;"></div>
                        <div class="text-center mb-4 mt-4" style="font-size:24px;font-weight:400;line-height: 40px;">{{ trans('custom.functional_multi_loading_coming_soon') }}</div>
                        <button class="btn btn-primary btn btn-primary mb-3" data-dismiss="modal" aria-hidden="true">Ok</button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script>
import vue2Dropzone from "vue2-dropzone";

export default {

    data() {
        return {
            files_types:{},
            files_types_arr:[],
            doc_types:{},
            doc_types_arr:[],
            new_item: {
                project_id: '',
                title: '',
                description: '',
                type: '',
                type_url:false,
                urls: [],
                upload: [],
                job_done_at: new Date()
            },
            editing_item: {
                id: null,
                project_id: '',
                title: '',
                description: '',
                type: '',
                url:[],
                urls: [],
                upload: [],
                job_done_at: new Date()
            },
            new_file: {
                title: '',
                type: 'uploading_temp_storage',
                job_done_at: new Date()
            },
            project: {
                permissions: [],
                company:{},
                items:{},
            },
            projects: [],
            projects_filt:[],
            type_options:[
                {'title' : trans('custom.documents'), 'id': 'documents'},
                {'title' : trans('custom.files'), 'id': 'files'},
            ],
            urls_tmp:[],
            item_operation_type :'document',
            item_operation_action :'add',
            empty_project : false,
            editing_job_done_at:new Date(),
            selectedType: null,
            submitted: false,
            uploadPercentage: 0,
            uploadPercentage_tech: 0,
            totalFiles: 0,
            is_super_user : typeof window.perosnal_permissions.is_super_user == 'undefined'? false:true,
            uploadProgresses: [],
            adding_file_project_id:null,
            adding_file_company_id:null,
            e57_extension: 'e57',
            size_convertation_limit_bytes: 32212254719,
        }
    },
    components: {
        vueDropzone: vue2Dropzone
    },
    mounted() {

        var app = this;

        axios.get('/api/v1/potree/types')
            .then((resp) => {
                this.files_types = resp.data['files'];
                for (let file_name in resp.data['files']){
                    this.files_types_arr.push({
                        value:file_name,
                        name:trans('custom.file_names.' + file_name)
                    })
                }
                this.doc_types = resp.data['documents'];
                for (let doc_name in resp.data['documents']){
                    this.doc_types_arr.push({
                        value:doc_name,
                        name:trans('custom.file_names.' + doc_name)
                    })
                }
            })
            .catch(function (data) {
                if (data.response.status !== 401) {
                    app.$dialog.alert("Could not load types")
                }
            });

        axios.get('/api/v1/projects/manageable').then(resp => {
            for (let key in resp.data) {
                let new_elem = {
                    'id': key,
                    'title': resp.data[key].title
                };
                app.projects.push(new_elem);
                app.projects_filt.push(new_elem);
            }
        }).catch((error) => {
            if (error.response.status !== 401) {
                app.$dialog.alert(window.trans('custom.error_load_company'));
            }
        });

        //$('body .dz-default.dz-message').unbind('click');
        /*$('body').on('click', '.dz-default.dz-message', function(){
            $(this).parents('.upload_div:first').find('.dz-clickable:first').click();
        });*/

        $('body').on('reset_upload_fle', function(){
            app.resetUpload();
            app.resetFileUpload();
        });


        $('body').on('click', '.reset_upload', function(){
            app.resetUpload();
            app.resetFileUpload();
        });


        $('body').off('open.modal.file.upload');
        $('body').on('open.modal.file.upload', function(e,o){
            console.log(o);
            app.openAddfile('choose', 'all', 'choose');
        });

        $('body').off('open.modal.file.edit');
        $('body').on('open.modal.file.edit', function(e,o){
            if (typeof o != 'undefined' && typeof o.projectId != 'undefined' && typeof o.edit_id != 'undefined'){
                app.openEditFile(o.projectId, o.edit_id, o.type);
            }
        });

    },
    methods: {

        openEditFile(project_id, file_id, type) {
            var app = this;
            $('#editModalFileModal form').css('display', 'block');
            this.adding_file_project_id = project_id;
            this.new_item.project_id = project_id;
            axios.get('/api/v1/projects/' + this.new_item.project_id).then(resp => {
                app.project = resp.data;
                app.changeAddType(type, 'edit', file_id);
            }).catch(resp => {
                if (resp.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                } else {
                    app.$dialog.alert(trans('custom.content_not_allowed'), {
                        'html': true,
                        okText: 'ok'
                    }).then(() => {
                        $('.close').click();
                    });
                }
            });
        },
        is_loading(){
            return window.is_loading;
        },
        update_type_url(val){
            this.new_item.type_url = val;
        },
        deleteItemEntryFile(type) {
            var app = this;
            app.$dialog.confirm(window.trans('custom.delete_confirm'))
                .then(function () {
                    axios.delete('/api/v1/projects/' + app.app.editing_item.project_id + '/item/' + app.editing_item.id + '/file/' + type).then(() => {
                        window.location.reload();
                    }).catch((resp) => {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.delete_error'));
                        }
                    });
                })
                .catch(function () {
                    console.log('Clicked on cancel')
                });

        },
        openAddfile(project_id, company_id, type) {
            console.log(project_id, company_id, type)
            if (project_id == 'choose'){
                this.empty_project = true;
                project_id = null;
            }
            if (type == 'choose'){
                type = 'files';
            }
            this.new_item = {
                project_id: project_id,
                title: '',
                description: '',
                type: '',
                urls: [],
                upload: [],
                job_done_at: new Date()
            },
                $('#uploadModalFileModal form').css('display', 'block');
            this.adding_file_project_id = project_id;
            this.new_item.project_id = project_id;
            this.new_item.adding_file_company_id = company_id;
            this.changeAddType(type, 'add');
            $('uploadModalFileModal form').css('display', 'block');
        },
        template_file: function () {
            return `<div class=\"dz-preview dz-file-preview\">
                   <div class=\"dz-image\"><img data-dz-thumbnail /></div>
                    <div class=\"dz-details\"><div class=\"dz-filename\">`+  trans('custom.filename') +`: <span data-dz-name></span></div>
                    <div class=\"dz-size\">`+  trans('custom.size')  + `: <span data-dz-size></span></div>
                     </div>
                    <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>
                    <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>  <div class=\"dz-success-mark\">
                    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">      <title>Check</title>      <defs></defs>      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>      </g>    </svg>  </div>  <div class=\"dz-error-mark\">    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">      <title>Error</title>      <defs></defs>
                    <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">
                    <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">
                        <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>
                    </g>      </g>    </svg>  </div></div>`;
        },
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
            let curr_item = this.item_operation_action == 'edit'? this.editing_item : this.new_item;
            curr_item.upload[response.index] = response.path + response.name;
            if(this.item_operation_action == 'edit'){
                curr_item.urls[response.index] = response.path + response.name;
            }
            this.totalFiles--;


            if (this.totalFiles === 0) {
                this.saveFileForm()
            }
        },
        updateUserSettingProjectTab($tab) {
            axios.put('/api/v1/usersettings', {
                'settings_key': 'project_'+ this.adding_file_project_id +'_view',
                'settings_value': $tab,
            }).then(function (resp) {
                console.log('View settings updated');
            }).catch(function (resp) {
                console.log('Failed to update View settings');
            });
        },
        saveFileForm() {
            var app = this;
            let formData = new FormData();
            var curr_item = app.item_operation_action == 'edit'? app.editing_item : app.new_item;


            for (let key in curr_item) {
                if (curr_item[key]  && (curr_item[key].constructor === Array || curr_item[key].constructor === Object)) {
                    for (let array_key in curr_item[key]) {
                        if ((curr_item[key][array_key] || key == 'urls') && curr_item[key][array_key] !='') {
                            formData.append(key + '[' + array_key + ']', curr_item[key][array_key]);
                        }
                    }
                } else {
                    if (curr_item[key] != null && curr_item[key] != '') {
                        if (key == 'job_done_at') {
                            if (typeof curr_item.id !== 'undefined' && curr_item.id > 0) {
                                formData.append(key, app.editing_job_done_at.toDateString());
                            } else {
                                formData.append(key, curr_item[key].toDateString());
                            }
                        } else {
                            formData.append(key, curr_item[key]);
                        }
                    }
                }
            }
            if (typeof curr_item.id !== 'undefined' && curr_item.id > 0) {
                formData.append('_method', 'PUT');
                axios.post('/api/v1/projects/' + app.adding_file_project_id + '/item/'+curr_item.id, formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(() => {
                    window.is_loading = false;
                    this.submitted = false;
                    $('body').trigger('load.file.background', {item: curr_item, status: 'finished'});
                    app.$dialog.alert(trans('custom.your_file_has_been_updated_successfully')).then(() => {
                        $('#uploadModalShortBar').modal('hide');
                        $('body').trigger('load.file.success.dialog', {project_id:app.adding_file_project_id});
                    });
                    if (this.item_operation_type == 'document') {
                        this.updateUserSettingProjectTab('documents');
                    } else {
                        this.updateUserSettingProjectTab('files');
                    }

                    //   app.$router.push({path: '/' + app.adding_file_company_id + '/projects/' + app.adding_file_project_id + '/items#files'});
                }).catch(error => {
                    $('#uploadModalShortBar').removeClass('show');
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                    } else {
                        this.resetUpload();
                        if (error.response.data && error.response.data.errors) {
                            app.$dialog.alert(window.parseError(error.response.data.errors), {'html': true});
                        } else if (error.response.data) {
                            app.$dialog.alert(window.parseError(error.response.data), {'html': true});
                        }
                    }
                });
            } else {
                axios.post('/api/v1/projects/' + app.adding_file_project_id + '/item', formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(() => {
                    window.is_loading = false;
                    this.submitted = false;
                    app.$dialog.alert(trans('custom.your_file_has_been_uploaded_successfully')).then(() => {
                        $('#uploadModalShortBar').removeClass('show');
                        $('body').trigger('load.file.success.dialog', {project_id:app.adding_file_project_id});
                    });
                    if (this.item_operation_type == 'document') {
                        this.updateUserSettingProjectTab('documents');
                    } else {
                        this.updateUserSettingProjectTab('files');
                    }

                }).catch(error => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                    } else {
                        this.resetUpload();
                        if (error.response.data && error.response.data.errors) {
                            app.$dialog.alert(window.parseError(error.response.data.errors), {'html': true});
                        } else if (error.response.data) {
                            app.$dialog.alert(window.parseError(error.response.data), {'html': true});
                        }
                    }
                });
            }

        },
        removedFile() {
            $('.modal .dz-message').css('display', 'block');
            $('.file_info_warning').html('').hide();
        },
        resetUpload() {
            $('#uploadModalShortBar').removeClass('show');
            $('.dz-message').css('display', 'block');
            var curr_item = this.item_operation_action == 'edit'? this.editing_item : this.new_item;
            window.is_loading = false;
            this.submitted = false;
            $('body').trigger('load.file.background', {item:curr_item, status:'resetted'});
            this.uploadPercentage = 0;
            this.uploadPercentage_tech = 0;
            if (typeof this.selectedType !== 'undefined' && this.selectedType !== null && this.selectedType.types) {
                let merged_types = this.mergeTypes(this.selectedType.types);
                for (let index in merged_types) {
                    if (typeof this.$refs['dropzone_' + index] != 'undefined' && this.$refs['dropzone_' + index].length > 0) {
                        this.$refs['dropzone_' + index][0].removeAllFiles();
                        this.$refs['dropzone_' + index][0].destroy();
                    }
                }
            }
        },
        uploadErrorHandle(file, error) {
            console.log('testtest23232')
            var app = this;
            var curr_item = app.item_operation_action == 'edit'? app.editing_item : app.new_item;
            window.is_loading = false;
            this.submitted = false;
            $('body').trigger('load.file.background', {item:curr_item, status:'failed'});
            $('#uploadModalShortBar').removeClass('show');
            if (!error.result && typeof error.error_message != 'undefined'){
                $('body').trigger('overlimit.membership',error);
            } else {
                if (error == "Upload canceled."){
                    return false;
                } else {
                    axios.post('/api/v1/alert/', {
                        'trans': 'log_failed_upload_file',
                        'project_id': app.adding_file_project_id,
                        'company_id': app.adding_file_company_id,
                    }).then(() => {
                        app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                            '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                            'html': true,
                            okText: "Ok",
                        });
                    })
                }
            }
            this.resetUpload();
        },
        fileAdded(event){
            console.log('testtest')
            $('.modal .dz-message').css('display', 'none');
            if (typeof event.name != 'undefined' && typeof event.size != 'undefined' &&
                /(?:\.([^.]+))?$/.exec(event.name)[1] == this.e57_extension && event.size > this.size_convertation_limit_bytes){
                $('.file_info_warning').html(trans('custom.attention_if_the_e57_file_is_larger_than_30gb')).slideDown('300');
            } else {
                $('.file_info_warning').html('').hide();
            }
        },
        fileProcessing() {
            this.submitted = true;
            this.totalFiles++
        },
        uploadProgress(file, progress) {
            this.uploadProgresses[file.upload.uuid] = progress;
            this.resetProgress()
        },
        changeAddType(type, action, edit_id = false){
            this.item_operation_type = type;
            this.item_operation_action = action;
            $('#uploadModalFileModal form, #editModalFileModal form').css('display', 'block');
            this.new_item = {
                project_id: this.adding_file_project_id,
                title: '',
                description: '',
                type: '',
                urls: [],
                upload: [],
                job_done_at: new Date(),
            };
            this.selectedType = null;
            if (this.item_operation_action == 'edit' && edit_id > 0) {
                let cur_type = type + 's';
                this.item_operation_type = cur_type;
                for (let item_key in this.project.items[cur_type]) {
                    let elements = this.project.items[cur_type][item_key];
                    for (let el_type in elements) {
                        if (elements[el_type].id == edit_id) {
                            this.editing_item = elements[el_type];
                            this.editing_job_done_at =  new Date(elements[el_type].job_done_at);
                            this.editing_item.upload = [];

                            if (this.item_operation_type == 'files'){
                                this.selectedType = (this.files_types[elements[el_type].type]) ? this.files_types[elements[el_type].type] : null;
                            }
                            if (this.item_operation_type == 'documents'){
                                this.selectedType = (this.doc_types[elements[el_type].type]) ? this.doc_types[elements[el_type].type] : null;
                            }
                        }
                    }

                }
                this.urls_tmp = JSON.stringify(this.editing_item.urls);
            }
        },
        resetProgress() {
            var app = this;
            let min = 101;
            for (let index in app.uploadProgresses) {
                if (app.uploadProgresses[index] < min) {
                    min = app.uploadProgresses[index];
                }
            }

            if (min < 101){
                if(min == 100 && app.uploadPercentage_tech == 100 || min < 100){
                    this.uploadPercentage = min.toFixed(0);
                }
                this.uploadPercentage_tech = min;
                let curr_item = app.item_operation_action == 'edit'? app.editing_item : app.new_item;
                window.is_loading = true;
                $('body').trigger('load.file.background', {item:curr_item, percentage:app.uploadPercentage, type:app.item_operation_type, status:'loading'});
            }
        },
        submitFileForm() {
            event.preventDefault();

            if (this.submitted){
                this.$dialog.alert("Could not load types")
            }

            let is_valid = true;
            $('.form-group.required.error-element').removeClass('error-element');
            $('.required-to-fill').remove();
            let modal_id = this.item_operation_action == 'edit'? 'editModalFileModal' : 'uploadModalFileModal';
            $.each($('#'+modal_id+' .form-group.required'), function (key, wrapper) {
                if ($(wrapper).find('input').length > 0) {
                    if ($(wrapper).find('input').val() == '' && $(wrapper).find('.selected-tag').length == 0) {
                        is_valid = false;
                        var message = '';

                        if ($(wrapper).find('.vs__selected-options').length > 0) {
                            message = 'custom.required_select_the_value';
                        } else {
                            message = 'custom.required_enter_the_value';
                        }

                        $(wrapper).addClass('error-element').append("<div class='required-to-fill'><i class='warn_icon'></i>" + trans(message) + "</div>");

                    }


                }
            });
            if (!is_valid){
                return false;
            }
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
                        this.saveFileForm();
                    }
                }
                $('#uploadModalFileModal').modal('hide');
                $('#uploadModalShortBar').addClass('show');
            }

        },
        typeChange(e) {

            this.new_item.urls = [];
            if (this.item_operation_type == 'files'){
                this.selectedType = (this.files_types[e]) ? this.files_types[e] : null;
            }
            if (this.item_operation_type == 'documents'){
                this.selectedType = (this.doc_types[e]) ? this.doc_types[e] : null;
            }

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
                url: '/api/v1/upload-chunk?index=' + index + '&project_id=' + this.adding_file_project_id,
                paramName: 'file',
                headers: {
                    "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                },
                acceptedFiles: types,
                dictDefaultMessage: trans('custom.drop_upload_text'),
                chunking: true,
                previewTemplate: this.template_file(),
                createImageThumbnails: false,
                maxFiles: 1,
                autoProcessQueue: false,
                addRemoveLinks: true,
                forceChunking: true,
                chunkSize: 5120000, // 5mb
                timeout: 3600000, // 1 hour
                maxFilesize: null
            }
        },
        dropzoneTempSpaceOptions() {
            return {
                url: '/api/v1/upload-chunk?temp_storage=1',
                paramName: 'file',
                headers: {
                    "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                },
                dictDefaultMessage: trans('custom.drop_upload_text'),
                chunking: true,
                previewTemplate: this.template_file(),
                createImageThumbnails: false,
                maxFiles: 1,
                autoProcessQueue: false,
                addRemoveLinks: true,
                forceChunking: true,
                chunkSize: 5120000, // 5mb
                timeout: 3600000, // 1 hour
                maxFilesize: null
            }
        },
        fileUploadProcessing() {
            this.submitted = true;
            this.totalFiles++;
        },
        uploadFileProgress(file, progress) {
            this.uploadProgresses[file.upload.uuid] = progress;
            this.resetFileProgress()
        },
        resetFileProgress() {
            var app = this;
            let min = 101;
            for (let index in app.uploadProgresses) {
                if (app.uploadProgresses[index] < min) {
                    min = app.uploadProgresses[index];
                }
            }

            if (min < 101){
                if(min == 100 && app.uploadPercentage_tech == 100 || min < 100){
                    this.uploadPercentage = min.toFixed(0);
                }
                this.uploadPercentage_tech = min;
                window.is_loading = true;
                $('body').trigger('load.file.background', {item:app.new_file, percentage:app.uploadPercentage, type:'uploading_temp_storage', status:'loading'});
            }
        },
        showFileSuccess(file) {
            this.totalFiles--;

            if (this.totalFiles === 0) {
                this.savingSuccess();
            }
        },
        removedFileFile() {
            $('.modal .dz-message').css('display', 'block');
            $('.file_info_warning').html('').hide();
        },
        resetFileUpload() {
            $('#uploadTempModalShortBar').removeClass('show');
            $('.dz-message').css('display', 'block');
            window.is_loading = false;
            this.submitted = false;

            $('body').trigger('load.file.background', {item:this.new_file, status:'resetted'});
            this.uploadPercentage = 0;
            this.uploadPercentage_tech = 0;
            this.$refs['dropzone_temp_storage'].removeAllFiles();
            this.$refs['dropzone_temp_storage'].destroy();
        },
        savingSuccess(){
            var app = this;
            window.is_loading = false;
            this.submitted = false;
            $('body').trigger('load.file.background', {item: app.new_file, status: 'finished'});
            app.$dialog.alert(trans('custom.your_file_has_been_updated_successfully')).then(() => {
                $('#uploadTempModalShortBar').modal('hide').removeClass('show');
                $('body').trigger('load.file.success.dialog');
            });
        },
        uploadFileErrorHandle(file, error) {
            var app = this;
            window.is_loading = false;
            this.submitted = false;
            $('body').trigger('load.file.background', {item:app.new_file, status:'failed'});
            $('#uploadTempModalShortBar').removeClass('show');
            if (!error.result && typeof error.error_message != 'undefined'){
                $('body').trigger('overlimit.membership',error);
            } else {
                if (error == "Upload canceled."){
                    return false;
                } else {
                    axios.post('/api/v1/alert/', {
                        'trans': 'log_failed_upload_file',
                        'company_id': app.adding_file_company_id,
                    }).then(() => {
                        app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                            '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                            'html': true,
                            okText: "Ok",
                        });
                    })
                }
            }
            this.resetFileUpload();
        },
        fileUploadAdded(event){
            this.new_file.title = event.name;
            $('.modal .dz-message').css('display', 'none');
        },
        submitUploadForm(){
            event.preventDefault();

            if (this.submitted){
                this.$dialog.alert("Could not load types")
            }

            if (!this.submitted) {
                this.$refs['dropzone_temp_storage'].processQueue();

                if (this.totalFiles < 1) {
                    this.savingSuccess();
                }
                $('#uploadModalTempStorageModal').modal('hide');
                $('#uploadTempModalShortBar').addClass('show');
            }
        }
    }
}
</script>
