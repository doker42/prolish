
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
                                    <trumbowyg v-model="$data['notification']['content_' + code]" :config="editorConfig" class="form-control" :name="'content' + code"></trumbowyg>
                                </div>
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
    import Trumbowyg from 'vue-trumbowyg';
    import 'trumbowyg/dist/ui/trumbowyg.css';
    import 'jquery-resizable-dom';
    import 'trumbowyg/dist/plugins/resizimg/trumbowyg.resizimg.min.js';
    import 'trumbowyg/dist/plugins/upload/trumbowyg.upload.min.js';
    import 'trumbowyg/dist/plugins/noembed/trumbowyg.noembed.min.js';

    export default {
        components: {
            Trumbowyg
        },
        data() {
            return {
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
                languages: window.availableLanguages,
                editorConfig: {
                    imageWidthModalEdit: true,
                    autogrow: true,
                    btnsDef: {
                        upload: {
                            ico: 'insertImage'
                        }
                    },
                    btns: [
                        ['viewHTML'],
                        ['formatting'],
                        ['strong', 'em', 'del'],
                        ['superscript', 'subscript'],
                        ['link'],
                        ['upload'],
                        ['noembed'],
                        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ['unorderedList', 'orderedList'],
                        ['horizontalRule'],
                        ['removeformat']
                    ],
                    plugins: {
                        upload: {
                            serverPath: '/api/v1/upload',
                            fileFieldName: 'image',
                            data: [{
                                name: 'binary', value: true
                            }],
                            headers: {
                                "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                            },
                            urlPropertyName: 'image'
                        },
                        resizimg: {
                            minSize: 64,
                            step: 16,
                        }
                    }
                }
            }
        },
        methods: {
            saveForm() {
                event.preventDefault();
                var app = this;
                axios.post('/api/v1/notifications', this.notification).then(() => {
                    this.$router.push({path: '/notifications'});
                }).catch(error => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not create notification:'), {'html': true});
                    }
                });
            },
        }
    }
</script>
