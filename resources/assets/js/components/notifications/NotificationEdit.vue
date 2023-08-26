
<template>
    <div>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <router-link :to="{name: 'notificationIndex'}" class="btn back_btn"> {{ trans('custom.back') }}</router-link>
                </div>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-body">
                <div class="container">
                    <form v-on:submit="saveForm()">
                        <div v-for="language, code in languages">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ language }} {{ trans('custom.title') }}</label>
                                    <input name="fullname" type="text" v-model="$data['notification']['title_' + code]" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ language }} {{ trans('custom.content') }}</label>
                                    <editor
                                        v-model="$data['notification']['content_' + code]"
                                        class="form-control"
                                        :name="'content' + code"
                                        :plugins="editorPlugins"
                                        :toolbar="editorToolbar"
                                        :init="editorConfig"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group text-right">
                                <button class="btn btn-success">{{ trans('custom.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Editor from '@tinymce/tinymce-vue'

    export default {
        components: {
            'editor': Editor
        },
        data() {
            return {
                editorPlugins: 'link image code preview imagetools table lists textcolor hr wordcount media',
                editorToolbar: 'undo redo | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | hr bullist numlist outdent indent | media link image table | code preview',
                editorConfig: {
                    height: 500,
                    menubar: false,
                    convert_urls : false,
                    mediaembed_max_width: 450,
                    relative_urls : false,
                    init_instance_callback : function(editor) {
                        var freeTiny = document.querySelector('.tox .tox-notification--in');
                        freeTiny.style.display = 'none';
                    },
                    images_upload_handler: function (blobInfo, success, failure, folderName) {
                        let xhr, formData;
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;

                        xhr.open('POST', '/api/v1/upload?binary=true');
                        let token = document.head.querySelector("[name=csrf-token]").content;
                        xhr.setRequestHeader("X-CSRF-Token", token);

                        xhr.onload = function() {
                            let json;

                            if (xhr.status != 200) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }
                            json = JSON.parse(xhr.responseText);

                            if (!json || typeof json.image != 'string') {
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            success(json.image);
                        };

                        formData = new FormData();
                        formData.append('image', blobInfo.blob(), blobInfo.filename());

                        xhr.send(formData);

                    }
                },
                notification: {
                    title_en: '',
                    title_lv: '',
                    title_ru: '',
                    title_et: '',
                    content_en: '',
                    content_lv: '',
                    content_ru: '',
                    content_et: ''
                },
                notificationId: null,
                languages: window.availableLanguages
            }
        },
        mounted() {
            this.notificationId = this.$route.params.id;

            axios.get('/api/v1/notifications/' + this.notificationId).then((response) => {
                this.notification = response.data;
            });
        },
        methods: {
            saveForm() {
                event.preventDefault();
                var app = this;
                axios.put('/api/v1/notifications/' + this.notificationId, this.notification).then(() => {
                    this.$router.push({path: '/notifications'});
                }).catch(error => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not update notification:'), {'html': true});
                    }
                });
            },
        }
    }
</script>
