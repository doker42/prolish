
<template>
    <div class="file_index 444">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-2 tablet-desktop-view">
            <span class="special_title"><b>{{ trans('custom.files') }}</b></span>
        </div>
        <span class="special_title mobile-view">{{ trans('custom.files') }}</span>
        <div class="mobile-view mt-4 text-right">
            <button class="btn filters" v-on:click="toggleFilterDisplay" :class="show_filters?'btn-success':'btn-outline-success'">{{trans('custom.filters')}}</button>
        </div>
        <div class="row new_sort_view mb-4">
            <div class="row mx-3 mt-3" :class="show_filters?'':'tablet-desktop-view'">

                <div :class="{ 'active' : order_data.field == 'title'}" class="sort_item">
                    <div  v-on:click="changeOrder('title')" class="sorting_title">{{ trans('custom.name') }}<i style="cursor:pointer"
                                                                                                               :class="{ 'sort' : typeof order_data.field !== 'title','sort-asc' : order_data.field === 'title' && order_data.dir === 'ASC' , 'sort-desc' : order_data.field === 'title' && order_data.dir === 'DESC'}"
                                                                                                               class="order-size"></i></div>
                </div>

                <div :class="{ 'active' : order_data.field == 'created_at'}" class="sort_item">
                    <div v-on:click="changeOrder('created_at')" class="sorting_date">{{ trans('custom.date') }}<i style="cursor:pointer"
                                                                                                                  :class="{ 'sort' : typeof order_data.field !== 'created_at','sort-asc' : order_data.field === 'created_at' && order_data.dir === 'ASC' , 'sort-desc' : order_data.field === 'created_at' && order_data.dir === 'DESC'}"
                                                                                                                  class="order-date"></i></div>
                </div>

                <div :class="{ 'active' : order_data.field == 'size'}" class="sort_item">
                    <div class="sorting_size"  v-on:click="changeOrder('size')">{{ trans('custom.size') }}<i style="cursor:pointer"
                                                                                                             :class="{ 'sort' : typeof order_data.field !== 'size','sort-asc' : order_data.field === 'size' && order_data.dir === 'ASC' , 'sort-desc' : order_data.field === 'size' && order_data.dir === 'DESC'}"
                                                                                                             class="order-size"></i></div>
                </div>

                <div v-if="tab != 'temp_files'" :class="is_super_user?'admin_view':''"  class="uploaders_file_filter">
                    <v-select :placeholder="trans('custom.project')"
                              :options="uploaders"
                              label="title"
                              index="id"
                              :autocomplete="is_super_user?'on':'off'"
                              :clearable="false"
                              v-model="uploader"
                              @input="filterFiles()">
                        <template v-slot:option="option">
                            <span>{{ option.list_title }}</span>
                        </template>
                    </v-select>
                </div>

                <div v-if="tab != 'temp_files'"  class="type_file_filter">
                    <v-select :placeholder="trans('custom.project')"
                              :options="all_file_types"
                              label="title"
                              index="value"
                              :clearable="false"
                              v-model="file_type"
                              @input="filterFiles()">
                        <template v-slot:option="option">
                            <span>{{ option.name }}</span>
                        </template>
                    </v-select>
                </div>

                <div v-if="tab != 'temp_files'" :class="is_super_user?'admin_view':''" class="file_project_filter">
                    <v-select :placeholder="trans('custom.project')"
                              :options="projects_filt"
                              label="title"
                              :autocomplete="is_super_user?'on':'off'"
                              index="id"
                              :clearable="false"
                              v-model="belongs_to"
                              @input="filterFiles()">
                        <template v-slot:option="option">
                            <span>{{ option.list_title }}</span>
                        </template>
                    </v-select>
                </div>

                <div v-if="tab != 'temp_files'"  class="mt-2 sort_item tablet-desktop-view">
                    <div class="clear_filters"  v-on:click="clearFilters">{{ trans('custom.clear') }}</div>
                </div>

                <div v-if="tab != 'temp_files'"  class="mt-3 w-100 text-center mobile-view">
                    <div class="btn btn-outline-primary width-200"  v-on:click="clearFilters">{{ trans('custom.clear') }}</div>
                </div>

            </div>
        </div>

        <div class="tabs_container">

            <tabs :options="{ useUrlFragment: false }" ref="itemsTabs" @clicked="tabClicked" @changed="tabChanged">
                <!-- Files tab -->
                <tab :name="trans('custom.files')" id="file_items">
                </tab>
                <tab :name="trans('custom.google_disk')" id="google_disk">
                </tab>
                <tab v-if="is_allowed_storage" :name="trans('custom.temporary_space')" id="temp_files">
                </tab>

                <span v-if="is_allowed_storage" class="question_tooltip"
                      v-tooltip="{
                        html:true,
                        content: '<span class=\'desc\'>' + trans('custom.webdav_tooltip') + '</span><a target=\'_blank\' href='+ webdav_instruction_url + '>'+trans('custom.how_to_use_wedav') + '</a><div class=\'temp_storage_info\'>' +
                         '<div><span class=\'data-title\'>'+trans('custom.webdav_url')+': ' +' </span><span class=\'value\'>'+webdav_url+'</span><span class=\'copy_webdav_url\'></span></div>' +
                         '<div><span class=\'data-title\'>'+trans('custom.webdav_login')+': ' +' </span><span class=\'value\'>'+webdav_login+'</span><span class=\'copy_webdav_login\'></span></div>' +
                          '<div><span class=\'data-title\'>'+trans('custom.webdav_password')+': '+ '</span><span class=\'value\'>' +webdav_pass+'</span><span class=\'copy_webdav_pass\'></span><div></div>',
                        show: is_webdav_tooltip_open,
                        trigger: 'manual',
                        classes :'instruction',
                       }"></span>
            </tabs>

          <span class="questions"><a href="https://my3d.artreal.pro/webdav_instruction.pdf" target="_blank">?</a></span>
<!--            <router-link v-if="tab != 'temp_files'"-->
<!--                         :to="{name: 'addProjectItem', params: {id: 'choose', company_id: 'all', type:'choose'}}"-->
<!--                         class="btn btn-success add_new_item">-->
<!--                <i class="add_file_icon"> </i> {{-->
<!--                    trans('custom.add_file') }}-->
<!--            </router-link>-->

            <button v-on:click="uploadModal" v-if="tab != 'temp_files'" class="btn-primary btn add_new_item"><i class="plus_icon"></i> {{ trans('custom.upload_file') }}</button>
            <button v-on:click="initiateUploadFile" v-else-if="is_allowed_storage" class="btn-primary btn add_new_item"><i class="plus_icon"></i> {{ trans('custom.upload_file') }}</button>
        </div>

        <div class="card items_container" v-if="tab != 'temp_files'">
            <table class="table table-striped" v-if="((uploading_file.in_process && uploading_file.type !='uploading_temp_storage') || file_items.data.length > 0)">
                <thead>
                <tr>
                    <th width="5%"></th>
                    <th class="tablet-desktop-view" width="10%"></th>
                    <th class="tablet-desktop-view" width="35%"></th>
                    <th class="mobile-view" width="35%"></th>
                    <th class="tablet-desktop-view" width="35%"></th>
                    <th class="mobile-view" width="10%"></th>
                    <th class="mobile-view" width="39%"></th>
                    <th class="text-center tablet-desktop-view"></th>
                </tr>
                </thead>
                <tbody>
                <template v-if="uploading_file.in_process && uploading_file.type !='uploading_temp_storage'">
                    <tr class="text-left h3 bg-secondary font-weight-bold desktop-view uploading_files"
                        :class="{ 'finished': uploading_file.finished}">
                        <td colspan="5">{{ trans('custom.uploading_files') }}</td>
                    </tr>
                    <tr class="text-center h3 font-weight-bold tablet-mobile-view uploading_files"
                        :class="{ 'finished': uploading_file.finished}">
                        <td colspan="5">{{ trans('custom.uploading_files') }}</td>
                    </tr>
                    <tr class="uploading_file">
                        <td class="pt-4 pl-2 desktop-view uploading_files"
                            :class="{ 'finished': uploading_file.finished}">
                            <i :class="'text-dark fa fa-file-other'" style="font-size: 2rem;"></i>
                        </td>

                        <td class="pt-3 pb-0 pl-2 tablet-mobile-view uploading_files"
                            :class="{ 'finished': uploading_file.finished}">
                            <i class='text-dark fa fa-file-other' style="font-size: 2rem;"></i>
                        </td>
                        <td>
                            <div class="name_holder uploading_files" :class="{ 'finished': uploading_file.finished}">
                                <span class="name">{{ uploading_file.title }}</span>
                                <span class="date">  {{ uploading_file.job_done_at.getFullYear()  + "-" + (uploading_file.job_done_at.getMonth()+1) + "-" + (uploading_file.job_done_at.getDate() < 10? '0'+uploading_file.job_done_at.getDate():uploading_file.job_done_at.getDate()) }}</span>
                            </div>
                        </td>
                        <td class="align-middle tablet-desktop-view">
                            {{ trans('custom.file') }}
                        </td>
                        <td class="align-middle uploading_files" :class="{ 'finished': uploading_file.finished}">
                            <div class="loading_title">{{trans('custom.loading')}}</div>
                            <div class="progress">
                                <div class="progress-bar"
                                     role="progressbar" :aria-valuenow="uploading_file.percentage" aria-valuemin="0"
                                     aria-valuemax="100" :style="'width:' + uploading_file.percentage + '%'">
                                    {{uploading_file.percentage}}%
                                </div>
                            </div>
                        </td>
                        <td class="align-middle h4 text-center">
                            <button v-if="!uploading_file.finished" class="btn btn-outline-success"
                                    v-on:click.prevent="triggerResetUpload()" type="button">
                                {{ trans('custom.cancel') }}
                            </button>
                            <button v-if="uploading_file.finished" class="btn btn-outline-success"
                                    v-on:click.prevent="window.location.reload()" type="button">
                                {{ trans('custom.ok') }}
                            </button>
                        </td>
                    </tr>
                </template>
                <template v-if="file_items.data.length > 0" v-for="item, index in file_items.data">
                    <tr :class="(item.deleted_at != null || item.project == null)?'deleted_file_item item_'+index:(checkItemFailed(item.status) ? 'text-danger item_'+index : 'item_'+index)">
                        <td class="pt-4 pl-2 desktop-view">
                            <template v-if="(item.deleted_at == null && item.project != null)">
                                <a v-if="item.view_url !== null" :href="item.view_url" target="_blank">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>

                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'youtube'"
                                   v-on:click.prevent="openVideo(item.url[0].embed, item.title)" href="#"
                                   data-toggle="modal" data-target="#videoModal">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>
                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"
                                   :href="item.url[0].url">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>

                                <i v-else :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                            </template>

                            <template v-else>
                                <i :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                            </template>

                        </td>
                        <td class="pt-3 pb-0 pl-2 tablet-mobile-view">
                            <template v-if="(item.deleted_at == null && item.project != null)">
                                <a v-if="item.view_url !== null" :href="item.view_url" target="_blank">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>

                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'youtube'"
                                   v-on:click.prevent="openVideo(item.url[0].embed, item.title)" href="#"
                                   data-toggle="modal" data-target="#videoModal">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>
                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"
                                   :href="item.url[0].url">
                                    <i :class="'text-dark fa ' + item.icon" style="font-size: 2rem;"></i>
                                </a>

                                <i v-else :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                            </template>

                            <template v-else>
                                <i :class="'fa ' + item.icon" style="font-size: 2rem;"></i>
                            </template>
                        </td>
                        <td class="tablet-desktop-view align-middle">
                            <div class="file_type">
                                {{trans('custom.file_names.'+item.type)}}
                            </div>
                        </td>
                        <td class="tablet-desktop-view">
                            <div class="name_holder">
                                <span class="name">{{ item.title }}<span v-if="item.description"
                                                                         class="question_tooltip"
                                                                         v-tooltip="{
                                                                                            content: item.description,
                                                                                            trigger : 'click',
                                                                                          }"></span></span>
                                <div class="bottom_info"><span class="date mr-2">  {{ item.job_done_at }}</span>
                                    <span class="size mr-2"> <i
                                        class="table-size fa fa-hdd"></i> {{ item.size }}mb</span>
                                    <template v-if="(item.deleted_at == null && item.project != null)">
                                    <span class="author mr-2">
                                        <a href="#" v-if="item.uploader !== null"
                                           v-on:click.prevent="openProfileUser(item.uploader.id)" class="tablet-view"
                                           data-toggle="modal" data-target="#profileModal"><img
                                            :src="item.uploader.picture"></a>
                                        <span v-else class="no_uploader"><img
                                            src="/images/user_pick_default.png"></span>
                                    </span>
                                        <router-link
                                            :to="{name: 'projectItems', params: {id: item.project_id, company_id: 'all'}}">
                                            <span class="project">{{"P"+String(item.project_id).padStart(4, '0')}}</span>
                                        </router-link>
                                    </template>
                                    <template v-else>
                                        <span class="author mr-2">
                                        <span href="#" v-if="item.uploader !== null"><img :src="item.uploader.picture"></span>
                                        <span v-else class="no_uploader"><img
                                            src="/images/user_pick_default.png"></span>
                                    </span>
                                        <span class="project">{{"P"+String(item.project_id).padStart(4, '0')}}</span>
                                    </template>
                                </div>
                            </div>
                        </td>
                        <td class="mobile-view">
                            <div class="name_holder">
                                                <span class="name">{{ item.title }}<span v-if="item.description" class="question_tooltip"
                                                                                         v-tooltip="{
                                                                                            content: item.description,
                                                                                            trigger : 'click',
                                                                                          }"></span></span>
                                <span class="date">  {{ item.job_done_at }}</span>
                            </div>
                        </td>
                        <td class="align-middle tablet-desktop-view"></td>
                        <td class="align-middle mobile-view">
                            {{ item.size }}mb
                        </td>
                        <td class="align-middle h4 text-center actions">
                            <template v-if="(item.deleted_at == null && item.project != null)">
                                <a v-if="item.external_view && (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))"
                                   href="#" class="tablet-desktop-view"
                                   v-on:click.prevent="copyExternalUrl(item.external_view)" target="_blank">
                                    <i class="fa fa-ext-share"></i></a>
                                <a v-if="item.view_url !== null && item.url.length > 0"
                                   :href="item.view_url" v-on:click="logPreview(item.view_url)"
                                   class="tablet-desktop-view"
                                   target="_blank">
                                    <i class="fa fa-preview"></i> </a>
                                <a v-if="item.url.length > 0 && item.url[0].type == 'youtube'"
                                   v-on:click.prevent="openVideo(item.url[0].embed, item.title)"
                                   class="tablet-desktop-view" href="#" data-toggle="modal"
                                   data-target="#videoModal">
                                    <i class="fa fa-preview"></i> </a>

                                <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"
                                   :href="item.url[0].url" class="tablet-desktop-view" target="_blank">
                                    <i class="fa fa-preview"></i> </a>
                            </template>
                            <div class="dropdown d-inline-block"
                                 v-if="item.project != null || item.project == null && (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))">
                                <button class="btn dropdown-toggle"
                                        v-bind:class="dropDownButtonClass(item.status)" type="button"
                                        data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                    {{ trans('custom.actions') }}
                                </button>
                                <div class="dropdown-menu">
                                    <template v-if="item.deleted_at != null">
                                        <a class="dropdown-item" v-on:click="restoreItem(item.id)" href="#">
                                            <i class="fa fa-restore"></i> {{
                                                trans('custom.restore') }}
                                        </a>
                                        <a class="dropdown-item" v-on:click="forceDeleteItem(item.id)" href="#">
                                            <i class="fa-trash-o bucket_icon"></i> {{
                                                trans('custom.delete') }}
                                        </a>
                                    </template>
                                    <template
                                        v-else-if="item.project == null && (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))">
                                        <a class="dropdown-item" v-on:click="restoreProject(item.project_id)" href="#">
                                            <i class="fa fa-restore"></i> {{
                                                trans('custom.restore_project') }}
                                        </a>
                                        <a class="dropdown-item" v-on:click="deleteFinalProject(item.project_id)"
                                           href="#">
                                            <i class="fa-trash-o bucket_icon"></i> {{
                                                trans('custom.delete_project') }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        <template v-if="item.view_url === null && item.url === null">
                                            <a class="dropdown-item disabled" href="#">
                                                <i class="fa fa-search text-warning"></i> {{
                                                    trans('custom.building') }}
                                            </a>
                                        </template>
                                        <template v-else-if="checkItemFailed(item.status)">
                                            <a class="dropdown-item disabled" href="#">
                                                <i class="fa fa-search text-warning"></i> {{
                                                    trans(item.status) }}
                                            </a>
                                        </template>
                                        <template v-else>
                                            <a v-if="item.view_url !== null" :href="item.view_url"
                                               target="_blank" v-on:click="logPreview(item.view_url)"
                                               class="dropdown-item text-capitalize">
                                                <i class="fa fa-search text-warning"></i> {{
                                                    trans('custom.preview') }}
                                            </a>
                                        </template>

                                        <a v-if="item.url.length > 0 && item.url[0].type == 'youtube'"
                                           v-on:click.prevent="openVideo(item.url[0].embed, item.title)"
                                           href="#" data-toggle="modal" data-target="#videoModal"
                                           class="dropdown-item">
                                            <i class="fa fa-play text-danger"></i> {{ trans('custom.watch')
                                            }}
                                        </a>

                                        <a v-else-if="item.url.length > 0 && item.url[0].type == 'mp4'"
                                           :href="item.url[0].url" target="_blank" class="dropdown-item">
                                            <i class="fa fa-play text-danger"></i> {{ trans('custom.watch')
                                            }}
                                        </a>

                                        <a v-for="url in item.url"
                                           v-if="url.type != 'view_url' && url.type != 'youtube' && url.type != 'url'"
                                           :href="url.url" v-on:click="logDownload(url.url)" target="_blank" download
                                           class="dropdown-item">
                                            <i class="fa fa-download text-primary"></i> {{
                                                trans('custom.download') }} .{{url.type}}
                                        </a>


                                        <a v-for="url in item.url"
                                           v-if="url.external_url && (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))"
                                           href="#" v-on:click.prevent="copyExternalUrl(url.external_url)"
                                           download class="dropdown-item">
                                            <i class="fa fa-share text-primary"></i> {{
                                                trans('custom.share') }} .{{url.type}}
                                        </a>

                                        <a v-if="item.external_view && (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))"
                                           href="#"
                                           v-on:click.prevent="copyExternalUrl(framedView(item.external_view), true)"
                                           download class="dropdown-item">
                                            <i class="fa fa-share-alt  text-primary"></i> {{
                                                trans('custom.frame') }}
                                        </a>

                                        <a v-if="item.external_view &&  (allowedDisplay('project_delete', 'project', item.project_id) || allowedDisplay('all'))"
                                           href="#" v-on:click.prevent="copyExternalUrl(item.external_url)"
                                           download class="dropdown-item mobile-view">
                                            <i class="fa fa-ext-share text-primary"></i> {{
                                                trans('custom.share') }}
                                        </a>

                                        <template
                                            v-if="item.status.split('||')[0] === 'custom.item_status_cc_converting'">
                                            <div class="dropdown-item text-center">
                                                        <span v-tooltip="getConvertedTooltipText(item.status)"
                                                              class="d-inline-block align-middle">
                                                            <tile></tile>
                                                        </span>
                                            </div>
                                        </template>
                                        <template v-else>

                                            <a v-on:click.prevent="openConvert(item.id, item.can_convert, index, item.type)"
                                               v-if="item.can_convert.length > 0 && typeof item.urls.url == 'undefined'"
                                               href="#"
                                               data-toggle="modal" data-target="#convertModal"
                                               class="dropdown-item">
                                                <i class="fa fa-random" style="color: #8e44ad;"></i> {{
                                                    trans('custom.convert') }}
                                            </a>

                                            <a href="#" v-if="item.potree_view"
                                               v-on:click.prevent="generatePotree(item.id, index)"
                                               class="dropdown-item">
                                                <i class="fa fa-random" style="color: #8e44ad;"></i>
                                                <template
                                                    v-if="item.view_url !== null && item.view_url.startsWith('/potree/')">
                                                    {{trans('custom.regenerate_potree') }}
                                                </template>
                                                <template v-else> {{trans('custom.generate_potree') }}</template>
                                            </a>

                                        </template>

                                        <router-link
                                            :to="{name: 'editProjectItem', params: {project_id: item.project_id, id: item.id, type: typeof files_types[item.type] != 'undefined'?'file':'document' , company_id: 'all'}}"
                                            class="dropdown-item">
                                            <i class="fa fa-edit text-success"></i> {{
                                                trans('custom.edit') }}
                                        </router-link>

                                        <a v-on:click.prevent="deleteEntryItem(item.id, index, item.project_id)"
                                           href="#" class="dropdown-item">
                                            <i class="fa fa-trash-o bucket_icon"></i> {{
                                                trans('custom.delete') }}
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>

                </tbody>
            </table>

            <div class="m-3" v-if="!(uploading_file.in_process) && file_items.data.length === 0">
                <img class="empty_icon" src="/images/icons/empty_file_icon.png">
                <span class="empty-text">{{trans('custom.no_results_by_your_request')}}</span>
            </div>

            <div id="undo_delete" v-if="delete_progress">
                <div class="progress mb-1 cursor-pointer" v-for="item, index in delete_progress"
                     @click="undoDelete(index, item.id)">
                    <div class="progress-bar progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100"
                         aria-valuemin="0" aria-valuemax="100" :style="{'width': item.progress + '%'}"></div>
                    <h5>{{ item.title }} Deleted <span class="text-success">[UNDO]</span></h5>
                </div>
            </div>
        </div>
        <div class="card items_container" v-else-if="is_allowed_storage">
            <table class="table table-striped" v-if="files.data.length > 0">
                <thead>
                <tr>
                    <th width="5%"></th>
                    <th class="tablet-desktop-view" width="45%"></th>
                    <th class="mobile-view" width="35%"></th>
                    <th class="tablet-desktop-view" width="35%"></th>
                    <th class="mobile-view" width="10%"></th>
                    <th class="mobile-view" width="39%"></th>
                    <th class="text-center tablet-desktop-view"></th>
                </tr>
                </thead>
                <tbody>
                <template v-if="uploading_file.in_process && uploading_file.type =='uploading_temp_storage'">
                    <tr class="text-left h3 bg-secondary font-weight-bold desktop-view uploading_files"
                        :class="{ 'finished': uploading_file.finished}">
                        <td colspan="5">{{ trans('custom.uploading_files') }}</td>
                    </tr>
                    <tr class="text-center h3 font-weight-bold tablet-mobile-view uploading_files"
                        :class="{ 'finished': uploading_file.finished}">
                        <td colspan="5">{{ trans('custom.uploading_files') }}</td>
                    </tr>
                    <tr class="uploading_file">
                        <td class="pt-4 pl-2 desktop-view uploading_files"
                            :class="{ 'finished': uploading_file.finished}">
                            <i :class="'text-dark fa fa-file-other'" style="font-size: 2rem;"></i>
                        </td>

                        <td class="pt-3 pb-0 pl-2 tablet-mobile-view uploading_files"
                            :class="{ 'finished': uploading_file.finished}">
                            <i class='text-dark fa fa-file-other' style="font-size: 2rem;"></i>
                        </td>
                        <td>
                            <div class="name_holder uploading_files" :class="{ 'finished': uploading_file.finished}">
                                <span class="name">{{ uploading_file.title }}</span>
                                <span class="date">  {{ uploading_file.job_done_at.getFullYear()  + "-" + (uploading_file.job_done_at.getMonth()+1) + "-" + (uploading_file.job_done_at.getDate() < 10? '0'+uploading_file.job_done_at.getDate():uploading_file.job_done_at.getDate()) }}</span>
                            </div>
                        </td>
                        <td class="align-middle uploading_files" :class="{ 'finished': uploading_file.finished}">
                            <div class="loading_title">{{trans('custom.loading')}}</div>
                            <div class="progress">
                                <div class="progress-bar"
                                     role="progressbar" :aria-valuenow="uploading_file.percentage" aria-valuemin="0"
                                     aria-valuemax="100" :style="'width:' + uploading_file.percentage + '%'">
                                    {{uploading_file.percentage}}%
                                </div>
                            </div>
                        </td>
                        <td class="align-middle h4 text-center">
                            <button v-if="!uploading_file.finished" class="btn btn-outline-success"
                                    v-on:click.prevent="triggerResetUpload()" type="button">
                                {{ trans('custom.cancel') }}
                            </button>
                            <button v-if="uploading_file.finished" class="btn btn-outline-success"
                                    v-on:click.prevent="loadTempStorage()" type="button">
                                {{ trans('custom.ok') }}
                            </button>
                        </td>
                    </tr>
                </template>
                <template v-if="files.data.length > 0" v-for="item, index in files.data">
                    <tr :class=" 'item_'+index">
                        <td class="pt-4 pl-2 desktop-view">
                            <i :class="'text-dark fa fa-file-other'" style="font-size: 2rem;"></i>
                        </td>
                        <td class="pt-3 pb-0 pl-2 tablet-mobile-view">
                            <i :class="'text-dark fa fa-file-other'" style="font-size: 2rem;"></i>
                        </td>

                        <td class="tablet-desktop-view">
                            <div class="name_holder">
                                <span class="name">{{ item.title }}</span>
                                <div class="bottom_info"><span class="date mr-2">  {{ item.created_at }}</span>
                                    <span class="size mr-2"> <i
                                        class="table-size fa fa-hdd"></i> {{ item.size }}mb</span>
                                </div>
                            </div>
                        </td>
                        <td class="mobile-view">
                            <div class="name_holder">
                                <span class="name">{{ item.title }}</span>
                                <span class="date">  {{ item.created_at }}</span>
                            </div>
                        </td>
                        <td class="align-middle tablet-desktop-view"></td>
                        <td class="align-middle mobile-view">
                            {{ item.size }}mb
                        </td>
                        <td class="align-middle h4 text-center actions">
                            <div class="dropdown d-inline-block">
                                <button class="btn dropdown-toggle btn-outline-success" type="button"
                                        data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                    {{ trans('custom.actions') }}
                                </button>
                                <div class="dropdown-menu">

                                    <a v-if="gallery_extensions.includes(item.ext)" v-on:click.prevent="addToGallery(item)"
                                       href="#" data-toggle="modal" data-target="#moveGaleryModal" class="dropdown-item">
                                       <i class="fa fa-exchange add-to-project"></i> {{ trans('custom.add_to_gallery')
                                        }}
                                    </a>

                                    <a v-else v-on:click.prevent="addToProject(item)"
                                       href="#" class="dropdown-item">
                                        <i class="fa fa-exchange add-to-project"></i> {{ trans('custom.add_to_project')
                                        }}
                                    </a>

                                    <a v-on:click.prevent="deleteTempFile(item.path)"
                                       href="#" class="dropdown-item">
                                        <i class="fa fa-trash-o bucket_icon"></i> {{
                                            trans('custom.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>

                </tbody>
            </table>

            <div class="m-3" v-if="files.data.length == 0">
                <img class="empty_icon" src="/images/icons/empty_file_icon.png">
                <span class="empty-text">{{trans('custom.no_results_by_your_request')}}</span>
            </div>

        </div>





        <div class="row mt-4">
            <div class="col-sm-12">
                <vue-pagination v-if="tab == 'google_disk' || tab == 'file_items'" :pagination="file_items"
                                @paginate="loadItems()"
                                :offset="5">
                </vue-pagination>

                <vue-pagination v-if="tab == 'temporary_space'" :pagination="files"
                                @paginate="loadTempStorage()"
                                :offset="5">
                </vue-pagination>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade styled_modal" id="profileModal" role="dialog" ref="profileModal">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('custom.profile') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="my-3 card p-3">
                            <div class="card-body profile_body text-center">

                                <div class="row mb-3">
                                    <div class="user-avatar-holder">
                                        <img :src="profile_user.picture"  class="img-responsive">
                                    </div>
                                </div>
                                <div class="text-left my-2">
                                    <div class="text-grey"><i class="user_company_icon"></i>{{trans('custom.company')}}:</div>
                                    <div class="text-user-info"><b>{{profile_user.company.title}}</b></div>
                                </div>
                                <div class="text-left my-2">
                                    <div class="text-grey"><i class="user_name_icon"></i>{{trans('custom.name')}}:</div>
                                    <div class="text-user-info"><b>{{profile_user.name}}</b></div>
                                </div>
                                <div class="text-left my-2">
                                    <div class="text-grey"><i class="user_email_icon"></i>{{trans('custom.email')}}:</div>
                                    <div class="text-user-info"><b>{{profile_user.email}}</b></div>
                                </div>
                                <div class="text-left my-2">
                                    <div class="text-grey"><i class="user_role_icon"></i>{{trans('custom.role')}}:</div>
                                    <div class="text-user-info capitalized"><b>{{profile_user.role}}</b></div>
                                </div>
                                <div v-if="profile_user.phone != null" class="text-left my-2">
                                    <div class="text-grey"><i class="user_phone_icon"></i>{{trans('custom.phone')}}:</div>
                                    <div class="text-user-info"><b>{{profile_user.phone}}</b></div>
                                </div>
                                <div v-if="Object.keys(profile_user.detailed_companies).length > 0" class="text-left my-2">
                                    <div class="text-grey"><i class="user_company_icon"></i>{{trans('custom.companies')}}</div>
                                    <div class="values_list text-left" v-for="company, index in profile_user.detailed_companies">
                                        <div class="text-user-info"><i class="user_company_icon"></i><u><b>{{company.title}}</b></u></div>
                                        <div class="text-grey">{{company.role}}</div>
                                    </div>
                                </div>
                                <div v-if="Object.keys(profile_user.detailed_projects).length > 0" class="text-left my-2">
                                    <div class="text-grey"><i class="user_project_icon"></i>{{trans('custom.projects')}}</div>
                                    <div class="values_list text-left" v-for="project, index in profile_user.detailed_projects">
                                        <div class="text-user-info"><i class="user_project_icon"></i><u><b>{{project.title}}</b></u></div>
                                        <div class="text-grey">{{project.role}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add File To Project Modal -->

        <div class="modal fade styled_modal" id="moveFileModal"  role="dialog" ref="moveFileModal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5  class="modal-title">{{ trans('custom.add_to_project') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file mt-0 pt-0">
                        <form v-on:submit="submitAddingFileForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.project') }}*</label>
                                    <v-select
                                        v-model="new_item.project_id"
                                        :options="projects"
                                        label="title"
                                        :autocomplete="is_super_user?'on':'off'"
                                        index="id"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label>
                                    <v-select
                                        v-model="item_operation_type"
                                        :options="adding_to_project_type_options"
                                        label="title"
                                        index="id"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.title') }}*</label>
                                    <input v-model="new_item.title" name="title" type="text"  class="form-control">
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
                                        :options="adding_to_project_file_types"
                                        label="name"
                                        index='value'
                                        :clearable="false"
                                    ></v-select>
                                </div>
                                <div v-if="item_operation_type=='documents'" class="col-sm-6 form-group required">
                                    <label class="control-label">{{ trans('custom.type') }}*</label>
                                    <v-select
                                        v-model="new_item.type"
                                        :options="adding_to_project_doc_types"
                                        label="name"
                                        index='value'
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="text-left mt-1 mb-0">
                                <div class="radio_block"><label for="action_move" class="container">{{ trans('custom.move') }}
                                    <input type="radio" name="add_to_project_action" id="action_move" v-model="adding_to_project_way" v-bind:value="adding_to_project_move_way" checked> <span
                                        class="checkmark"></span></label></div>
                                <div class="radio_block"><label for="action_copy" class="container">{{ trans('custom.copy') }}
                                    <input type="radio" name="add_to_project_action" id="action_copy" v-model="adding_to_project_way" v-bind:value="adding_to_project_copy_way"> <span
                                        class="checkmark"></span></label></div>
                            </div>
                            <div class="way_hint" v-if="adding_to_project_way == adding_to_project_move_way">
                                {{ trans('custom.this_file_will_be_moved_to_a_chosen_project_and_erased_from_this_folder') }}
                            </div>
                            <div class="way_hint" v-if="adding_to_project_way == adding_to_project_copy_way">
                                {{ trans('custom.this_file_will_be_copied_to_a_chosen_project') }}
                            </div>
                            <div class="upload_div mt-2 mb-2">
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <div class="card shadow-none">
                                            <div class="card-horizontal">
                                                <div class="img-square-wrapper position-relative" style="width: 100px;">
                                                    <i class="fa fa-file-other absolute-center w-50 h-50" style="font-size: 50px"></i>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text mb-0"><b>{{ trans('custom.filename') }}: </b><a :href="adding_to_project_file.path" target="_blank">{{ adding_to_project_file.title }}</a></p>
                                                    <p class="card-text mb-0"><b>{{ trans('custom.size') }}: </b> {{ adding_to_project_file.size }}mb</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group dropzone-progress text-left">
                                    <button class="btn btn-primary">
                                        <span v-if="adding_to_project_way == adding_to_project_move_way">{{ trans('custom.move') }}</span>
                                        <span v-if="adding_to_project_way == adding_to_project_copy_way">{{ trans('custom.copy') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade styled_modal" id="moveFileGalleryModal"  role="dialog" ref="moveFileGalleryModal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5  class="modal-title">{{ trans('custom.add_to_gallery') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file mt-0 pt-0">
                        <form v-on:submit="submitAddingFileGalleryForm()">
                            <div class="row">
                                <div class="col-sm-12 form-group required">
                                    <label class="control-label">{{ trans('custom.project') }}*</label>
                                    <v-select
                                        v-model="new_item.project_id"
                                        :options="projects"
                                        label="title"
                                        @change="updateFolders"
                                        :autocomplete="is_super_user?'on':'off'"
                                        index="id"
                                        :clearable="false"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="row" v-if="project_folders.length > 0">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.folder') }}</label>
                                    <v-select
                                        v-model="new_item.folder_id"
                                        :options="project_folders"
                                        label="title"
                                        index="id"
                                        :clearable="true"
                                    ></v-select>
                                </div>
                            </div>
                            <div class="text-left mt-1 mb-0">
                                <div class="radio_block"><label for="action_move" class="container">{{ trans('custom.move') }}
                                    <input type="radio" name="add_to_project_action" id="gallery_action_move" v-model="adding_to_project_way" v-bind:value="adding_to_project_move_way" checked> <span
                                        class="checkmark"></span></label></div>
                                <div class="radio_block"><label for="action_copy" class="container">{{ trans('custom.copy') }}
                                    <input type="radio" name="add_to_project_action" id="gallery_action_copy" v-model="adding_to_project_way" v-bind:value="adding_to_project_copy_way"> <span
                                        class="checkmark"></span></label></div>
                            </div>
                            <div class="way_hint" v-if="adding_to_project_way == adding_to_project_move_way">
                                {{ trans('custom.this_file_will_be_moved_to_a_chosen_project_and_erased_from_this_folder') }}
                            </div>
                            <div class="way_hint" v-if="adding_to_project_way == adding_to_project_copy_way">
                                {{ trans('custom.this_file_will_be_copied_to_a_chosen_project') }}
                            </div>
                            <div class="upload_div mt-2 mb-2">
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <div class="card shadow-none">
                                            <div class="card-horizontal">
                                                <div class="img-square-wrapper position-relative m-3" style="width: 100px;">
                                                    <img :src="'/'+adding_to_project_file.path" class="absolute-center"></img>
                                                </div>
                                                <div class="card-body w-50">
                                                    <p class="card-text mb-0"><b>{{ trans('custom.filename') }}: </b><a :href="adding_to_project_file.path" target="_blank">{{ adding_to_project_file.title }}</a></p>
                                                    <p class="card-text mb-0"><b>{{ trans('custom.size') }}: </b> {{ adding_to_project_file.size }}mb</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group dropzone-progress text-left">
                                    <button class="btn btn-primary">
                                        <span v-if="adding_to_project_way == adding_to_project_move_way">{{ trans('custom.move') }}</span>
                                        <span v-if="adding_to_project_way == adding_to_project_copy_way">{{ trans('custom.copy') }}</span>
                                    </button>
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
import parsePhoneNumber from 'libphonenumber-js';


export default {
    data() {
        return {
            file_items: {
                total: 0,
                per_page: 9,
                from: 1,
                to: 0,
                current_page: 1,
                data: []
            },
            files: {
                total: 0,
                per_page: 2,
                from: 1,
                to: 0,
                current_page: 1,
                data: []
            },
            order_data:{
                'field' : 'created_at',
                'dir' : 'DESC',
            },
            video_iframe: null,
            video_title: null,
            new_item: {
                project_id: '',
                title: '',
                description: '',
                type: '',
                folder_id:0,
                urls: [],
                upload: [],
                job_done_at: new Date()
            },
            is_allowed_storage:false,
            gallery_extensions:['jpeg','png','jpg','gif','svg','bmp', 'jpeg'],
            project_folders:[],
            profile_user: {
                picture:'',
                name: null,
                email: null,
                role: '',
                job_title:'',
                password: null,
                phone: null,
                company:{
                    title:'',
                },
                detailed_companies:{},
                detailed_projects:{},
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
            show_filters:true,
            per_page: 9,
            paginate_options:[
                {name:9},
                {name:21},
                {name:45},
            ],
            item_operation_type :'',
            item_operation_action :'add',
            delete_progress: [],
            files_types:{},
            doc_types:{},
            all_file_types:[],
            uploaders:[],
            adding_to_project_file_types:[],
            adding_to_project_doc_types:[],
            adding_to_project_type_options:[],
            adding_to_project_way:'move',
            adding_to_project_move_way:'move',
            adding_to_project_copy_way:'copy',
            adding_to_project_file:{
                title:'',
                size:'',
                path:'',
                ext:'',
            },
            is_super_user : typeof window.perosnal_permissions.is_super_user == 'undefined'? false:true,
            belongs_to:'all',
            file_type:'all',
            uploader:'all',
            projects: [],
            projects_filt:[],
            type_options:[],
            tab:'file_items',
            uploading_file:{
                in_process:false,
                title:'',
                type:'',
                job_done_at:'',
                percentage:0,
                finished: false,
                item_type:'',
            },
            //curr_company_id: window.company_limits.company_id,
            webdav_login: '',
            webdav_url: '',
            webdav_pass: '',
            pollerIDs: [],
            allowedDisplay: function(action, model = false, id = false) {
                return window.allowedDisplay(action, model, id)
            },
            convert_list: [],
            convert_item_id: null,
            convert_item_index: null,
            convert_cant_convert: false,
            convert_type: '',
            selected_conversion_format: null,
            webdav_instruction_url : '/webdav_instruction.pdf',
            is_webdav_tooltip_open:false,
        }
    },
    components: {
        VuePagination
    },
    mounted() {

        var app = this;
        if (this.$route.query.per_page) {
            this.per_page = this.$route.query.per_page
        } else if (localStorage.getItem('perPage')) {
            this.per_page = localStorage.getItem('perPage');
        }


        axios.get('/api/v1/usersettings', {
            params: {
                'settings_key': 'files_page_settings',
            }
        }).then(resp => {
            this.order_data.field = (typeof resp.data.order_settings.field !== 'undefined') ? resp.data.order_settings.field : 'created_at';
            this.order_data.dir  = (typeof resp.data.order_settings.dir !== 'undefined') ? resp.data.order_settings.dir : 'DESC';
            this.belongs_to = (typeof resp.data.belongs_to !== 'undefined' && resp.data.belongs_to != 0) ? resp.data.belongs_to : 'all';
            this.file_type = (typeof resp.data.file_type !== 'undefined' && resp.data.file_type != 0) ? resp.data.file_type : 'all';
            this.uploader = (typeof resp.data.uploader !== 'undefined' && resp.data.uploader != 0) ? resp.data.uploader : 'all';
            this.load()
        }).catch(() => {
            this.load()
            console.log("Could not load settings")
        });

        axios.get('/api/v1/projectitems/uploaders').then(resp => {
            app.uploaders.push({
                'id': 'all',
                'title': window.trans('custom.uploaders'),
                'list_title': window.trans('custom.all_uploaders')
            });
            for (let key in resp.data) {
                let new_elem = {
                    'id': resp.data[key],
                    'title': key,
                    'list_title': key,
                };
                app.uploaders.push(new_elem);
            }
        }).catch((error) => {
            if (error.response.status !== 401) {
                app.$dialog.alert(window.trans('custom.error_load_uploaders'));
            }
        });

        axios.get('/api/v1/projects/manageable').then(resp => {
            app.projects_filt.push({
                'id': 'all',
                'title': window.trans('custom.projects'),
                'list_title': window.trans('custom.all_projects'),
            });
            for (let key in resp.data) {
                let new_elem = {
                    'id': key,
                    'title': resp.data[key].title,
                    'list_title': resp.data[key].title,
                    'folders':[],
                };
                for(let folder in resp.data[key].folders){
                    new_elem.folders.push({
                        'id' : folder,
                        'title' :  resp.data[key].folders[folder],
                        'list_title' :  resp.data[key].folders[folder]
                    })
                }
                app.projects.push(new_elem);
                app.projects_filt.push(new_elem);
            }
        }).catch((error) => {
            if (error.response.status !== 401) {
                app.$dialog.alert(window.trans('custom.error_load_company'));
            }
        });

        axios.get('/api/v1/potree/types')
            .then((resp) => {
                app.all_file_types.push({
                    value:'all',
                    name:trans('custom.all_types'),
                    title : trans('custom.type'),
                })
                app.files_types = resp.data['files'];
                for (let file_name in resp.data['files']){
                    app.all_file_types.push({
                        value:file_name,
                        name:trans('custom.file_names.' + file_name),
                        title:trans('custom.file_names.' + file_name)
                    })
                }
                app.doc_types = resp.data['documents'];
                for (let doc_name in resp.data['documents']){
                    app.all_file_types.push({
                        value:doc_name,
                        name:trans('custom.file_names.' + doc_name),
                        title:trans('custom.file_names.' + doc_name)
                    })
                }
            })
            .catch(function (data) {
                if (data.response.status !== 401) {
                    app.$dialog.alert("Could not load types")
                }
            });

        axios.get('/api/v1/companies/temp_storage_credentials')
            .then((response) => {
                if (response.data.result){
                    app.is_allowed_storage = true;
                    app.webdav_login = response.data.webdav_login;
                    app.webdav_url = response.data.webdav_url;
                    app.webdav_pass = response.data.webdav_pass;
                } else {
                    app.is_allowed_storage = false;
                }
            });


        $('body').off('load.file.success.dialog');
        $('body').on('load.file.success.dialog', function(event, o){
            if(app.$router.currentRoute.name == 'filesList') {
                window.location.reload();
            }
        });


        $('body').off('modal_params');
        $('body').on('modal_params', function(e, obj){
            app.initiateModalActions(obj);
        });

        $('body').on('load.file.background', function (e, o) {
            switch (o.status) {
                case 'loading':
                    app.uploading_file.title = o.item.title;
                    app.uploading_file.type = o.item.type;
                    app.uploading_file.icon = o.item.icon;
                    app.uploading_file.job_done_at = o.item.job_done_at;
                    app.uploading_file.in_process = true;
                    app.uploading_file.percentage = o.percentage;
                    app.uploading_file.finished = false;
                    app.uploading_file.item_type = o.type;
                    break;
                case 'failed':
                case 'resetted':
                    app.uploading_file.in_process = false;
                    app.uploading_file.finished = false;
                    break;
                case 'finished':
                    app.uploading_file.finished = true;
                    app.uploading_file.in_process = false;
                default:
                    break;
            }
        });

        $('body').off('click', '.copy_webdav_url');
        $('body').on('click', '.copy_webdav_url', function(e){
            var $this = $(this);
            app.copyWevdavUrl();
            $this.parents('div:first').addClass('blinked_wrapp');
            setTimeout(function () {
                $this.parents('div:first').removeClass('blinked_wrapp');
            }, 1000);
        });

        $('body').off('click', '.copy_webdav_login');
        $('body').on('click', '.copy_webdav_login', function(e){
            var $this = $(this);
            app.copyWebdavLogin();
            $this.parents('div:first').addClass('blinked_wrapp');
            setTimeout(function () {
                $this.parents('div:first').removeClass('blinked_wrapp');
            }, 1000);
        });

        $('body').off('click', '.copy_webdav_pass');
        $('body').on('click', '.copy_webdav_pass', function(e){
            var $this = $(this);
            app.copyWevdavPass();
            $this.parents('div:first').addClass('blinked_wrapp');
            setTimeout(function () {
                $this.parents('div:first').removeClass('blinked_wrapp');
            }, 1000);
        });

        $('body').off('click', '.tabs-component-panels .question_tooltip');
        $('body').on('click', '.tabs-component-panels .question_tooltip', function(e){
            app.is_webdav_tooltip_open = !app.is_webdav_tooltip_open;
        });


    },
    destroyed() {
        _.each(this.pollerIDs, pollerID => { clearInterval(pollerID); });
    },
    methods: {
      uploadModal(){
        $('body').trigger('open.modal.file.upload', {name: "item_create", company_id: "all", val: 1, project_id: "choose", type: "choose"});
        $('#uploadModalFileModal').modal('show');
      },
        initiateModalActions(obj){
            var app = this;
            if (typeof obj.name != 'undefined' && obj.name == 'item_create'){
                if (window.is_loading){
                    $('#multiLoadingComingSoonModal').modal('show');
                } else {
                    $('body').trigger('open.modal.file.upload', {type:obj.type, projectId:obj.project_id, companyId:obj.company_id});
                    $('#uploadModalFileModal').modal('show');
                }
            }
            if (typeof obj.name != 'undefined' && obj.name == 'item_edit'){
                if (allowedDisplay('project_manage', 'project', obj.project_id) || allowedDisplay('all')) {
                    $('body').trigger('open.modal.file.edit', {'companyId': 'all', 'projectId':obj.project_id, type:obj.type, edit_id:obj.val});
                    $('#editModalFileModal').modal('show');
                } else {
                    app.$dialog.alert('<div class="mt-4"><img src="/images/popup/create_project_not_allowed.png" style="width: 55%;"></div><div class="text-center mt-4" style="font-size: 22px;line-height: 33px;font-weight:400;">' + window.trans('custom.you_do_not_have_permission_to_access') + '</div>', {
                        'html': true,
                        okText: "Ok",
                    });
                }
            }
        },
        initiateUploadFile(){
            if (window.is_loading){
                $('#multiLoadingComingSoonModal').modal('show');
            } else {
                $('#uploadModalTempStorageModal').modal('show');
            }
        },
        copyWebdavLogin()
        {
            this.copyToBuffer(this.webdav_login);
        },
        copyWevdavUrl()
        {
            this.copyToBuffer(this.webdav_url);
        },
        copyWevdavPass()
        {
            this.copyToBuffer(this.webdav_pass);
        },
        copyToBuffer(text){
            var t_area = document.createElement('textArea');
            t_area.value = text;
            document.body.appendChild(t_area);
            console.log(t_area.innerHTML)
            t_area.select();
            document.execCommand("copy");
            document.body.removeChild(t_area);
        },
        toggleFilterDisplay()
        {
            this.show_filters = !this.show_filters;
        },
        clearFilters()
        {
            this.belongs_to = 'all';
            this.file_type = 'all';
            this.uploader = 'all';
            this.filterFiles();
        },
        openProfileUser(id){
            var app = this;
            axios.get('/api/v1/user/' + id)
                .then(function (resp) {
                    app.profile_user = resp.data;
                }).catch((error) => {
                if (error.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert("Could not load user")
                }
            });
        },
        logDownload(href){
            axios.post('/api/v1/statistics',{
                action:'download_file',
                data:href,
            });
        },
        logPreview(href){
            axios.post('/api/v1/statistics',{
                action:'preview_file',
                data:href,
            });
        },
        framedView(url){
            return '<iframe width="560" height="315" src="'+url+'" frameborder="0" allowfullscreen></iframe>';
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
        dropDownButtonClass(status) {
            if (this.checkItemFailed(status)) {
                return 'btn-outline-danger';
            }

            if (status === 'custom.item_status_success') {
                return 'btn-outline-success';
            }

            return 'btn-outline-warning';
        },
        tabClicked (selectedTab) {
            console.log('Current tab re-clicked:' + selectedTab.tab.name);
        },
        tabChanged (selectedTab) {
            let tempUrl = decodeURI(window.location.hash).split('#')[1];
            this.tab = selectedTab.tab.id.toLowerCase();
            window.location.hash = tempUrl + '#' + selectedTab.tab.id.toLowerCase();
            this.updateUserSettings(selectedTab.tab.id.toLowerCase());
            if (this.tab == 'file_items' || this.tab == 'google_disk'){
                this.loadItems();
            } else {
                this.loadTempStorage();
            }
        },
        load(){
            this.loadTempStorage();
            this.loadItems();
        },
        loadTempStorage(){
            var app = this;
            axios.get('/api/v1/companies/temp_files', {
                params: {
                    'page': this.file_items.current_page,
                    'order_field': this.order_data.field,
                    'order_dir':this.order_data.dir,
                    'per_page':this.per_page,
                }
            }).then(resp => {
                this.files = resp.data;
            }).catch((error) => {
                if (error.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else if (error.response.status == 403){
                    app.is_allowed_storage = false;
                    console.log('No webdav folder for company')
                } else {
                    app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                        '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                        'html': true,
                        okText: "Ok",
                    });
                }
            });
            localStorage.setItem('perPage', app.per_page)
        },
        loadItems() {
            var app = this;
            axios.get('/api/v1/projectitems', {
                params: {
                    'page': this.file_items.current_page,
                    'belongs': this.belongs_to,
                    'type': this.file_type,
                    'uploader': this.uploader,
                    'order_field': this.order_data.field,
                    'order_dir':this.order_data.dir,
                    'per_page':this.per_page,
                    'tab':this.tab,
                }
            }).then(resp => {
                this.file_items = resp.data;

            }).catch((error) => {
                if (error.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                } else {
                    app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                        '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                        'html': true,
                        okText: "Ok",
                    });
                }
            });
            localStorage.setItem('perPage', app.per_page)
        },
        openVideo(embed, title) {
            this.video_iframe = embed;
            this.video_title = title;
        },
        deleteEntryItem(id, index, project_id) {
            var app = this;
            this.$dialog.confirm(window.trans('custom.delete_confirm'))
                .then(function () {
                    axios.delete('/api/v1/projects/' + project_id + '/item/' + id)
                        .then(function (resp) {
                            app.loadItems();

                            const deleted_index = app.delete_progress.push({
                                'id': id,
                                'title': app.file_items.data[index].title,
                                'progress': 200
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
        openConvert(item_id, formats, index, type) {
            this.convert_list = formats;
            this.convert_item_id = item_id;
            this.convert_item_index = index;
            this.convert_cant_convert = (formats[0] === 'cant_convert');
            this.convert_type = type;
        },
        triggerResetUpload(){
            $('body').trigger('reset_upload_fle');
        },
        addToProject(item){
            var app = this;

            app.adding_to_project_doc_types = [];
            app.adding_to_project_file_types = [];
            app.adding_to_project_type_options = [];
            app.adding_to_project_file = item;
            for(var type in app.files_types){
                if (app.files_types[type].types.includes(item.ext)){
                    app.adding_to_project_file_types.push({
                        value:type,
                        name:trans('custom.file_names.' + type)
                    });
                }
            };
            for(var type in app.doc_types){
                if (app.doc_types[type].types.includes(item.ext)){
                    app.adding_to_project_doc_types.push({
                        value:type,
                        name:trans('custom.file_names.' + type)
                    });
                }
            };
            if (app.adding_to_project_file_types.length > 0){
                app.adding_to_project_type_options.push({'title' : trans('custom.files'), 'id': 'files'});
            }
            if (app.adding_to_project_doc_types.length > 0){
                app.adding_to_project_type_options.push({'title' : trans('custom.documents'), 'id': 'documents'});
            }


            if (app.adding_to_project_type_options.length == 0 || app.projects.length == 0){
                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                    '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.you_have_no_project_to_add_or_your_file_doesnt_fit') + '</div>', {
                    'html': true,
                    okText: "Ok",
                });

            } else {
                $('#moveFileModal').modal('show');

            }
        },
        addToGallery(item) {
            var app = this;
            app.adding_to_project_file = item;
            if (app.projects.length == 0){
                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                    '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.you_have_no_project_to_add_or_your_file_doesnt_fit') + '</div>', {
                    'html': true,
                    okText: "Ok",
                });

            } else {
                $('#moveFileGalleryModal').modal('show');
            }

        },
        submitAddingFileForm() {
            event.preventDefault();

            let is_valid = true;
            var app = this;
            $('.form-group.required.error-element').removeClass('error-element');
            $('.required-to-fill').remove();
            $.each($('#moveFileModal .form-group.required'), function (key, wrapper) {
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
            if (!is_valid) {
                return false;
            } else {
                $('#moveFileModal').modal('hide');
                axios.post('/api/v1/companies/storage_file_to_project', {
                    path: app.adding_to_project_file.path,
                    project_id:app.new_item.project_id,
                    delete_source:app.adding_to_project_way == app.adding_to_project_move_way,
                }).then((resp) => {
                    app.new_item.upload.push(resp.data.new_file);
                    let formData = new FormData();
                    var curr_item = app.new_item;
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
                    axios.post('/api/v1/projects/' + app.new_item.project_id + '/item', formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }).then(() => {
                        if (app.adding_to_project_way == app.adding_to_project_move_way) {
                            app.$dialog.alert(trans('custom.your_file_has_been_moved_to_project'));
                        } else {
                            app.$dialog.alert(trans('custom.your_file_has_been_copied_to_project'));
                        }
                        app.load();

                    }).catch(error => {
                        if (error.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                        } else {
                            if (error.response.data && error.response.data.errors) {
                                app.$dialog.alert(window.parseError(error.response.data.errors), {'html': true});
                            } else if (error.response.data) {
                                app.$dialog.alert(window.parseError(error.response.data), {'html': true});
                            }
                        }
                    });
                }).catch(error => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        if (error.response.status == 403){
                            $('body').trigger('overlimit.membership',error.response.data);
                        } else {
                            axios.post('/api/v1/alert/', {
                                'trans': 'log_failed_upload_file',
                                'company_id': app.curr_company_id,
                            }).then(() => {
                                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                                    '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                                    'html': true,
                                    okText: "Ok",
                                });
                            });
                        }
                    }
                });
            }

        },
        updateFolders(event){
            var app = this;
            app.project_folders=[];
            for(let key in app.projects){
                if (event == app.projects[key].id){
                    for(let f_key in app.projects[key].folders){
                        app.project_folders.push({
                            id:app.projects[key].folders[f_key].id,
                            title:app.projects[key].folders[f_key].title,
                        })
                    }
                }
            }
        },
        submitAddingFileGalleryForm()
        {
            event.preventDefault();

            let is_valid = true;
            var app = this;
            $('.form-group.required.error-element').removeClass('error-element');
            $('.required-to-fill').remove();
            $.each($('#moveFileGalleryModal .form-group.required'), function (key, wrapper) {
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
            if (!is_valid) {
                return false;
            } else {
                $('#moveFileGalleryModal').modal('hide');
                axios.post('/api/v1/companies/storage_file_to_gallery', {
                    path: app.adding_to_project_file.path,
                    project_id:app.new_item.project_id,
                    folder_id:app.new_item.folder_id,
                    delete_source:app.adding_to_project_way == app.adding_to_project_move_way,
                }).then((resp) => {
                    if (app.adding_to_project_way == app.adding_to_project_move_way) {
                        app.$dialog.alert(trans('custom.your_file_has_been_moved_to_project'));
                    } else {
                        app.$dialog.alert(trans('custom.your_file_has_been_copied_to_project'));
                    }
                    app.load();
                }).catch(error => {
                    if (error.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        if (error.response.status == 403){
                            $('body').trigger('overlimit.membership',error.response.data);
                        } else {
                            axios.post('/api/v1/alert/', {
                                'trans': 'log_failed_upload_file',
                                'company_id': app.curr_company_id,
                            }).then(() => {
                                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                                    '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                                    'html': true,
                                    okText: "Ok",
                                });
                            });
                        }
                    }
                });
            }
        },
        deleteTempFile(path) {
            var app = this;
            this.$dialog.confirm(window.trans('custom.delete_confirm'))
                .then(function () {
                    axios.post('/api/v1/companies/delete_storage_file', {
                        path: path
                    }).then((resp) => {
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.file_deleted_successfully'), {
                            'html': true,
                            okText: 'ok'
                        });
                        app.loadTempStorage();
                    })
                        .catch(function (resp) {
                            if (resp.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                            } else {
                                app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                                    '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                                    'html': true,
                                    okText: "Ok",
                                });
                            }
                        });
                }).catch(function () {
                console.log('Clicked on cancel')
            });
        },
        getConvertedTooltipText(status) {
            const data = status.split("||");

            if (data.length > 1) {
                return trans(data[0]) + " ." + data[1]
            }
            return data[0]
        },
        startConvertion(e) {
            var app = this;
            var item_id = app.convert_item_id;
            var format = app.selected_conversion_format;
            var convert_type = app.convert_type;
            app.convert_item_id = null;
            app.selected_conversion_format = null;

            axios.post('/api/v1/statistics',{
                action:'launch_conversion',
                data:convert_type + ' to ' + format,
            });

            if (!(item_id && format && convert_type)) {
                return
            }

            app.file_items[app.convert_item_index].status = 'custom.item_status_cc_converting||' + format;
            $('#convertModal').modal('hide');

            axios.post('/api/v1/projects/' + app.file_items.data[app.convert_item_index].project_id + '/item/' + item_id + '/convert', {format: format, file_type: convert_type})
                .then(function () {
                    app.startConversionPoller(item_id);
                })
                .catch(function (resp) {
                    app.file_items.data[app.convert_item_index].status = '';
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not convert this item");
                    }
                })
        },
        startConversionPoller(item_id, interval = 2000) {
            var app = this;
            const pollerID = setInterval(() => {
                axios.head('/api/v1/projects/' + app.file_items.data[app.convert_item_index].project_id + '/item/' + item_id + '/convert/status')
                    .then((response) => {
                        if (response.headers.converting !== '1') {
                            axios.get('/api/v1/projects/' + app.file_items.data[app.convert_item_index].project_id).then(response => (this.project = response.data));
                            clearInterval(pollerID);
                        }
                    });
            }, interval);
            this.pollerIDs.push(pollerID);
        },
        generatePotree(id, file_index) {
            var app = this;
            axios.get('/api/v1/projects/' + app.file_items.data[file_index].project_id + '/item/' + id + '/generate_potree')
                .then((resp) => {
                    app.file_items.data[file_index].status = 'custom.item_status_cc_converting';
                    app.startConversionPoller(id);
                })
                .catch(function (resp) {
                    if (resp.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                    } else {
                        app.$dialog.alert('<div class="mt-4"><img src="/images/popup/demo_project_operation_limit.png" style="width: 55%;"></div>' +
                            '<div style="font-size: 1rem;font-weight: 400;line-height: 1.5rem;text-align: justify;margin-top:2rem;">' + trans('custom.oops_something_went_wrong_our_team_is_already') + '</div>', {
                            'html': true,
                            okText: "Ok",
                        });
                    }
                });
        },
        restoreItem(id, project_id) {
            var app = this;

            axios.put('/api/v1/projects/' + project_id+ '/item/' + id + '/restore')
                .then(function (resp) {
                    app.loadItems();
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
                            app.loadItems();
                        })
                        .catch(function (resp) {
                            if (resp.response.status === 401) {
                                window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert("Could not delete item");
                            }
                        });
                })
                .catch(function () {
                    console.log('Clicked on cancel')
                });
        },
        undoDelete(index, id, project_id) {
            this.delete_progress.splice(index, 1);
            this.restoreItem(id, project_id);
        },
        deleteFinalProject(id) {
            var app = this;
            app.$dialog.confirm(window.trans('custom.attention_project_will_be_removed_with_all_files_without_ability_to_restore'))
                .then(function () {
                    axios.delete('/api/v1/projects/' + id + '/force').then(() => {
                        app.loadItems();
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_been_deleted'), {
                            'html': true,
                            okText: 'ok'
                        });
                    }).catch(() => {
                        app.$dialog.alert(window.trans('custom.delete_error'));
                    });
                })
                .catch(function () {
                    console.log('Clicked on cancel')
                });

        },
        restoreProject(id){
            var app = this;
            axios.put('/api/v1/projects/' + id + '/restore')
                .then(function (resp) {
                    app.loadItems();
                    app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_been_restored'), {
                        'html': true,
                        okText: 'ok'
                    });
                }).catch((data) => {
                if (data.response.status === 401) {
                    window.location.href = '/logouted/' + 'custom.session_is_over/' + window.activeLanguage;
                } else if (data.response.status == 403) {
                    $('body').trigger('overlimit.membership', data.response.data);
                } else {
                    app.$dialog.alert("Could not restore item");
                }
            });
        },
        filterFiles()
        {
            this.load();
        },
        updateUserSettings() {
            axios.put('/api/v1/usersettings', {
                'settings_key': 'files_page_settings',
                'settings_value': {
                    'order_settings': this.order_data,
                    'belongs_to': this.belongs_to,
                    'role': this.role,
                    'tab':this.tab,
                },
            }).then(function (resp) {
                console.log('View settings updated');
            }).catch(function (resp) {
                console.log('Failed to update View settings');
            });
        },
        formatDate(string) {
            if (string != null) {
                var d = new Date(string.replace(/-/g, "/")),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;

                return [year, month, day].join('-');
            } else {
                return '';
            }
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

    }
}
</script>
