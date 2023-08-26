
<template>
    <div class="project_items 123123123">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                    <a href="#" v-on:click.prevent="back_to_previous()" class="btn back_btn">{{ trans('custom.back') }}</a>
                </div>
            </div>
        </div>

        <div v-if="!project" class="text-center">{{ trans('custom.no_project_found') }}</div>
        <div v-if="project !== null">
            <div class="card my-3">
                <div class="card-body">
                    <div class="row">
                    <div class="text-md-left text-center card-img-wrapper">
                        <a class="" href="#" data-toggle="modal" data-target="#projectImageModal">
                            <img :src="project.image" class="img-fluid img-thumbnail" />
                            <i class="fa fa-search item_search_icon"></i>
                        </a>
                    </div>
                    <div class="row project_card_content" >
                        <div class="col-sm-12 col-md-8 text-md-left text-center pl-5">
                        <span class="h3 text-dark">{{ project.title }}</span>

                            <p class="mt-3 mb-1"><div class="address_icon cont-icon"></div>{{ project.address }}</p>

                            <p class="mt-3 mb-1">{{ project.description }}</p>
                        </div>

                        <div class="col-sm-12 col-md-2 text-md-left text-center">
                            <img :title="project.company.title" :src="project.company.logo"
                                 style="max-height: 50px; max-width:150px; vertical-align: text-bottom;"/>
                            <div class="storage_info mt-3">
                                <div class="storage_icon"></div>
                                {{project.size_gb}} GB
                            </div>

                        </div>

                        <div class="col-sm-12 col-md-2 text-md-right text-center">
                            <div class="project_actions_holder">
                                <a v-if="project.permissions.includes('project_manage')" v-on:click="changeAddType('file', 'add')"  href="#" class="font-weight-bold text-white btn btn-success" data-toggle="modal" data-target="#addFileModal"><i class="add_file_icon"> </i> {{ trans('custom.add_file') }}</a>
                                <a v-if="project.permissions.includes('project_manage')" v-on:click="changeAddType('document', 'add')"   href="#" class="font-weight-bold btn btn-outline-success" data-toggle="modal" data-target="#addFileModal"><i class="add_doc_icon"> </i> {{ trans('custom.add_document_short') }}</a>
                                <router-link :to="{name: 'editProjectVisibility', params: {id: project.id, company_id: project.company_id}}" v-if="project.permissions.includes('project_manage')" class="btn btn-xs btn-default btn-outline-success">
                                    <i class="add_user_icon"></i>
                                    <template v-if="project_visibility[project.id] != undefined">
                                        <span class="user_counter"> {{ project_visibility[project.id] }}</span>
                                    </template>
                                    {{ trans('custom.share') }}
                                </router-link>

                                <div class="dropdown" v-if="project.personal_visibility!= undefined || project.permissions.includes('project_manage') || project.permissions.includes('project_delete')">
                                    <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                        {{ trans('custom.actions') }}
                                    </button>
                                    <div class="dropdown-menu">
                                        <a v-on:click.prevent="deleteVisibility(project.id, index)" href="#" v-if="project.personal_visibility!= undefined"><i class="leave_icon"></i>{{ trans('custom.leave') }}</a>
                                        <a v-if="project.permissions.includes('project_transfer')"
                                           v-on:click.prevent="openTransferProject()" href="#" class=""
                                           data-toggle="modal" data-target="#transferProjectModal"><i class="transfer_icon"></i>{{
                                            trans('custom.transfer') }}</a>
                                        <a v-if="project.permissions.includes('project_manage')" v-on:click.prevent="openEditProject(project.id)" href="#" class="" data-toggle="modal" data-target="#editProjectModal"><i class="edit_icon"></i>{{ trans('custom.edit') }}</a>
                                        <a v-on:click.prevent="openDeleteMenu(project.id, index)" v-if="project.permissions.includes('project_delete')" href="#" data-toggle="modal" data-target="#deleteModal"><i class="delete_icon"></i>
                                            {{ trans('custom.delete') }}
                                        </a>
                                        <a v-if="project.permissions.includes('project_manage')" href="#" class="" data-toggle="modal" data-target="#addContactModal"><i class="add_to_team_icon"></i> {{ trans('custom.add_contact') }}</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    </div>
                    <div class="row files_cont_row">
                    <div class="col-sm-12 col-md-2 text-md-left text-center">
                    </div>

                    <div class="col-sm-12 col-md-12  text-md-right text-center files_cont">
                        <router-link :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}">
                            <h3 class="d-inline align-text-top" v-for="type, files in project.summary">
                                <i :class="'fa text-dark '+ files"></i> <span>{{ type.length }}</span>
                            </h3>

                        </router-link>

                        <router-link :to="{name: 'projectItems', params: {id: project.id, company_id: companyId, galleryTab: 1}}">
                            <h3 class="d-inline align-text-top" v-if="project.gallery_items > 0">
                                <i :class="'fa text-dark fa-image'"></i> <span>{{ project.gallery_items }}</span>
                            </h3>
                        </router-link>
                    </div>
                </div>


                </div>


            </div>

            <tabs :options="{ useUrlFragment: false }" ref="itemsTabs" @clicked="tabClicked" @changed="tabChanged">
                <!-- Files tab -->
                <tab :name="trans('custom.files')" id="files" v-if="project.permissions.includes('project_files_view')">
                    <table class="table table-striped table-responsive-md" v-if="project.items">
                        <thead>
                            <tr>
                                <th width="5%"></th>
                                <th width="18%"></th>
                                <th width="66%"></th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="group, group_title, group_index in project.items['files']">
                                <tr class="text-left h3 bg-secondary font-weight-bold">
                                    <td colspan="5">{{ trans('custom.file_names.' + group_title) }}</td>
                                </tr>
                                <tr v-for="item, index in group" :class="checkItemFailed(item.status) ? 'text-danger item_'+index : 'item_'+index">
                                    <td class="pt-4 pl-2">
                                        <a v-if="item.view_url !== null" :href="item.view_url" target="_blank">
                                            <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                        </a>

                                        <a v-else-if="item.url.length > 0 && item.url[0].type == 'youtube'" v-on:click.prevent="openVideo(item.url[0].embed, item.title)" href="#" data-toggle="modal" data-target="#videoModal">
                                            <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                        </a>
                                        <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"  :href="item.url[0].url">
                                            <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                        </a>

                                        <i v-else :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                                    </td>
                                    <td>
                                        <div class="name_holder">
                                                <span class="name">{{ item.title }}</span>
                                                <span class="date">  {{ item.job_done_at }}</span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        {{ item.description }}
                                    </td>
                                    <td class="align-middle h4 text-center">
                                        <div class="dropdown d-inline-block">
                                            <button class="btn dropdown-toggle" v-bind:class="dropDownButtonClass(item.status)" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{ trans('custom.actions') }}
                                            </button>
                                            <div class="dropdown-menu">
                                                <template v-if="item.view_url === null && item.url === null">
                                                    <a class="dropdown-item disabled" href="#">
                                                        <i class="fa fa-search text-warning"></i> {{ trans('custom.building') }}
                                                    </a>
                                                </template>
                                                <template v-else-if="checkItemFailed(item.status)">
                                                    <a class="dropdown-item disabled" href="#">
                                                        <i class="fa fa-search text-warning"></i> {{ trans(item.status) }}
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    <a v-if="item.view_url !== null" :href="item.view_url" target="_blank" class="dropdown-item text-capitalize">
                                                        <i class="fa fa-search text-warning"></i> {{ trans('custom.preview') }}
                                                    </a>
                                                </template>
                                                <a v-if="issetShareUrls(item.url)" v-on:click.prevent="shareProcess(item.url)" download class="dropdown-item cursor-pointer">
                                                    <i class="fa fa-share text-primary"></i> {{ trans('custom.share') }}
                                                </a>

                                                <a v-if="item.external_view && (allowedDisplay('project_delete', 'project', projectId) || allowedDisplay('all'))"
                                                   href="#"
                                                   v-on:click.prevent="copyExternalUrl(framedView(item.external_view), true)"
                                                   download class="dropdown-item">
                                                    <i class="fa fa-ext-share-frame text-primary"></i> {{
                                                        trans('custom.frame') }}
                                                </a>

                                                <a v-if="item.url.length > 0 && item.url[0].type == 'youtube'" v-on:click.prevent="openVideo(item.url[0].embed, item.title)"  href="#" data-toggle="modal" data-target="#videoModal" class="dropdown-item">
                                                    <i class="fa fa-play text-danger"></i> {{ trans('custom.watch') }}
                                                </a>

                                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"  :href="item.url[0].url" target="_blank" class="dropdown-item">
                                                    <i class="fa fa-play text-danger"></i> {{ trans('custom.watch') }}
                                                </a>

                                                <a v-for="url in item.url" v-if="url.type != 'view_url' && url.type != 'youtube'" :href="url.url" target="_blank" download class="dropdown-item">
                                                    <i class="fa fa-download text-primary"></i> {{ trans('custom.download') }} {{url.type}}
                                                </a>
                                                <template v-if="project.permissions.includes('project_files_manage')">

                                                    <template v-if="item.status.split('||')[0] === 'custom.item_status_cc_converting'">
                                                        <div class="dropdown-item text-center">
                                                        <span v-tooltip="getConvertedTooltipText(item.status)" class="d-inline-block align-middle">
                                                            <tile></tile>
                                                        </span>
                                                        </div>
                                                    </template>
                                                    <template v-else>

                                                        <a v-on:click.prevent="openConvert(item.id, item.can_convert, index)" v-if="item.can_convert.length > 0" href="#" data-toggle="modal" data-target="#convertModal" class="dropdown-item">
                                                            <i class="fa fa-random" style="color: #8e44ad;"></i> {{ trans('custom.convert') }}
                                                        </a>

                                                    </template>

                                                   <!-- <router-link :to="{name: 'editProjectItem', params: {project_id: project.id, id: item.id, type: 'files', company_id: companyId}}" class="dropdown-item">
                                                        <i class="fa fa-edit text-success"></i> {{ trans('custom.edit') }}
                                                    </router-link> -->
                                                    <a v-if="project.permissions.includes('project_manage')" v-on:click="changeAddType('file', 'edit', item.id)"   href="#" class="dropdown-item" data-toggle="modal" data-target="#editFileModal"><i class="fa fa-edit text-success"></i> {{ trans('custom.edit') }}</a>


                                                    <template v-if="item.size">
                                                        <div class="dropdown-item">
                                                            <i class="fa fa-hdd"></i> {{ item.size }}mb
                                                        </div>


                                                    </template>

                                                    <a v-on:click.prevent="deleteEntryItem(item.id, index, 'files', group_title)" href="#" class="dropdown-item">
                                                        <i class="fa fa-trash-o bucket_icon"></i> {{ trans('custom.delete') }}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </tab>

                <!-- Gallery tab -->
                <tab :name="trans('custom.gallery')" id="gallery" v-if="project.permissions.includes('project_files_view')">

                    <span v-if="!gallery_folder" v-on:click.stop="galleryDownloadGallery(projectId)"  v-tooltip="'Download *zip'" class="download-folder top-link">
                                   {{trans('custom.gallery_download')}}
                    </span>

                    <span v-if="gallery_folder" v-on:click.stop="galleryDownloadFolder(gallery_folder)"  v-tooltip="'Download *zip'" class="download-folder top-link">
                                   {{trans('custom.gallery_folder_download')}}
                     </span>

                    <template v-if="project.permissions.includes('project_manage')">
                        <vue-dropzone id="dropzone" v-on:vdropzone-success="galleryUpload" :options="dropzoneOptions"></vue-dropzone>
                    </template>
                    <div style="margin: 15px -5px 0px">
                        <viewer :options="{zoomRatio: 0.2}" :images="gallery_images['urls']">
                            <div v-if="!gallery_folder" class="gallery_image border-primary btn-outline-primary rounded" data-toggle="modal" data-target="#newFolderModal">
                                <div class="text-center absolute-center"><span class="blue_plus">+</span>
                                    {{ trans('custom.add_folder') }}
                                </div>
                            </div>

                            <div class="gallery_image border-0 gallery-folder" v-if="gallery_folder" @click="changeGalleryFolder('')">
                                <div class="absolute-center pt-4 absolute-center font-weight-bold">
                                    ...
                                </div>
                            </div>

                            <div class="gallery_image border-0 gallery-folder" v-for="(folder, folderIndex) in gallery_images['folders']" @click="changeGalleryFolder(folder.id)">
                                <div class="absolute-center pt-4 absolute-center font-weight-bold">
                                    {{ folder.title }}
                                </div>
                                <span v-on:click.stop="galleryDownloadFolder(folder.id)"  v-tooltip="'Download *zip'"  class="download-folder">
                                    <i class="fa fa-download download_icon"></i>
                                </span>
                                <span v-on:click.stop="galleryDeleteFolder(folder.id, folderIndex)" style="top: 25%;" v-tooltip="'Delete'" v-if="project.permissions.includes('project_manage')">
                                    <i class="fa fa-trash-o bucket_icon"></i>
                                </span>
                            </div>

                            <div class="gallery_image" v-for="(src, imageIndex) in gallery_images['urls']">
                                <img :src="src" :key="src">
                                <span v-on:click.stop="galleryDelete(imageIndex)" v-tooltip="'Delete'" v-if="project.permissions.includes('project_manage')">
                                    <i class="fa fa-trash-o bucket_icon"></i>
                                </span>
                                <a  v-tooltip="'Download image'"  :href="src"  download  class="download-image">
                                   <i class="fa fa-download download_icon"></i>
                                </a>
                            </div>
                        </viewer>
                    </div>

                </tab>

                <!-- Documents tab -->
                <tab :name="trans('custom.documents')" id="documents"  v-if="project.permissions.includes('project_documents_view')">
                    <table class="table table-striped table-responsive-md" v-if="project.items">

                        <tbody>
                        <template v-for="group, group_title in project.items['documents']">
                            <tr class="text-left h3 bg-secondary font-weight-bold">
                                <td colspan="5">{{ trans('custom.file_names.' + group_title) }}</td>
                            </tr>
                            <tr v-for="item, index in group" :class="'item_'+index">
                                <td class="pt-4 p-2" v-if="item.url.length > 0">
                                    <a v-if="item.view_url !== null" :href="item.view_url" target="_blank">
                                        <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                    </a>

                                    <a v-else-if="item.url.length > 0 && item.url[0].type == 'youtube'" v-on:click.prevent="openVideo(item.url[0].embed, item.title)" href="#" data-toggle="modal" data-target="#videoModal">
                                        <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                    </a>
                                    <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"  :href="item.url[0].url">
                                        <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                    </a>

                                    <i v-else :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                                </td>
                                <td class="pt-4 p-2" v-else>
                                   <i :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                                </td>
                                <td>
                                    <h4 class="text-dark font-weight-bold">{{ trans('custom.file_names.' + item.type) }}</h4>
                                    <span>{{ item.title }}</span>
                                </td>
                                <td class="align-middle">
                                    {{ item.description }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ item.job_done_at }}
                                </td>
                                <td class="align-middle h4 text-right">
                                    <template v-if="item.status == 'custom.item_status_success'">
                                        <span v-if="item.view_url === null && item.url === null">({{ trans('custom.building') }})</span>

                                        <div v-if="item.view_url !== null || item.url !== null">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-outline-success dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    {{ trans('custom.actions') }}
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a v-if="item.view_url !== null && item.url.length > 0" :href="item.view_url" target="_blank" class="dropdown-item">
                                                        <i class="fa fa-search text-warning"></i> {{ trans('custom.preview') }}
                                                    </a>

                                                    <a v-else-if="item.url.length > 0 && item.url[0].type == 'youtube'" v-on:click.prevent="openVideo(item.url[0].embed, item.title)" href="#" data-toggle="modal" data-target="#videoModal">
                                                        <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                                    </a>

                                                    <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"  :href="item.url[0].url">
                                                        <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                                    </a>

                                                    <a v-for="url in item.url" v-if="url.type != 'view_url'" :href="url.url" target="_blank" download class="dropdown-item">
                                                        <i class="fa fa-download text-primary"></i> {{ trans('custom.download') }} {{ url.type }}
                                                    </a>


                                                    <template v-if="project.permissions.includes('project_files_manage')">

                                                        <!--<router-link :to="{name: 'editProjectItem', params: {project_id: project.id, id: item.id, type: 'documents', company_id: companyId}}" class="dropdown-item">
                                                            <i class="fa fa-edit text-success"></i> {{ trans('custom.edit') }}
                                                        </router-link> -->
                                                        <a v-if="project.permissions.includes('project_manage')" v-on:click="changeAddType('document', 'edit', item.id,)"   href="#" class="dropdown-item" data-toggle="modal" data-target="#editFileModal"><i class="fa fa-edit text-success"></i> {{ trans('custom.edit') }}</a>


                                                        <a v-on:click.prevent="deleteEntryItem(item.id, index, 'documents', group_title)" href="#" class="dropdown-item">
                                                            <i class="fa fa-trash-o"></i> {{ trans('custom.delete') }}
                                                        </a>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <strong>{{ trans(item.status) }}</strong>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </tab>

                <!-- Contacts tab -->
                <tab :name="trans('custom.contacts')" id="team" v-if="project.permissions.includes('project_manage')">
                    <div class="row pull-right">
                        <a v-if="project.permissions.includes('project_manage')" href="#" class="btn btn-xs btn-default btn-outline-success mr-3 mt-2 mb-4" data-toggle="modal" data-target="#addContactModal"><i class="add_to_team_iconbtn"></i> {{ trans('custom.add_contact') }}</a>
                    </div>
                    <table class="table table-striped table-responsive-md" v-if="project.contacts">
                        <tbody>
                            <template v-for="contact, index in project.contacts">
                                <tr>
                                    <td class="align-middle name_cell">
                                        <img class="avatar" :src="contact.picture"  />
                                        <div class="name_holder">
                                            <template v-if="contact.job_title !=null">
                                                <span class="name">{{ contact.fullname }}</span>
                                                <span v-if="contact.position !=null" class="job_title">{{ contact.position }}</span>
                                            </template>
                                            <template v-else>
                                                <span class="name alone">{{ contact.fullname }}</span>
                                            </template>
                                        </div>

                                    </td>
                                    <td class="align-middle"><a :href="'mailto:' + contact.email">{{ contact.email }}</a></td>
                                    <td class="align-middle"><a :href="'tel:' + contact.phone">{{ contact.phone }}</a></td>
                                    <td class="align-middle h4">
                                        <router-link :to="{name: 'editProjectContact', params: {project_id: project.id, id: contact.id, company_id: companyId}}" v-tooltip="'Edit'" class="btn_edit_user">

                                        </router-link>

                                        <a v-on:click.prevent="deleteEntryContact(contact.id, index)" class="btn_delete_user" href="#" v-tooltip="'Delete'">

                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </tab>

                <!-- Logs tab -->
                <tab :name="trans('custom.logs')" id="logs" v-if="project.permissions.includes('project_view_logs')">
                    <ul class="list-group">
                        <li class="list-group-item" style="overflow-wrap: break-word;" v-for="log in logs" v-html="log.text">
                            {{ log.text }}
                        </li>
                    </ul>
                </tab>

                <!-- Recycle bin -->
                <tab :name="trans('custom.recycle_bin')" id="recyclebin" v-if="project.permissions.includes('project_restore_deleted')">
                    <table class="table table-striped table-responsive-md">
                        <tbody>
                            <template v-if="project.deleted">
                                <tr v-for="item in project.deleted">
                                    <td class="align-middle"><i :class="'fa ' + item.icon" style="font-size: 2rem;"></i></td>
                                    <td>
                                        <h4>{{ item.type }}</h4>
                                        <span>{{ item.title }}</span>
                                    </td>
                                    <td class="align-middle">
                                        {{ item.description }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ item.deleted_at }}
                                    </td>
                                    <td class="align-middle h4 text-center">
                                        <a v-on:click.prevent="restoreItem(item.id)" href="#" v-tooltip="'Restore'" class="mr-2 align-middle btn_restore">
                                        </a>

                                        <a v-on:click.prevent="forceDeleteItem(item.id, index)" class="btn_delete_user" href="#" v-tooltip="'Force Delete'" v-if="project.permissions.includes('project_documents_manage')">

                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </tab>
            </tabs>
        </div>
        <!-- Add Contact -->
        <div class="modal fade styled_modal" id="addContactModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.add_contact') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="saveContactForm()">
                            <input type="hidden" name="project_id" v-model="project.id">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.choose') }} {{ trans('custom.email') }}</label>
                                    <v-select :options="emails" label="email" v-on:change="selectContactEmail">
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
                                <div class="col-sm-12 form-group text-left">
                                    <button class="btn btn-primary">{{ trans('custom.add') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add File Modal -->
        <div class="modal fade styled_modal" id="addFileModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="this.item_operation_type=='file'" class="modal-title">{{ trans('custom.add_file') }}</h5>
                        <h5 v-if="this.item_operation_type=='document'" class="modal-title">{{ trans('custom.add_document') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitFileForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.title') }}</label>
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
                                    <label class="control-label">{{ trans('custom.job_done_at') }}</label>
                                    <v-date-picker v-model="new_item.job_done_at"></v-date-picker>
                                </div>
                                <div v-if="item_operation_type=='file'" class="col-sm-6 form-group">
                                    <label class="control-label">{{ trans('custom.type') }}</label>
                                    <v-select
                                            v-model="new_item.type"
                                            :options="files_types_arr"
                                            label="name"
                                            index='value'
                                            @input="typeChange"
                                            :clearable="false"
                                    ></v-select>
                                </div>
                                <div v-if="item_operation_type=='document'" class="col-sm-6 form-group">
                                    <label class="control-label">{{ trans('custom.type') }}</label>
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
                                                    <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                                  v-on:vdropzone-processing="fileProcessing"
                                                                  v-on:vdropzone-success="showSuccess"
                                                                  v-on:vdropzone-file-added="fileAdded"
                                                                  :include-styling="false"
                                                                  v-on:vdropzone-error="uploadErrorHandle"
                                                                  :ref="'dropzone_' + type_index" :id="'dropzoneId_' + type.join('_')"
                                                                  :options="filedropzoneOptions(type.join(','), type_index)"></vue-dropzone>
                                                    <div class="dz-default dz-message"><span>{{trans('custom.drop_upload_text')}}</span></div>
                                                </tab>

                                                <tab name="Google Drive">
                                                    <label class="control-label">{{ trans('custom.google_drive_url') }}</label>
                                                    <input type="url" v-model="new_item.urls[type]" class="form-control" placeholder="https://drive.google.com/file/d/XoChevujOfacRe3lcrav/view" />
                                                </tab>
                                            </tabs>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group text-left">
                                    <div v-show="submitted" class="progress">
                                        <div class="progress-bar"
                                             role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0"
                                             aria-valuemax="100" :style="'width:' + uploadPercentage + '%'">{{uploadPercentage}}%</div>
                                    </div>

                                    <button class="btn btn-primary" v-if="!submitted">
                                        <span>{{ trans('custom.confirm') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade styled_modal" id="editFileModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="this.item_operation_type=='file'" class="modal-title">{{ trans('custom.edit_file') }}</h5>
                        <h5 v-if="this.item_operation_type=='document'" class="modal-title">{{ trans('custom.edit_document') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitFileForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.title') }}</label>
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
                                <div class="col-sm-6 form-group date_picker_styled">
                                    <label class="control-label">{{ trans('custom.job_done_at') }}</label>
                                    <v-date-picker v-model="editing_job_done_at"></v-date-picker>
                                </div>

                                    <div class="col-sm-6 form-group">
                                        <label class="control-label">{{ trans('custom.type') }}</label><br/>
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
                                                            <vue-dropzone v-on:vdropzone-upload-progress="uploadProgress"
                                                                          v-on:vdropzone-processing="fileProcessing"
                                                                          v-on:vdropzone-file-added="fileAdded"
                                                                          v-on:vdropzone-success="showSuccess"
                                                                          v-on:vdropzone-error="uploadErrorHandle"
                                                                          :ref="'dropzone_edit_' + type_index"
                                                                          :include-styling="false"
                                                                          :id="'dropzoneId_edit_' + type"
                                                                          :options="filedropzoneOptions(type.join(','), type_index)"></vue-dropzone>
                                                            <div class="dz-default dz-message"><span>{{trans('custom.drop_upload_text')}}</span></div>
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
                                <div class="col-sm-12 form-group text-left">
                                    <div v-show="submitted" class="progress">
                                        <div class="progress-bar"
                                             role="progressbar" :aria-valuenow="uploadPercentage" aria-valuemin="0"
                                             aria-valuemax="100" :style="'width:' + uploadPercentage + '%'">{{uploadPercentage}}%</div>
                                    </div>


                                    <button class="btn btn-primary" v-if="!submitted">
                                        <span>{{ trans('custom.confirm') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer Project Modal -->
        <div class="modal fade styled_modal" id="transferProjectModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-top modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.transfer') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form v-on:submit="transferProjectForm()">
                            <div class="row" v-if="companies_transfer.length > 0">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.company') }}</label>
                                    <v-select
                                            v-model="transfer_company_id"
                                            :options="companies_transfer"
                                            label="title"
                                            index="id"
                                            :clearable="false"
                                    ></v-select>
                                    <div class="input-group-append search_on_select">
                                        <span class="input-group-text"><i class="search_icon"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="radio_block">
                                    <span>{{trans('custom.transfer_rights')}}</span>
                                    <input type="radio" v-model="transfer_type" v-bind:value="change_own_type" checked>
                                </div>
                                <div class="radio_block">
                                    <span>{{trans('custom.send_a_copy')}}</span>
                                    <input type="radio" v-model="transfer_type" v-bind:value="copy_type">
                                </div>

                            </div>
                            <div class="row">
                                <span class="transfer_descr">
                                    <template v-if="transfer_type == 'change_own'">{{ trans('custom.transfer_project_without_return_text') }}</template>
                                    <template v-if="transfer_type == 'copy'">{{ trans('custom.transfer_project_copy_text') }}</template>
                                </span>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 form-group text-left mt-2">
                                    <button class="btn btn-primary">{{ trans('custom.confirm') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Project Modal -->
        <div class="modal fade styled_modal" id="editProjectModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="project.id == null" class="modal-title">{{ trans('custom.create_new') }}</h5>
                        <h5 v-else class="modal-title">{{ trans('custom.edit_project') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form v-on:submit="saveProjectForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.title') }}*</label>
                                    <input v-validate="'required'" name="title" type="text" v-model="project.title" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.description') }}*</label>
                                    <textarea v-model="project.description" class="form-control" rows="2d-inline align-text-top"></textarea>
                                </div>
                            </div>
                            <div class="row" v-if="proj_companies.length > 1">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.belongs_to_company') }}*</label>
                                    <v-select
                                            v-model="project.company_id"
                                            :options="proj_companies"
                                            label="title"
                                            index="id"
                                            :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.address') }}*</label>
                                    <places v-validate="'required'" type="text" v-model="project.address" class="form-control" required
                                            @change="changeEditingAddress"
                                            :options="{ appId: 'plQCIBC0U0NA', apiKey: 'c4d78aefebb6e8c565275c6ec8aff1d2' }"
                                    >
                                    </places>
                                    <div class="row mt-2">
                                        <div class="col-sm-12 col-md-6 form-group">
                                            <label class="control-label">Latitude</label>
                                            <input type="number" step="0.0001" v-model="editing_geo.lat" class="form-control">
                                        </div>
                                        <div class="col-sm-12 col-md-6 form-group">
                                            <label class="control-label">Longitude</label>
                                            <input type="number" step="0.0001" v-model="editing_geo.lng" class="form-control">
                                        </div>
                                    </div>
                                    <input type="hidden" v-model="project.geo_point"/>
                                </div>
                            </div>

                            <!--   <div class="row">
                                  <div class="col-sm-6 form-group">
                                      <label class="control-label cursor-pointer">
                                          {{ trans('custom.public') }}
                                          <a :href="publicUrl" target="_blank">({{ trans('custom.preview') }})</a>
                                      </label>
                                      <v-select
                                              v-model="project.public"
                                              :options="editing_publiched"
                                              label="title"
                                              index="value"
                                      ></v-select>
                                  </div>
                                  <div class="col-sm-6 form-group">
                                     <label class="control-label">{{ trans('custom.status') }}</label>
                                      <v-select
                                              v-model="project.status"
                                              :options="editing_status"
                                              label="title"
                                              index="value"
                                      ></v-select>
                                  </div>
                              </div>-->
                            <div class="upload_styled">
                                <simple-file-upload :setImage="project.image" @uploaded="imageProjectUploaded"></simple-file-upload>
                                <span class="custom_upload_btn btn">{{ trans('custom.choose_image') }}</span>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group text-left mt-2">
                                    <button class="btn btn-primary">{{ trans('custom.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade styled_modal" id="deleteModal" role="dialog" ref="deleteVueModel">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.delete_confirm') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <button @click="deleteEntry(deleting_id)"
                                type="button"
                                data-dismiss="modal"
                                class="btn btn-warning mr-2">{{ trans('custom.move_to_trash') }}
                        </button>
                        <button @click="deleteFinalEntry(deleting_id)"
                                type="button"
                                data-dismiss="modal"
                                class="btn btn-danger ml-2">{{ trans('custom.force_delete') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Video Modal -->
        <div class="modal fade" id="videoModal" role="dialog" ref="vuemodal">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-if="video_title">{{ video_title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center" v-if="video_iframe" v-html="video_iframe"></div>
                </div>
            </div>
        </div>

        <!-- Image Preview -->
        <div class="modal fade" id="projectImageModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ project.title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <img :src="project.image" style="max-width: 100%;max-height: 100%;" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Convert Modal -->
        <div class="modal fade styled_modal" id="convertModal" role="dialog" ref="convertVueModel">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.select_format') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <template v-if="convert_cant_convert">
                            <p v-html="trans('custom.cant_convert_text')"></p>
                        </template>
                        <template v-else>
                            <div class="btn-group-vertical btn-group-lg">
                                <button @click="startConvertion(convert_item_id, format)" type="button" class="btn btn-primary" v-for="format in convert_list">.{{ format }}</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Folder Modal -->
        <div class="modal fade styled_modal" id="newFolderModal" role="dialog" ref="newFolderVueModel">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.prompt_folder_name') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="col-sm-12 form-group">
                            <input type="text" v-model="new_folder_name" class="form-control">
                        </div>

                        <div class="col-sm-12 form-group">
                            <button @click="addGalleryFolder()" type="button"
                                    class="btn btn-primary width-150">{{ trans('custom.create') }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div id="undo_delete" v-if="delete_progress">
            <div class="progress mb-1 cursor-pointer" v-for="item, index in delete_progress" @click="undoDelete(index, item.id)">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" :style="{'width': item.progress + '%'}"></div>
                <h5>{{ item.title }} Deleted <span class="text-success">[UNDO]</span></h5>
            </div>
        </div>
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    import 'viewerjs/dist/viewer.css'
    import Viewer from 'v-viewer'
    import Places from 'vue-places'

    Vue.use(Viewer)

    export default {
        components: {
            vueDropzone: vue2Dropzone,
            Places
        },
        data() {
            return {
                projectId: null,
                companyId: null,
                project: {
                    permissions: [],
                    company:{},
                    items:{},
                },
                types: null,
                allowedDisplay: function(action) {
                    return window.allowedDisplay(action)
                },
                video_iframe: null,
                video_title: null,
                logs: [],
                item_operation_type :'document',
                item_operation_action :'add',
                delete_progress: [],
                convert_list: [],
                convert_item_id: null,
                convert_item_index: null,
                convert_cant_convert: false,

                dropzoneOptions: {
                    url: '/api/v1/upload?binary=1',
                    paramName: 'image',
                    headers: {
                        "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                    },
                    acceptedFiles: 'image/*',
                    dictDefaultMessage: '<div class="text-white btn btn-info d-inline-block upload_btn">' +
                        '<i data-v-abb2ff96="" class="plus_icon"></i>  Choose a file</div> <h5 class="d-inline-block ml-1">or drug it in here</h5>' +
                        '<title class="limit-upload-message">Maximum file size is 32MB</title>',
                    resizeHeight: 1080,
                    previewTemplate: this.template(),
                },
                gallery_images: [],
                gallery_index: null,
                gallery_folder: '',
                deleting_id:'',
                emails: [],
                contact: {
                    fullname: '',
                    email: '',
                    phone: '',
                    project_id: null,
                    position: ''
                },
                back_page : '/',
                files_types:{},
                files_types_arr:[],
                doc_types:{},
                doc_types_arr:[],
                new_item: {
                    project_id: '',
                    title: '',
                    description: '',
                    type: '',
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
                urls_tmp: [],
                editing_job_done_at:new Date(),
                selectedType: null,
                submitted: false,
                uploadPercentage: 0,
                uploadPercentage_tech: 0,
                totalFiles: 0,
                new_folder_name:'',
                publicUrl: window.publicUrl,
                proj_companies:[],
                companies_transfer:[],
                uploadProgresses: [],
                project_visibility: [],
                editing_geo: {
                    lat: 0,
                    lng: 0
                },
                editing_publiched:[
                    {title:trans('custom.disabled'),
                        value: '0'},
                    {title:trans('custom.active'),
                        value: '1'},
                ],
                editing_status:[
                    {title:trans('custom.hidden') ,
                        value: '0'},
                    {title:trans('custom.published'),
                        value: '1'},
                ],
                change_own_type:'change_own',
                copy_type:'copy',
                transfered_id: false,
                transfer_type:'change_own',
                transfer_company_id:false,
            }
        },
        mounted() {
            this.projectId = this.$route.params.id;
            this.companyId = this.$route.params.company_id;
            this.contact.project_id = this.$route.params.id;
            this.new_item.project_id = this.$route.params.id;
            var app = this;
            axios.get('/api/v1/projects/' + this.projectId).then(resp => {
                this.project = resp.data;
                if (this.project.permissions.includes('project_view_logs')) {
                    axios.get('/api/v1/projects/' + this.projectId + '/logs').then(response => (this.logs = response.data))
                }

                this.fetchGallery('');

                let tabs = this.$refs.itemsTabs;

                axios.get('/api/v1/usersettings').then(resp => {
                    this.back_page = (typeof resp.data !== 'undefined' && typeof resp.data.pre_project_page !== 'undefined') ? resp.data.pre_project_page : '/';
                    let hash = (typeof resp.data !== 'undefined' && typeof resp.data['project_' + this.projectId + '_view'] !== 'undefined') ? resp.data['project_' + this.projectId + '_view'] : decodeURI(window.location.hash).split('#')[2];
                    if (typeof hash === "undefined") {
                        hash = 'files';
                    }
                    tabs.selectTab("#" + hash);
                }).catch(() => {
                    let hash = decodeURI(window.location.hash).split('#')[2];
                    if (typeof hash === "undefined") {
                        hash = 'files';
                    }
                    tabs.selectTab("#" + hash);
                    console.log("Could not load settings")
                });
            }).catch(resp => {
                if (resp.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert(trans('custom.content_not_allowed'), {
                        'html': true,
                        okText: 'ok'
                    }).then(() => {
                        $('.close').click();
                        app.$router.replace('/');
                    });
                }
            });
            $('.map_item').addClass('disabled');
            var app= this;
            axios.get('/api/v1/companies/verified').then((resp) => {
                for(let key in resp.data){
                    app.proj_companies.push({
                        'id' : resp.data[key].id,
                        'title' : resp.data[key].title,
                    });
                }
            }).catch((data) => {
                if (data.response.status !== 401) {
                    app.$dialog.alert("Could not load companies");
                }
            });

            axios.get('/api/v1/companies/all_verified').then((resp) => {
                for(let key in resp.data){
                    app.companies_transfer.push({
                        'id' : resp.data[key].id,
                        'title' : resp.data[key].title,
                    });
                }
            }).catch((data) => {
                if (data.response.status !== 401) {
                    app.$dialog.alert("Could not load companies");
                }
            });

            axios.get('/api/v1/projects/visibility_counter', {
                params: {
                    ids: [this.projectId]
                }
            }).then(resp => {
                this.project_visibility = resp.data;
            })

            axios.get('/api/v1/projects/emails').then((resp) => {
                this.emails = resp.data;
            });

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

            $(this.$refs.vuemodal).on("hidden.bs.modal", () => {
                this.video_iframe = '';
            });

            $('body').on('click', '.custom_upload_btn', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').click();
            });

            $('body').on('click', '.dz-default.dz-message', function(){
                $(this).parents('#upload-file').find('.dz-clickable:first').click();
            });
        },
        methods: {
            tabClicked (selectedTab) {
                console.log('Current tab re-clicked:' + selectedTab.tab.name);
            },
            tabChanged (selectedTab) {
                let tempUrl = decodeURI(window.location.hash).split('#')[1];
                window.location.hash = tempUrl + '#' + selectedTab.tab.id.toLowerCase();
                this.updateUserSettingProjectTab(selectedTab.tab.id.toLowerCase());
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
            template: function () {
                return `<div class=\"dz-preview dz-file-preview\">
                   <div class=\"dz-image\"><img data-dz-thumbnail /></div>
                    <div class=\"dz-details\">   <div class=\"dz-size\"><span data-dz-size></span></div>
                     <div class=\"dz-filename\"><span data-dz-name></span></div> </div>
                    <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>
                    <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>  <div class=\"dz-success-mark\">
                    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">      <title>Check</title>      <defs></defs>      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>      </g>    </svg>  </div>  <div class=\"dz-error-mark\">    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">      <title>Error</title>      <defs></defs>
                    <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">
                    <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">
                        <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>
                    </g>      </g>    </svg>  </div></div>`;
            },
            updateUserSettingProjectTab($tab) {
                axios.put('/api/v1/usersettings', {
                    'settings_key': 'project_'+ this.projectId +'_view',
                    'settings_value': $tab,
                }).then(function (resp) {
                    console.log('View settings updated');
                }).catch(function (resp) {
                    console.log('Failed to update View settings');
                });
            },
            getConvertedTooltipText(status) {
                const data = status.split("||");

                if (data.length > 1) {
                    return trans(data[0]) + " ." + data[1]
                }
                return data[0]
            },
            openDeleteMenu(id){
                this.deleting_id = id;
            },
            deleteFinalEntry(id) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.attention_project_will_be_removed_with_all_files_without_ability_to_restore'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + id + '/force').then(() => {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_been_deleted'), {'html':true, okText:'ok'});
                        }).catch(() => {
                            app.$dialog.alert(window.trans('custom.delete_error'));
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            deleteEntry(id) {
                var app = this;
                axios.delete('/api/v1/projects/' + id).then(() => {
                    app.$dialog.alert(window.trans('custom.project_will_be_deleted_in_30_days_without_ability_to_restore'),{
                        'html': true,
                        okText: 'ok'
                    }).then(() => {
                        $('.close').click();
                        app.$router.replace('/');
                    });
                }).catch((resp) => {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.trans('custom.delete_error'));
                    }
                });
            },
            deleteItemEntryFile(type) {
                var app = this;
                app.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + app.projectId + '/item/' + app.editing_item.id + '/file/' + type).then(() => {
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
            deleteEntryItem(id, index, type, group) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + app.projectId + '/item/' + id)
                            .then(function (resp) {
                                axios.get('/api/v1/projects/' + app.projectId).then(response => (app.project = response.data));

                                const deleted_index = app.delete_progress.push({
                                    'id': id,
                                    'title': app.project.items[type][group][index].title,
                                    'progress': 100
                                }) - 1;

                                setTimeout( () => {
                                    app.delete_progress[deleted_index].progress = 0;
                                });

                                setTimeout( () => {
                                    if (app.delete_progress[0] && app.delete_progress[0].id === id) {
                                        app.delete_progress.splice(0, 1);
                                    }
                                }, 5000)
                            })
                            .catch(function (resp) {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert("Could not delete project item");
                                }
                            });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });

            },
            deleteEntryContact(id, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + id + '/contact/' + id)
                            .then(function (resp) {
                                app.project.contacts.splice(index, 1);
                            })
                            .catch(function (resp) {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert("Could not delete contact");
                                }
                            });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            openVideo(embed, title) {
                this.video_iframe = embed;
                this.video_title = title;
            },
            openConvert(item_id, formats, index) {
                this.convert_list = formats;
                this.convert_item_id = item_id;
                this.convert_item_index = index;
                this.convert_cant_convert = (formats[0] === 'cant_convert')
            },
            startConvertion(item_id, format) {

                var app = this;

                app.convert_item_id = null;
                app.project.items['files']['point_clouds'][app.convert_item_index].status = 'custom.item_status_cc_converting||' + format;
                $('#convertModal').modal('hide');

                axios.post('/api/v1/projects/' + app.projectId + '/item/' + item_id + '/convert', {format: format})
                    .then(function () {})
                    .catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not convert this item");
                        }
                    })
            },
            resetUpload() {
                $('.dz-default.dz-message').css('display', 'block');
                this.submitted = false;
                this.uploadPercentage = 0;
                this.uploadPercentage_tech = 0;
                let drozone_key = this.item_operation_action == 'add'? 'dropzone_' : 'dropzone_edit_';
                if (this.selectedType && this.selectedType.types) {
                    let merged_types = this.mergeTypes(this.selectedType.types);
                    for (let index in merged_types) {
                        if (this.$refs[drozone_key + index] && this.$refs[drozone_key + index].length > 0) {
                            this.$refs[drozone_key + index][0].removeAllFiles()
                        }
                    }
                }
            },
            uploadErrorHandle(file, error) {
                this.resetUpload();
                this.$dialog.alert("Message:\n\n" + JSON.stringify(error));
            },
            fileAdded(){
                $('.dz-default.dz-message').css('display', 'none');
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

                if (min < 101){
                    if(min == 100 && this.uploadPercentage_tech == 100 || min < 100){
                        this.uploadPercentage = min.toFixed(0);
                    }
                    this.uploadPercentage_tech = min;
                }
            },
            restoreItem(id) {
                var app = this;

                axios.put('/api/v1/projects/' + app.projectId + '/item/' + id + '/restore')
                    .then(function (resp) {
                        axios.get('/api/v1/projects/' + app.projectId).then(response => (app.project = response.data));
                    })
                    .catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not restore item");
                        }
                    });
            },
            forceDeleteItem(id, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + id + '/item/' + id + '/force')
                            .then(function (resp) {
                                app.project.deleted.splice(index, 1);
                            })
                            .catch(function (resp) {
                                if (resp.response.status === 401) {
                                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                                } else {
                                    app.$dialog.alert("Could not delete contact");
                                }
                            });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            undoDelete(index, id) {
                this.delete_progress.splice(index, 1);
                this.restoreItem(id);
            },
            checkItemFailed(status) {
                if (status === 'custom.item_status_failed_size') {
                    return true;
                } else if (status === 'custom.item_status_failed_timeout') {
                    return true;
                } else if (status === 'custom.item_status_failed') {
                    return true;
                }
                return false;
            },
            issetShareUrls(item_urls){
                var app = this;
                var result = false;
                if(allowedDisplay('project_delete', 'project', app.projectId) || allowedDisplay('all')){
                    for (let key in item_urls) {
                        if (item_urls[key].external_url) {
                            result = true;
                        }
                    }
                }
                return result;
            },
            shareProcess(item_urls){
                var app = this;
                var urls = {};
                for (let key in item_urls) {
                    if (item_urls[key].external_url) {
                        urls[item_urls[key].type] = item_urls[key].external_url;
                    }
                }
                if(Object.keys(urls).length > 1){
                    app.share_list = urls;
                    $('#shareModal').modal('show');
                }
                if(Object.keys(urls).length == 1){
                    app.copyExternalUrl(urls[Object.keys(urls)[0]]);
                }
            },
            copyExternalUrl(url, is_frame = false){
                var t_area = document.createElement('textArea');
                t_area.value = url;
                document.body.appendChild(t_area);
                console.log(t_area.innerHTML)
                t_area.select();
                document.execCommand("copy");
                document.body.removeChild(t_area);
                if (is_frame){
                    this.$dialog.alert(window.trans('custom.frame_been_copied_to_the_clipboard'));
                }else{
                    this.$dialog.alert(window.trans('custom.url_been_copied_to_the_clipboard'));
                }
            },

            framedView(url){
                return '<iframe width="560" height="315" src="'+url+'" frameborder="0" allowfullscreen></iframe>';
            },

            galleryUpload(file, response) {
                var app = this;
                if (response && response.image) {
                    axios.post('/api/v1/projects/' + app.projectId + '/gallery', {
                        url: response.image,
                        folder_id: app.gallery_folder
                    }).then((resp) => {
                        app.gallery_images['urls'].push(response.image);
                        app.gallery_images['ids'].push(resp.data.id);
                    }).catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not add image to gallery");
                        }
                    })
                }
            },
            galleryDelete(index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        const id = app.gallery_images['ids'][index];
                        axios.delete('/api/v1/projects/' + app.projectId + '/gallery/' + id).then(() => {
                            app.gallery_images['urls'].splice(index, 1);
                            app.gallery_images['ids'].splice(index, 1);
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            dropDownButtonClass(status) {
                if (this.checkItemFailed(status)) {
                    return 'btn-outline-danger';
                }

                if (status === 'custom.item_status_success') {
                    return 'btn-outline-success';
                }

                return 'btn-outline-warning';
            },
            addGalleryFolder() {
                var app = this;
                if (app.new_folder_name != '') {
                    axios.post('/api/v1/projects/' + app.projectId + '/gallery/folder', {
                        title: app.new_folder_name
                    }).then((resp) => {
                        app.gallery_images['folders'].push({
                            'id': resp.data.id,
                            'title': app.new_folder_name
                        });
                        $('.close').click();
                        app.new_folder_name = '';
                    }).catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not add folder to gallery");
                        }
                    })
                }
            },
            changeGalleryFolder(folder_id) {
                var app = this;
                if (folder_id == ''){
                    setTimeout(function(){    app.gallery_folder = folder_id;
                        app.gallery_images = [];
                        app.fetchGallery(folder_id); }, 50);
                } else {
                    this.gallery_folder = folder_id;
                    this.gallery_images = [];
                    this.fetchGallery(folder_id);
                }

            },
            fetchGallery(folder_id) {
                axios.get('/api/v1/projects/' + this.projectId + '/gallery?folder_id=' +folder_id).then(response => (
                    this.gallery_images = response.data
                ))
            },
            galleryDeleteFolder(id, index) {
                var app = this;
                this.$dialog.confirm(window.trans('custom.delete_confirm'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + app.projectId + '/gallery/folder/' + id).then(() => {
                            app.gallery_images['folders'].splice(index, 1);
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            galleryDownloadGallery(id){
                var app = this;
                axios({
                    url: '/api/v1/projects/' + id + '/gallery/download',
                    method: 'GET',
                    responseType: 'blob',
                }).then((response) => {
                        let filename = response.headers['content-disposition'].split(';')[1].split('=')[1];
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                        var fileLink = document.createElement('a');

                        fileLink.href = fileURL;
                        fileLink.setAttribute('download',filename);
                        document.body.appendChild(fileLink);
                        fileLink.click();
                }).catch(function (resp) {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not download gallery");
                    }
                });
            },
            galleryDownloadFolder(id) {
                var app = this;
                    axios({
                        url: '/api/v1/projects/gallery/folder/' + id + '/download',
                        method: 'GET',
                        responseType: 'blob',
                    }).then((response) => {
                            let filename = response.headers['content-disposition'].split(';')[1].split('=')[1];
                            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                            var fileLink = document.createElement('a');

                            fileLink.href = fileURL;
                            fileLink.setAttribute('download',filename);
                            document.body.appendChild(fileLink);
                            fileLink.click();
                    }).catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert("Could not download folder");
                        }
                    });
            },
            back_to_previous(){
                window.location.href = this.back_page;
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
            submitFileForm() {
                event.preventDefault();

                if (!this.submitted) {
                    if (this.selectedType && this.selectedType.types) {
                        let drozone_key = this.item_operation_action == 'add'? 'dropzone_' : 'dropzone_edit_';
                        let merged_types = this.mergeTypes(this.selectedType.types);
                        for (let index in merged_types) {
                            if (!this.simpleInput(merged_types[index])) {
                                if (this.$refs[drozone_key + index] && this.$refs[drozone_key + index].length > 0) {
                                    this.$refs[drozone_key + index][0].processQueue();
                                }
                            }
                        }

                        if (this.totalFiles < 1) {
                            this.saveFileForm();
                        }
                    }
                }
            },
            changeEditingAddress(e) {
                if (e.latlng) {
                    this.project.geo_point = e.latlng;
                    this.editing_geo = e.latlng;
                }
            },
            changeAddType(type, action, edit_id = false){
                this.item_operation_type = type;
                this.item_operation_action = action;
                this.new_item = {
                    project_id: this.projectId,
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
                    for (let item_key in this.project.items[cur_type]) {
                        let elements = this.project.items[cur_type][item_key];
                        for (let el_type in elements) {
                            if (elements[el_type].id == edit_id) {
                                this.editing_item = elements[el_type];
                                this.editing_job_done_at =  new Date(elements[el_type].job_done_at);
                                this.editing_item.upload = [];

                                if (this.item_operation_type == 'file'){
                                    this.selectedType = (this.files_types[elements[el_type].type]) ? this.files_types[elements[el_type].type] : null;
                                }
                                if (this.item_operation_type == 'document'){
                                    this.selectedType = (this.doc_types[elements[el_type].type]) ? this.doc_types[elements[el_type].type] : null;
                                }
                            }
                        }

                    }
                    this.urls_tmp = JSON.stringify(this.editing_item.urls);
                }
            },
            simpleInput(type) {
                return type == 'youtube' || type == 'view_url';
            },
            filedropzoneOptions(types, index) {
                return {
                    url: '/api/v1/upload-chunk?index=' + index + '&project_id=' + this.projectId,
                    paramName: 'file',
                    headers: {
                        "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                    },
                    acceptedFiles: types,
                    dictDefaultMessage: trans('custom.drop_upload_text'),
                    previewTemplate: this.template_file(),
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
            },
            saveFileForm() {
                var app = this;
                let curr_item = app.item_operation_action == 'edit'? app.editing_item : app.new_item;
                let formData = new FormData();

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
                    axios.post('/api/v1/projects/' + app.projectId + '/item/' + curr_item.id, formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(() => {
                        window.location.reload();
                    }).catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.resetUpload();

                            if (error.response.data && error.response.data.errors) {
                                app.$dialog.alert(window.parseError(error.response.data.errors), {'html': true});
                            } else if (error.response.data) {
                                app.$dialog.alert(window.parseError(error.response.data), {'html': true});
                            }
                        }
                    });
                } else {
                    axios.post('/api/v1/projects/' + app.projectId + '/item', formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(() => {
                        $('.close').click();
                        if (this.item_operation_type == 'document') {
                            this.updateUserSettingProjectTab('documents');
                            window.location.reload();
                        } else {
                            this.updateUserSettingProjectTab('files');
                            window.location.reload();
                        }

                    }).catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
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
            deleteVisibility(project_id){
                var app = this;
                app.$dialog.confirm(window.trans('custom.are_you_sure_you_want_to_permanently_leave_this_project'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + project_id + '/visibility_leave').then((response) => {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>'
                                + response.data.description, {
                                'html': true,
                                okText: 'ok'
                            }).then(() => {
                                window.location.href = '/';
                            });
                        }).catch((error) => {
                            if (error.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert('Error occured. Try Again later.');
                            }
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            typeChange(e) {
                this.new_item.urls = [];
                if (this.item_operation_type == 'file'){
                    this.selectedType = (this.files_types[e]) ? this.files_types[e] : null;
                }
                if (this.item_operation_type == 'document'){
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
            openEditProject(project_id) {
                this.project.status = this.project.status == 0 ? '0' : '1';
                this.project.public = this.project.public == null ? '0' : '1';


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
            saveProjectForm() {
                event.preventDefault();
                var app = this;
                app.project.geo_point = this.editing_geo;

                app.project.status = app.project.status == '0' ? 0 : 1;
                app.project.public = app.project.public == '0' ? null : '1';
                axios.put('/api/v1/projects/' + app.project.id, app.project).then(() => {
                    window.location.reload();
                }).catch((error) => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not edit your project");
                    }
                });

            },
            imageProjectUploaded(file) {
                this.project.image = file;
            },
            saveContactForm() {
                event.preventDefault();
                let app = this;
                let formData = app.contact;

                axios.post('/api/v1/projects/' + app.projectId + '/contact', formData)
                    .then(function (resp) {
                        app.updateUserSettingProjectTab('team');
                        window.location.reload();
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.parseError(error.response.data.errors, 'Could not create new contact:'), {'html': true});
                        }
                    });
            },
            selectContactEmail(e) {
                if (e && e.email) {
                    this.contact.fullname = e.name;
                    this.contact.email = e.email;
                    this.contact.phone = e.phone;
                } else {
                    this.contact.fullname = '';
                    this.contact.email = '';
                }
            },
            openTransferProject(){
                this.transfer_company_id = false;
                this.transfer_type = 'change_own';

            },
            transferProjectForm() {
                var app = this;
                if (!app.transfer_company_id) {
                    app.$dialog.alert("Could not succeed the transfer");
                } else {
                    if (app.companyId == app.transfer_company_id) {
                        app.$dialog.alert(window.trans('custom.project_already_belongs'));
                    } else {
                        axios.post('/api/v1/transfer',
                            {
                                'type': app.transfer_type,
                                'id': app.projectId,
                                'company_id': app.transfer_company_id,
                            }).then((resp) => {
                            if (resp.data.result) {
                                app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>'
                                    + window.trans('custom.transfer_request_succeed'), {
                                    'html': true,
                                    okText: 'ok'
                                }).then(() => {
                                    $('.close').click();
                                });
                            } else {
                                app.$dialog.alert(resp.data.description?resp.data.description:resp.data);
                            }

                        }).catch((data) => {
                            if (data.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert("Could not succeed the transfer");
                            }
                        });
                    }
                }
            },
        }
    }
</script>

<style scoped>
    .gallery_image {
        display: inline-block;
        cursor: pointer;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
        border: 1px solid #ebebeb;
        margin: 5px;
        width: calc(20% - 10px);
        height: 200px;
        position: relative;
        vertical-align: top;
        overflow: hidden;
    }

    .tabs-component-panel .dropzone span{
        font-size: 16px;
    }

    .tabs-component-panel .dropzone{
        min-height: 25px;
        border-style: dashed;
        padding: 0;
    }

    .gallery_image:last-child {
        margin-right: 0;
    }

    .gallery_image img {
        position: absolute;
        z-index: 3;
        min-height: 100%;
        min-width: 100%;
    }



    .fa.fa-download{
        text-shadow: 1px 1px rgb(255 255 255 / 37%);
    }

    .download-folder.top-link{
        float: right;
        margin-top: -38px;
        color: #298494;
        text-decoration: underline;
        cursor: pointer;
        font-weight: bold;
    }

    .download-folder.top-link:hover{
        color:#2E96A8;
    }

    .gallery-folder {
        background: url(/images/icons/folder-icon.png) no-repeat;
        background-position-y: -8px;
        width: calc(20% - 54px);
        background-position: center center;
        background-size: contain;
        margin-left: 25px;
        margin-right: 25px;
    }

    a.btn.btn-danger i{
        color:white !important;
    }
</style>
