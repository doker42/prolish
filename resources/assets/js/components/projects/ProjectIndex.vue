<template xmlns="http://www.w3.org/1999/html">
    <div class="project_index">
        <div class="row  mb-1">
            <div class="col-sm-12 col-md-8">
                <span class="special_title mb-0 d-inline align-middle">{{ companyName }}</span>
            </div>
        </div>

        <div class="row map-cont">
            <div class="col-sm-12 mt-5">
                <mapbox
                        access-token="pk.eyJ1Ijoia2lrdW5jaGlrIiwiYSI6ImNqczBkZjJ1MjFkZmwzeW0xa2J6am92ZmgifQ.dtB6550NNaHP-ovRadTWsQ"
                        :map-options="{
                            style: 'mapbox://styles/kikunchik/cjs0dfrwe06pn1fpacsegdype',
                            center: [21.426,56.300],
                            zoom: 4.5,
                            scrollZoom: false
                        }"
                        :nav-control="{
                              show: false,
                              showZoom: true,
                              showCompass: false
                        }"
                        @map-load="onMapReady"
                        @map-click="mapClicked"
                        @map-mousemove="mapMouseMoved"
                        v-if="showMap"
                        ref="projectMap"
                ></mapbox>
            </div>
        </div>

        <!-- <div class="row">
            <div class="col-sm-12 col-md-7 mt-3 text-right">

               <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-info active" @click="changeView('list')">
                        <input type="radio" name="options" autocomplete="off"> <i class="fa fa-list-ul"></i>
                    </label>
                    <label class="btn btn-info" @click="changeView('column')">
                        <input type="radio" name="options" autocomplete="off"> <i class="fa fa-th"></i>
                    </label>
                </div>
            </div>-->

        <div class="row">
            <div class="col-sm-8 mt-3">
                <div class="input-group search_input">
                    <input type="text" class="form-control" placeholder="Search" v-model="search" @keyup="searchProject()">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="search_icon"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row  mb-4">
            <div class="col-sm-4 mt-3">
                <span class="inp_title">{{ trans('custom.filter') }}</span>
                <v-select
                        v-if="companies.length > 1"
                        :placeholder="trans('custom.belongs_to')"
                        :options="companies"
                        :clearable="false"
                        label="list_title"
                        index="id"
                        v-model="companyName"
                        @input="switchCategory"
                >
                    <template v-slot:option="option">
                        <hr v-if="option.divider" class="mb-1 mt-0" />
                        <span v-if="!option.divider">{{ option.list_title }}</span>
                    </template>
                </v-select>
            </div>
            <div class="col-sm-4 mt-3">
                <span class="inp_title">{{ trans('custom.sort_by') }}</span>
                <v-select v-model="sort"
                          @input="sortProjects"
                          :options="sort_options"
                          :clearable="false"
                          label="title"
                          index="value">
                </v-select>
            </div>
            <div class="col-sm-2"></div>
            <div class="col-sm-2">
                <div class="btn-group create_project" v-if="allowed_create">
                    <a v-on:click.prevent="openCreateProject()" href="#" data-toggle="modal" data-target="#editProjectModal" class="btn btn-primary">{{ trans('custom.create_new') }}</a>
                </div>
            </div>
        </div>

        <template v-if="!projects.data.length">
            <div class="row">
                <div class="col-sm-12 text-center mt-4">{{ trans('custom.no_project_found') }}</div>
            </div>
        </template>

        <template v-else>

            <!-- List view -->
            <div v-if="view_type == 'list'" style="min-height: 60vh">

                <div class="card my-3 transfer_request" v-for="transfer, index in active_transfer_list">
                    <div class="card-body">


                        <div class="row">
                            <div class="text-md-left text-center card-img-wrapper">
                                <a href="#"><img :src="transfer.project.image" class="img-fluid img-thumbnail opacity05"/></a>
                            </div>
                            <div class="row project_card_content">
                                <div class="col-sm-12 col-md-8 text-md-left text-center pl-5 opacity05">
                                    <span class="h3 text-dark">{{ transfer.project.title }}</span><span class="text-lowercase ml-3 h4">(*{{trans('custom.transfer_request')}})</span>

                                    <p class="mt-3 mb-1">
                                    <div class="address_icon cont-icon"></div>
                                    {{ transfer.project.address }}</p>

                                    <p class="mt-3 mb-1">{{ transfer.project.description }}</p>
                                </div>
                                <div class="col-sm-12 col-md-2 text-md-left text-center opacity05">
                                    <img :title="transfer.project.company.title" :src="transfer.project.company.logo"
                                         style="max-height: 50px; max-width:150px; vertical-align: text-bottom;"/>
                                    <div class="storage_info mt-3">
                                        <div class="storage_icon"></div>
                                        {{transfer.project.size_gb}} GB
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-2 text-md-right text-center">
                                    <div class="project_actions_holder">
                                        <a class="btn btn-xs btn-success font-weight-bold text-white"
                                           v-on:click.prevent="processRequest(transfer.id, 'approve', transfer.cast_type)">
                                            {{trans('custom.approve')}}
                                        </a>
                                        <span class="btn btn-default btn-outline-danger font-weight-bold " v-on:click.prevent="processRequest(transfer.id, 'decline',transfer.cast_type)">
                                           {{trans('custom.decline')}}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row files_cont_row opacity05">
                            <div class="col-sm-12 col-md-12 text-md-right text-center files_cont">
                                <span>
                                    <h3 class="d-inline align-text-top" v-for="type, files in transfer.project.summary">
                                        <i :class="'fa text-dark '+ files"></i> <span>{{ type.length }}</span>
                                    </h3>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card my-3" v-for="project, index in projects.data">
                    <div class="card-body">
                        <div class="row" v-if="project.deleted_at === null">
                            <div class="text-md-left text-center card-img-wrapper">
                                 <span class="btn btn-sm button-favourite btn-outline-warning" v-on:click="favouriteProject(project.id, index)">
                                        <i class="fa"
                                           v-bind:class="{ 'fa-star': project.favourite, 'fa-star-o': !project.favourite }"></i>
                                    </span>
                                <router-link
                                        :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}">
                                    <img :src="project.image" class="img-fluid img-thumbnail"/></router-link>
                            </div>
                            <div class="row project_card_content">
                                <div class="col-sm-12 col-md-8 text-md-left text-center pl-5">
                                    <router-link
                                            :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}"
                                            class="h3 text-dark">{{ project.title }}
                                    </router-link>

                                    <p class="mt-3 mb-1">
                                    <div class="address_icon cont-icon"></div>
                                    {{ project.address }}</p>

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
                                        <a v-if="project.permissions.includes('project_manage')"
                                           v-on:click.prevent="openAddfile(project.id)" href="#"
                                           class="font-weight-bold text-white btn btn-success" data-toggle="modal"
                                           data-target="#addFileModal"><i class="add_file_icon"> </i> {{
                                            trans('custom.add_file') }}</a>
                                        <router-link
                                                :to="{name: 'editProjectVisibility', params: {id: project.id, company_id: project.company_id}}"
                                                v-if="project.permissions.includes('project_manage')"
                                                class="btn btn-xs btn-default btn-outline-success">
                                            <i class="add_user_icon"></i>
<!--                                            <i class="add_to_team_iconbtn_white"></i>-->
                                            <template v-if="project_visibility[project.id] != undefined">
                                                <span class="user_counter"> {{ project_visibility[project.id] }}</span>
                                            </template>
                                            {{ trans('custom.share') }}
                                        </router-link>

                                        <div class="dropdown"
                                             v-if="project.personal_visibility!= undefined || project.permissions.includes('project_manage') || project.permissions.includes('project_delete')">
                                            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                {{ trans('custom.actions') }}
                                            </button>
                                            <div class="dropdown-menu">
                                                <a v-on:click.prevent="deleteVisibility(project.id, index)"
                                                      v-if="project.personal_visibility!= undefined"><i class="leave_icon"></i>{{ trans('custom.leave') }}</a>
                                                <a v-if="project.permissions.includes('project_transfer')"
                                                   v-on:click.prevent="openTransferProject(project.id)" href="#" class=""
                                                   data-toggle="modal" data-target="#transferProjectModal"><i class="transfer_icon"></i>{{
                                                    trans('custom.transfer') }}</a>
                                                <a v-if="project.permissions.includes('project_manage')"
                                                   v-on:click.prevent="openEditProject(project.id)" href="#" class=""
                                                   data-toggle="modal" data-target="#editProjectModal"><i class="edit_icon"></i>{{
                                                    trans('custom.edit') }}</a>
                                                <a v-on:click.prevent="openDeleteMenu(project.id, index)"
                                                   v-if="project.permissions.includes('project_delete')" href="#"
                                                   data-toggle="modal" data-target="#deleteModal"><i class="delete_icon"></i>
                                                    {{ trans('custom.delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row" v-else>
                            <div class="text-md-left text-center card-img-wrapper">
                                <a href="#"><img :src="project.image" class="img-fluid img-thumbnail"/></a>
                            </div>
                            <div class="row project_card_content">
                            <div class="col-sm-12 col-md-8 text-md-left text-center pl-5">
                                <span class="h3 text-dark">{{ project.title }}</span>

                                <p class="mt-3 mb-1">
                                <div class="address_icon cont-icon"></div>
                                {{ project.address }}</p>

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
                                         <a class="btn btn-xs btn-success font-weight-bold text-white"
                                             v-on:click.prevent="restoreEntry(project.id, index)"
                                               v-if="project.permissions.includes('project_delete')">
                                    <i class="fa fa-history restore_white_icon"></i>{{trans('custom.restore')}}
                                </a>
                                    <a class="btn btn-default btn-outline-dark font-weight-bold " v-on:click.prevent="deleteFinalEntry(project.id, index)"
                                          v-if="project.permissions.includes('project_delete')">
                                    <i class="fa fa-trash-o bucket_icon" style="font-size:18px"></i>{{trans('custom.delete')}}
                                </a>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="row files_cont_row" v-if="project.deleted_at === null">

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
                        <div class="row files_cont_row" v-else>
                            <div class="col-sm-12 col-md-12 text-md-right text-center files_cont">
                                <span>
                                    <h3 class="d-inline align-text-top" v-for="type, files in project.summary">
                                        <i :class="'fa text-dark '+ files"></i> <span>{{ type.length }}</span>
                                    </h3>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column view -->
            <div class="row" v-if="view_type == 'column'" style="min-height: 60vh">
                <div class="col-md-4 col-sm-12 my-3 project-columns" v-for="project, index in projects.data">
                    <div class="card" v-if="project.deleted_at === null">
                        <div class="project-thumbnail card-img-top">
                            <router-link :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}"><div :style="{'background-image': 'url(' + project.image + ')'}"></div></router-link>
                        </div>
                        <div class="card-body">
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" class="h5" :title="project.title">
                                {{ project.title }}
                            </div>

                            <hr class="mt-1 mb-2" />

                            <div class="row pt-3">
                                <div class="col-sm-9">
                                    <img :title="project.company.title" :src="project.company.logo" style="max-height: 37px;max-width:55%" />
                                </div>

                                <div class="col-sm-3 text-right">
                                    <router-link :to="{name: 'projectItems', params: {id: project.id, company_id: companyId}}" style="margin-left: -20px;">
                                        <span class="btn btn-success">
                                            {{ trans('custom.open') }}
                                        </span>
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" v-else>
                        <div class="project-thumbnail card-img-top item-archived">
                            <div :style="{'background-image': 'url(' + project.image + ')'}"></div>
                        </div>
                        <div class="card-body">
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; " class="h5 item-archived" :title="project.title">
                                {{ project.title }}
                            </div>

                            <hr class="mt-1 mb-2" />

                            <div class="row pt-3">
                                <div class="col-sm-7">
                                    <img :title="project.company.title" :src="project.company.logo" style="max-height: 37px;max-width:55%" />
                                </div>

                                <div class="col-sm-5 text-right">

                                    <span class="btn btn-xs btn-success btn-info btn-active" v-on:click="restoreEntry(project.id, index)" v-if="project.permissions.includes('project_delete')">
                                    <i class="fa fa-history"></i>
                                    </span>
                                    <span class="btn btn-danger" v-on:click="deleteFinalEntry(project.id, index)"
                                          v-if="project.permissions.includes('project_delete')">
                                    <i class="fa fa-close" style="font-size:18px"></i>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

            <div class="row">
                <div class="col-sm-12 col-md-11">
                    <vue-pagination  :pagination="projects"
                                     @paginate="load()"
                                     :offset="5"
                                     :scrolltop="true">
                    </vue-pagination>
                </div>

                <div class="col-sm-12 col-md-1">
                    <select class="form-control ml-1" v-model="per_page" @change="load">
                        <option value="9">9</option>
                        <option value="21">21</option>
                        <option value="45">45</option>
                    </select>
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
                            <div class="align-bottom">
                                <button @click="deleteEntry(deleting_id, deleting_index)"
                                        type="button"
                                        data-dismiss="modal"
                                        class="btn btn-warning mr-2">{{ trans('custom.move_to_trash') }}
                                </button>
                                <button @click="deleteFinalEntry(deleting_id, deleting_index)"
                                        type="button"
                                        data-dismiss="modal"
                                        class="btn btn-danger ml-2">{{ trans('custom.force_delete') }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        <!-- Edit Project Modal -->
        <div class="modal fade styled_modal" id="editProjectModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="editing_project.id == null" class="modal-title">{{ trans('custom.create_new') }}</h5>
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
                                    <input v-validate="'required'" name="title" type="text" v-model="editing_project.title" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.description') }}*</label>
                                    <textarea v-model="editing_project.description" class="form-control" rows="2d-inline align-text-top"></textarea>
                                </div>
                            </div>
                            <div class="row" v-if="proj_companies.length > 1">
                                <div class="col-sm-12 form-group">
                                    <label class="control-label">{{ trans('custom.belongs_to_company') }}*</label>
                                    <v-select
                                            v-model="editing_project.company_id"
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
                                    <places v-validate="'required'" type="text" v-model="editing_project.address" class="form-control" required
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
                                    <input type="hidden" v-model="editing_project.geo_point"/>
                                </div>
                            </div>

                            <!-- <div class="row">
                                 <div class="col-sm-6 form-group">
                                     <label class="control-label cursor-pointer">
                                         {{ trans('custom.public') }}
                                         <a :href="publicUrl" target="_blank">({{ trans('custom.preview') }})</a>
                                     </label>
                                     <v-select
                                             v-model="editing_project.public"
                                             :options="editing_publiched"
                                             label="title"
                                             index="value"
                                     ></v-select>
                                 </div>
                                 <div class="col-sm-6 form-group">
                                  <label class="control-label">{{ trans('custom.status') }}</label>
                                     <v-select
                                             v-model="editing_project.status"
                                             :options="editing_status"
                                             label="title"
                                             index="value"
                                     ></v-select>
                                </div>
                            </div>-->
                            <div class="upload_styled">
                            <simple-file-upload :setImage="editing_project.image" @uploaded="imageProjectUploaded"></simple-file-upload>
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
        <div class="modal fade styled_modal" id="addFileModal"  role="dialog" ref="vuemodal" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5  class="modal-title">{{ trans('custom.add_file') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body add_file">
                        <form v-on:submit="submitAddingFileForm()">
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
                                <div class="col-sm-6 form-group">
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
                            </div>
                            <div class="upload_div" v-if="selectedType !== null">
                                <div class="row styled_tabs" v-for="type, type_index in this.mergeTypes(selectedType.types)">
                                    <div class="col-sm-12 form-group">
                                        <template v-if="simpleInput(type.join(', '))">
                                            <label v-if="type.join(', ') === 'youtube'" class="control-label mt-4"><b>YouTube/Vimeo</b>
                                                shareable link</label>
                                            <label v-if="type.join(', ') === 'view_url'" class="control-label mt-4">View url</label>

                                            <input :name="'url[' + item.type + ']'"
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
                                                                  v-on:vdropzone-error="uploadErrorHandle"
                                                                  v-on:vdropzone-file-added="fileAdded"
                                                                  :include-styling="false"
                                                                  :ref="'dropzone_' + type_index" :id="'dropzoneId_' + type.join('_')"
                                                                  :options="dropzoneOptions(type.join(','), type_index)"></vue-dropzone>
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

    </div>

</template>

<script>
    import VuePagination from '../Pagination';
    import Places from 'vue-places'
    import vue2Dropzone from "vue2-dropzone";
    import TextFormInputMixin from "../../../vendor/vue-forms/js/mixins/TextFormInputMixin";

    export default {
        data: function () {
            return {
                companyId: null,
                companyName: null,
                companies: [],
                proj_companies:[],
                companies_transfer:[],
                projects: {
                    total: 0,
                    per_page: 2,
                    from: 1,
                    to: 0,
                    current_page: 1,
                    data: []
                },
                allowed_create: false,
                view_type: 'list',
                allowedDisplay: function(action, model = false, id = false) {
                    return window.allowedDisplay(action, model, id)
                },
                sort: 'created_at:desc',
                sort_options :[
                    {title:trans('custom.sort_by_title_asc'),
                        value: 'title:asc'},
                    {title:trans('custom.sort_by_title_desc'),
                        value: 'title:desc'},
                    {title:trans('custom.sort_by_created_desc'),
                        value: 'created_at:desc'},
                    {title:trans('custom.sort_by_created_asc'),
                        value: 'created_at:asc'},
                ],
                showMap: false,
                per_page: 9,
                firstTime: true,
                search: '',
                searchDelay: null,
                searchMemory: '',
                project_visibility: [],
                deleting_index: null,
                deleting_id: null,
                modalShow: true,
                editing_project:{},
                publicUrl: window.publicUrl,
                files_types:{},
                files_types_arr:[],
                new_item: {
                    project_id: '',
                    title: '',
                    description: '',
                    type: '',
                    urls: [],
                    upload: [],
                    job_done_at: new Date()
                },
                selectedType: null,
                submitted: false,
                uploadPercentage: 0,
                uploadPercentage_tech: 0,
                totalFiles: 0,
                uploadProgresses: [],
                adding_file_project_id:null,
                adding_file_company_id:null,
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
                active_transfer_list:[],
            }
        },
        components: {
            TextFormInputMixin,
            VuePagination,
            Places,
            vueDropzone: vue2Dropzone
        },
        mounted() {
            var app = this;
            if (this.$route.query.per_page) {
                this.per_page = this.$route.query.per_page
            } else if (localStorage.getItem('perPage')) {
                this.per_page = localStorage.getItem('perPage');
            }

            axios.get('/api/v1/companies/verified').then((resp) => {

                for (let key in resp.data) {
                    app.proj_companies.push({
                        'id': resp.data[key].id,
                        'title': resp.data[key].title,
                    });
                }

            }).catch((response) => {
               if (response.response.status === 401){
                   window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
               } else {
                   this.$dialog.alert("Could not load companies");
               }
            });

            axios.get('/api/v1/transfer').then((resp) => {
                for (let key in resp.data) {
                    app.active_transfer_list = resp.data;
                }
            }).catch((response) => {
                if (response.response.status !== 401) {
                    app.$dialog.alert("Could not load transfers");
                }
            });

            axios.get('/api/v1/companies/all_verified').then((resp) => {
                for(let key in resp.data){
                    app.companies_transfer.push({
                        'id' : resp.data[key].id,
                        'title' : resp.data[key].title,
                    });
                }
            }).catch((response) => {
                if (response.response.status !== 401) {
                    app.$dialog.alert("Could not load companies");
                }
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
                })
                .catch(function (response) {
                    if (response.response.status !== 401) {
                        this.$dialog.alert("Could not load types")
                    }
                });

            this.projects.current_page = this.$route.query.page ? this.$route.query.page : 1;

            axios.get('/api/v1/user/companies').then(resp => {
                this.companies = resp.data;
                var accesssed_companies_ids = [];
                for(let index in this.companies){
                    if (typeof this.companies[index].id !== 'undefined'){
                        accesssed_companies_ids.push(this.companies[index].id);
                    }
                }
                axios.get('/api/v1/usersettings?settings_key=projects_page_settings').then(resp => {

                    if (typeof resp.data !== 'undefined' && typeof resp.data.show_welcome_intro !== 'undefined') {
                        app.$dialog.alert('<div class="mt-4"><img src="/images/email/welcome_to_3dcloud_email.png"></div><div class="text-center mb-4 mt-4" style="font-size:32px;font-weight:400;">' + trans('custom.wellcome_popup_subject') + '</div>' +
                            '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">' + trans('custom.wellcome_popup_text1') + '</div>', {
                            'html': true,
                            okText: trans('custom.next'),
                        }).then(function () {
                            app.$dialog.alert('<div class="mt-4"><img src="/images/email/welcome_to_3dcloud_email.png"></div><div class="text-center mb-4 mt-4" style="font-size:32px;font-weight:400;">' + trans('custom.wellcome_popup_subject') + '</div>' +
                                '<div style="font-size: 16px;font-weight: 400;line-height: 24px;text-align: justify;">'+ trans('custom.wellcome_popup_text2') + '</div>', {
                                'html': true,
                                okText: 'Ok',
                            });
                        });
                    }
                    let field = (typeof resp.data.order_settings.field !== 'undefined') ? resp.data.order_settings.field : 'created_at';
                    let dir = (typeof resp.data.order_settings.dir !== 'undefined') ? resp.data.order_settings.dir : 'desc';
                    this.sort = field + ':' + dir;
                    this.view_type = (typeof resp.data.view_type !== 'undefined') ? resp.data.view_type : 'list';
                    this.showMap = (typeof resp.data.showMap !== 'undefined') ? resp.data.showMap : true;
                    this.companyId = (typeof resp.data.companyId !== 'undefined' && accesssed_companies_ids.indexOf(resp.data.companyId) > -1) ? resp.data.companyId : this.$route.params.id;
                    this.switchCategory(this.companyId);
                    $('.map_item').removeClass('disabled');
                    if (this.showMap){
                        $('.map_item').addClass('active');
                    }
                }).catch(() => {
                    this.switchCategory(this.$route.params.id);
                    console.log("Could not load settings")
                });
            }).catch((response) => {
                if (response.response.status !== 401) {
                    app.$dialog.alert("Could not load user companies");
                }
            });

            axios.put('/api/v1/usersettings', {
                'settings_key': 'pre_project_page',
                'settings_value': '/'+decodeURI(window.location.hash),
            }).then(function (resp) {
                console.log('View settings updated');
            }).catch(function (resp) {
                console.log('Failed to update View settings');
            });

            $('body').on('click', '.map_item:not(.disabled)', function(){
                app.mapSwitch();
            });

            $('body').on('click', '.custom_upload_btn', function(){
                $(this).parents('.upload_styled:first').find('input[type="file"]:first').click();
            });

            $('body').on('click', '.dz-default.dz-message', function(){
                $(this).parents('#upload-file').find('.dz-clickable:first').click();
            });

        },
        methods: {
            load() {
                this.$router.replace(`/${this.companyId}/projects?per_page=${this.per_page}&page=${this.projects.current_page}`);
                var app = this;
                axios.get(`/api/v1/projects?${this.buildQuery()}`).then(resp => {
                    this.projects = resp.data;

                    axios.get('/api/v1/projects/visibility_counter', {
                        params: {
                            ids: this.projects.data.map(item => item.id)
                        }
                    }).then(resp => {
                        this.project_visibility = resp.data;
                    })
                }).catch((response) => {
                    if (response.response.status === 401){
                        window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.trans('custom.error_load_project'));
                    }
                });

                localStorage.setItem('perPage', this.per_page)
            },
            imageProjectUploaded(file) {
                this.editing_project.image = file;
            },
            changeEditingAddress(e) {
                if (e.latlng) {
                    this.editing_project.geo_point = e.latlng;
                    this.editing_geo = e.latlng;
                }
            },
            sortProjects(){
                this.updateUserSettings();
                this.load();
            },
            deleteVisibility(project_id, index){
                var app = this;
                app.$dialog.confirm(window.trans('custom.are_you_sure_you_want_to_permanently_leave_this_project'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + project_id + '/visibility_leave').then((response) => {
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>'
                                + response.data.description, {
                                'html': true,
                                okText: 'ok'
                            }).then(() => {
                                app.projects.data.splice(index, 1);
                                app.updateCompanies();
                            });

                        }).catch((response) => {
                            if (response.response.status === 401){
                                window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
                            } else {
                                app.$dialog.alert('Error occured. Try Again later.');
                            }
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            updateUserSettings() {
                axios.put('/api/v1/usersettings', {
                    'settings_key': 'projects_page_settings',
                    'settings_value': {
                        'order_settings': {
                            field:this.sort.split(':')[0],
                            dir:this.sort.split(':')[1],
                        },
                        'view_type': this.view_type,
                        'showMap': this.showMap,
                        'companyId': this.companyId,
                    },
                }).then(function (resp) {
                    console.log('View settings updated');
                }).catch(function (resp) {
                    console.log('Failed to update View settings');
                });
            },
            updateCompanies() {
                var app = this;
                axios.get('/api/v1/user/companies').then(resp => {
                    this.companies = resp.data;
                }).catch((response) => {
                    if (response.response.status === 401){
                        window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not load user companies");
                    }
                });
            },
            switchCategory(value) {
                if (!this.firstTime) this.projects.current_page = 1;

                this.companyId = value;
                this.companyName = trans('custom.loading');

                if (this.companyId == 'shared') {
                    this.companyName = trans('custom.shared_with_me');
                } else if (this.companyId == 'all') {
                    this.companyName = trans('custom.all_projects');
                } else if (this.companyId == 'favourite') {
                    this.companyName = trans('custom.favourite');
                }  else if (this.companyId == 'archived') {
                    this.companyName = trans('custom.recycle_bin');
                }  else {
                    for (const index in this.companies) {
                        if(this.companies[index].id == this.companyId){
                            this.companyName = this.companies[index].title;
                        }
                    }
                }
                var app = this;
                axios.get('/api/v1/projects/check_create').then(resp => {
                    this.allowed_create = resp.data;
                }).catch((response) => {
                    if (response.response.status === 401){
                        window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert(window.trans('custom.error_load_project'));
                    }
                });
                if (!this.firstTime) this.updateUserSettings();
                this.firstTime = false;
                this.load();
            },
            buildQuery() {
                let sort = this.sort.split(':');

                const params = {
                    belong_to: this.companyId,
                    page: this.projects.current_page,
                    sort: sort[0],
                    sort_order: sort[1],
                    per_page: this.per_page,
                    query: this.search
                };

                return Object.keys(params).map(key => key + '=' + params[key]).join('&');
            },
            openDeleteMenu(id, index){
                this.deleting_index = index;
                this.deleting_id = id;
            },
            openEditProject(project_id){
                 for(let project in this.projects.data){
                     if(this.projects.data[project].id == project_id){
                         this.editing_project = this.projects.data[project];
                         this.editing_geo = this.projects.data[project].geo_point;
                         this.editing_project.status =  this.editing_project.status== 0?'0':'1';
                         this.editing_project.public =  this.editing_project.public== null?'0':'1';
                     }
                 }

            },
            openTransferProject(project_id){
                this.transfered_id = project_id;
                this.transfer_company_id = false;
                this.transfer_type = 'change_own';

            },
            transferProjectForm(){
                var app = this;
                if (!app.transfer_company_id){
                    app.$dialog.alert("Could not succeed the transfer");

                } else {
                    for (let project in this.projects.data) {
                        if (this.projects.data[project].id == this.transfered_id && this.transfered_id > 0) {
                            if (this.projects.data[project].company_id == this.transfer_company_id) {
                                app.$dialog.alert(window.trans('custom.project_already_belongs'));
                            } else {
                                axios.post('/api/v1/transfer',
                                    {
                                        'type': app.transfer_type,
                                        'id': app.transfered_id,
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
                                        app.$dialog.alert(resp.data.description?resp.data.description:'Error occured');
                                    }

                                }).catch((data) => {
                                    if (data.response.status === 401){
                                        window.location.href = '/logouted/'+'custom.session_is_over/'+window.activeLanguage;
                                    } else {
                                        app.$dialog.alert("Could not succeed the transfer");
                                    }
                                });
                            }
                        }
                    }
                }
            },
            openCreateProject(){
                this.editing_project = {};
                this.editing_project.image = '/images/450x450.png';
                this.editing_project.status = '0';
                this.editing_project.public = '0';
                this.editing_project.company_id = this.proj_companies[0].id;
                this.editing_geo = {
                    lat: 0,
                    lng: 0
                };
            },
            saveProjectForm() {
                event.preventDefault();
                var app = this;
                this.editing_project.geo_point = this.editing_geo;

                this.editing_project.status =  this.editing_project.status== '0'?0:1;
                this.editing_project.public =  this.editing_project.public== '0'?null:'1';

                if(typeof this.editing_project.id !== 'undefined') {

                    axios.put('/api/v1/projects/' + app.editing_project.id, app.editing_project).then(() => {
                        window.location.reload();
                    }).catch(() => {
                        app.$dialog.alert("Could not edit your project");
                    });
                } else {
                    axios.post('/api/v1/projects', app.editing_project).then(() => {
                        window.location.reload();
                    }).catch(() => {
                        app.$dialog.alert("Could not create new project");
                    });
                }

            },
            deleteFinalEntry(id, index) {
                var app = this;
                app.$dialog.confirm(window.trans('custom.attention_project_will_be_removed_with_all_files_without_ability_to_restore'))
                    .then(function () {
                        axios.delete('/api/v1/projects/' + id + '/force').then(() => {
                            app.projects.data.splice(index, 1);
                            app.updateCompanies();
                            app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_been_deleted'), {'html':true, okText:'ok'});
                        }).catch(() => {
                            app.$dialog.alert(window.trans('custom.delete_error'));
                        });
                    })
                    .catch(function () {
                        console.log('Clicked on cancel')
                    });
            },
            restoreEntry(id, index){
                var app = this;
                axios.put('/api/v1/projects/' + id + '/restore')
                    .then(function (resp) {
                        app.projects.data.splice(index, 1);
                        app.updateCompanies();
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_been_restored'), {
                            'html': true,
                            okText: 'ok'
                        });
                    }).catch((data) => {
                    if (data.response.status === 401) {
                        window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                    } else {
                        app.$dialog.alert("Could not restore item");
                    }
                });
            },
            deleteEntry(id, index) {
                var app = this;
                    axios.delete('/api/v1/projects/' + id).then(() => {
                        app.projects.data.splice(index, 1);
                        app.updateCompanies();
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>' + window.trans('custom.project_will_be_deleted_in_30_days_without_ability_to_restore'), {'html':true, okText:'ok'});
                    }).catch((data) => {
                        if (data.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.delete_error'));
                        }
                    });
            },
            favouriteProject(id, index) {
                var app = this,
                    value = !app.projects.data[index].favourite;
                axios.put('/api/v1/projects/' + id + '/favourite', {favourite: value})
                    .then(function (resp) {
                        app.projects.data[index].favourite = value;
                        app.switchCategory(app.companyId);
                        app.updateCompanies();
                    })
                    .catch(function (resp) {
                        if (resp.response.status === 401) {
                            window.location.href = '/logouted/' + 'custom.session_is_over/'+window.activeLanguage;
                        } else {
                            app.$dialog.alert(window.trans('custom.error_update_project'));
                        }
                    });
            },
            changeView(type) {
                this.view_type = type;
                this.updateUserSettings();
            },
            onMapReady(map) {
                map.loadImage("/images/marker.png", function(error, image) {
                    if (error) throw error;
                    map.addImage("custom-marker", image);
                });

                const nav = new mapboxgl.NavigationControl({
                    showCompass: false
                });
                map.addControl(nav, 'top-right');

                map.addSource("projects", {
                    type: "geojson",
                    data: `/api/v1/projects?json=1&belong_to=${this.companyId}&page=1&per_page=1000&sort=id&sort_order=asc`,
                    cluster: true,
                    clusterMaxZoom: 14, // Max zoom to cluster points on
                    clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
                });

                map.addLayer({
                    id: "clusters",
                    type: "circle",
                    source: "projects",
                    interactive: true,
                    filter: ["has", "point_count"],
                    paint: {
                        "circle-color": "rgba(110,204,57,.6)",
                        "circle-radius": 20,
                        "circle-stroke-width": 5,
                        "circle-stroke-color": 'rgba(181,226,140,6)'
                    }
                });

                map.addLayer({
                    id: "cluster-count",
                    type: "symbol",
                    source: "projects",
                    filter: ["has", "point_count"],
                    layout: {
                        "text-field": "{point_count_abbreviated}",
                        "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
                        "text-size": 12
                    }
                });

                map.addLayer({
                    id: "points",
                    type: "symbol",
                    source: "projects",
                    interactive: true,
                    filter: ["!", ["has", "point_count"]],
                    layout: {
                        "icon-image": "custom-marker",
                        "icon-size": 0.15
                    }
                });
            },
            mapClicked(map, e) {
                this.addPopUp(map, e);

                const features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                if (!features.length) {
                    return;
                }

                const clusterId = features[0].properties.cluster_id;
                map.getSource('projects').getClusterExpansionZoom(clusterId, function (err, zoom) {
                    if (err)
                        return;

                    map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                });
            },
            mapMouseMoved(map, e) {
                const features = map.queryRenderedFeatures(e.point, {
                    layers: ['points', 'clusters']
                });
                map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';
            },
            addPopUp(map, e) {
                const features = map.queryRenderedFeatures(e.point, {
                    layers: ['points']
                });
                if (!features.length) {
                    return;
                }

                const feature = features[0];

                map.easeTo({
                    center: features[0].geometry.coordinates
                });

                let router = this.$router,
                    company_id = this.companyId;

                const popupContent = Vue.extend({
                    props: ['feature', 'router', 'company_id'],
                    data: function() {
                        return {
                            item: feature.properties
                        }
                    },
                    template: '<div @click="popupClicked" v-html="item.html" class="cursor-pointer"></div>',
                    methods: {
                        popupClicked() {
                            router.push({ name: 'projectItems', params: { id: feature.properties.id, company_id: company_id } })
                        },
                    }
                });

                // Populate the popup and set its coordinates
                // based on the feature found.
                const popup = new mapboxgl.Popup()
                    .setLngLat(feature.geometry.coordinates)
                    .setHTML('<div id="vue-popup-content"></div>')
                    .addTo(map);

                new popupContent().$mount('#vue-popup-content');
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
            mapSwitch() {
                this.showMap = !this.showMap
                if (this.showMap){
                    $('.map_item').addClass('active');
                } else {
                    $('.map_item').removeClass('active');
                }
                this.updateUserSettings();
            },
            searchProject() {
                if (this.searchDelay) {
                    clearTimeout(this.searchDelay);
                    this.searchDelay = null;
                }
                this.searchDelay = setTimeout(() => {
                    if (this.search != this.searchMemory) {
                        this.searchMemory = this.search;
                        this.projects.current_page = 0;
                        this.load()
                    }
                }, 800);
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
                this.new_item.upload[response.index] = response.path + response.name;
                this.totalFiles--;

                if (this.totalFiles === 0) {
                    this.saveAddingFileForm()
                }
            },
            saveAddingFileForm() {
                let formData = new FormData();
                var app = this;
                for (let key in this.new_item) {
                    if (this.new_item[key].constructor === Array) {
                        for (let array_key in this.new_item[key]) {
                            if (app.new_item[key][array_key] && app.new_item[key][array_key] !='') {
                                formData.append(key + '[' + array_key + ']', app.new_item[key][array_key]);
                            }
                        }
                    } else {
                        if (key == 'job_done_at') {
                            formData.append(key, this.new_item[key].toDateString());
                        } else {
                            formData.append(key, this.new_item[key]);
                        }
                    }
                }

                axios.post('/api/v1/projects/' + app.adding_file_project_id + '/item', formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(() => {
                    $('.close').click();
                    app.$router.push({path: '/' + app.companyId + '/projects/' + app.adding_file_project_id + '/items#files'});
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
            },
            resetUpload() {
                $('.dz-default.dz-message').css('display', 'block');
                this.submitted = false;
                this.uploadPercentage = 0;
                this.uploadPercentage_tech = 0;
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
            submitAddingFileForm() {
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
                            this.saveAddingFileForm();
                        }
                    }
                }
            },
            typeChange(e) {
                this.new_item.urls = [];
                this.selectedType = (this.files_types[e]) ? this.files_types[e] : null;

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
            openAddfile(project_id) {
                this.adding_file_project_id = project_id;
                this.new_item.project_id = project_id;
                for(let project in this.projects.data){
                    if (this.projects.data[project].id == project_id){
                        this.adding_file_company_id = this.projects.data[project].company_id;
                    }
                }
            },
            processRequest(transfer_id, action, type){
                var app = this;
                axios.put('/api/v1/transfer',
                    {
                        'type': type,
                        'action': action,
                        'id': transfer_id,
                    }).then((resp) => {
                    if (resp.data.result) {
                        let message = '';
                        if(action == 'approve') {
                            if (type == 'change_own') {
                                message = 'transfer_succeed';
                            }
                            if (type == 'copy') {
                                message = 'transfer_succeed_copying_launched';
                            }
                        }
                        if (action == 'decline'){
                            message = 'transfer_request_declined';
                        }
                        app.$dialog.alert('<div class="sc-circle"><div class="sc-sign"></div></div>'
                            + window.trans('custom.'+message), {
                            'html': true,
                            okText: 'ok'
                        }).then(() => {
                           window.location.reload();
                        });
                    } else {
                        app.$dialog.alert(resp.data.description?resp.data.description:'Error occured');
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

    }
</script>

<style scoped>
    .item-archived{
        opacity: .6;
    }
    a.btn.btn-danger i{
        color:white !important;
    }
    .button-favourite{
        margin-top: -10px;
    }
</style>