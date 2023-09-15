<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if (Auth::user())
        {!! Auth::user()->company->title ?? env('APP_NAME') !!}
        @else
            {{env('APP_NAME')}}
        @endif
    </title>

    <!-- Scripts -->
    <script src="/js/lang.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://js.stripe.com/v3/"></script>

    @yield('cust_scripts')

    <script>
        window.env = "<?= env('APP_ENV') ?>";
        window.stripe_key = "<?= env('STRIPE_KEY') ?>";
        window.Stripe = Stripe(window.stripe_key);
        window.authorised = <?= (int)Auth::check(); ?>;
        window.activeLanguage = '{{ App::getLocale() }}';
        window.availableLanguages = {!! json_encode(Config::get('languages'))  !!};
        window.perosnal_permissions = <?php echo json_encode(Auth::check()?\App\Models\User::manager()->getUserRoles(Auth::user()->id):[]); ?>;

        window.publicUrl = "<?= env('APP_URL') ?>/public";

        window.rebuildPageLoad = () => {};
        window.clearmodalHash = () => {};
        window.initiatePageLoad = () => {};
        window.addHash = () => {};

        @if (Auth::user())
        window.allowedDisplay = function (action, model = false, id = false) {
            var roles_permissions = <?php
                $roles = config('roles');
                echo json_encode($roles);
                ?>;

            var base_role = '<?php echo Auth::check()?Auth::user()->role:'guest';?>';

            if(!model){
                let permissions = roles_permissions[base_role].permissions;
                return (permissions[0] == 'all' || permissions.indexOf(action) >= 0);
            } else {
                if (window.perosnal_permissions.is_super_user) {
                    return true;
                } else {
                    if (typeof window.perosnal_permissions[model] !== 'undefined' && typeof window.perosnal_permissions[model][id] !== 'undefined') {
                        return roles_permissions[window.perosnal_permissions[model][id]].permissions.indexOf(action) >= 0;
                    } else {
                        return false
                    }
                }
            }
        }
        window.parseError = function(content, title = 'Error:'){
            var error_message = title + "<br><br>";

            if(typeof content == 'object'){
                for(let key in content){
                    var item_error = key[0].toUpperCase() + key.slice(1) + ': ';
                    if (typeof content[key] == 'Object'){
                        for(let error_item_key in content[key]){
                            item_error = item_error + content[key] + ' '
                        }
                    } else {
                        item_error = item_error + content[key];
                    }
                    item_error = item_error + '<br>';
                    error_message = error_message + item_error;
                }
                return error_message;
            } else {
                error_message = error_message + content;
                return error_message;
            }
        }
        window.updatePersonalPermissions = function () {
            axios.get('/api/v1/user/personal_permissions').then(response => (
                window.perosnal_permissions = response.data))
        }
        window.eraseCookie = function(name){
            createCookie(name, "", -1);
        }

        window.readCookie = function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }

        window.createCookie = function(name,value,days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }
        @endif
    </script>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @yield('cust_styles')

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script> @if (Auth::user())(function (d, w, c) {
            w.ChatraID = 'nxyWn2tRJgKupbHsw';
            var s = d.createElement('script');
            w[c] = w[c] || function () {
                (w[c].q = w[c].q || []).push(arguments);
            };
            s.async = true;
            s.src = 'https://call.chatra.io/chatra.js';
            if (d.head) d.head.appendChild(s);
        })(document, window, 'Chatra');@endif</script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel shadow-none guest">
            @guest
                <div class="container">
                    <div class="logo-container">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img src="/images/logo.png" />
                        </a>
                    </div>
                </div>
            @endguest
            <div class="container">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @auth
                            <li class="nav-item">
                                <router-link :to="{name: 'index'}"  class="nav-link mr-0 " href=""><i class="projects_icon"></i>{{ __('custom.projects') }}</router-link>
                            </li>

                            @if (Auth::user()->role == 'super_user' || Auth::user()->role == 'administrator')
                            <li class="nav-item">
                                <router-link :to="{name: 'userList'}"  class="nav-link mr-0 ml-3" href=""><i class="users_icon"></i>{{ __('custom.users') }}</router-link>
                            </li>
                            @endif
                            <li class="nav-item">
                                <router-link :to="{name: 'filesList'}" class="nav-link file_nav "
                                             href=""><i class="files_icon "></i>{{ __('custom.files') }}</router-link>

                            </li>
                            <li class="nav-item">
                                <router-link :to="{name: 'companyIndex'}"  class="nav-link mr-0 ml-3" href=""><i class="companies_icon"></i>{{ __('custom.companies') }}</router-link>
                            </li>

                            <li class="nav-item map_item disabled">
                                <span  class="nav-link mr-0 ml-3" ><i class="map_icon"></i>{{ __('custom.map') }}</span>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                    @guest
                        <!-- Language Links -->
                        <!--<li class="nav-item dropdown lang-dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                <span>{{ Config::get('languages')[App::getLocale()] }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="min-width: initial">
                                @foreach (Config::get('languages') as $lang => $language)
                                    @if ($lang != App::getLocale())
                                        @if(Auth::check())
                                            <span style="cursor:pointer;" class="dropdown-item"
                                                  onclick="axios.get('{{ route('lang.switch', $lang) }}').then(response => (window.location.reload()))">
                                            <span>   {{ $language }}</span>
                                        </span>
                                        @else
                                            <a href="{{ route('lang.switch', $lang) }}" class="dropdown-item">
                                                {{ $language }}
                                            </a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </li>-->
                        <!-- Authentication Links

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('custom.login') }}</a>
                            </li>-->
                        @else
                            @if(Auth::user()->ownedCompanies->count() > 1)

                                <!-- Companies Links -->
                                    <li class="nav-item dropdown companies-dropdown">
                                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                            <span class="overflow-dotted">{{Auth::user()->company->title}}</span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" style="min-width: initial">
                                            @foreach (Auth::user()->ownedCompanies as $owned_company)
                                                @if ($owned_company->id != Auth::user()->company_id)
                                                    <span style="cursor:pointer;" class="dropdown-item"
                                                          onclick="axios.get('{{ route('company.switch', $owned_company->id) }}').then(response => (window.location.reload()))">
                                            <span>   {{ $owned_company->title }}</span>
                                        </span>

                                                @endif
                                            @endforeach
                                        </div>
                                    </li>

                            @endif

                            @if (Auth::user()->company->verified)
                            <li class="nav-item storage_bar">
                                <div class="dropdown-header">
                                    <storage-status></storage-status>
                                </div>
                            </li>
                                @else
                                @if(Auth::user()->id == Auth::user()->company->owner_id)
                                        <router-link :to="{name: 'companyEdit', params: {id: {{Auth::user()->company_id}}}}" class="mr-4 mt-4 verify_btn">
                                            <span class="btn btn-primary">{{trans('custom.register_company')}}</span>
                                        </router-link>
                                    @endif
                                @endif


                                <li class="nav-item notifications">
                                    <router-link :to="{name: 'notificationIndex'}">
                                        <span class="notifications_point"><span v-if="notifications">@{{ notifications }}</span></span>
                                    </router-link>
                                </li>



                                <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link last_item dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <div class="ratio img-responsive img-circle" style="background-image: url({{ Auth::user()->picture }});"></div>
                                    <span class="caret"></span>
                                </a>



                                    <div class="dropdown-menu dropdown-menu-right top-right-user-menu" aria-labelledby="navbarDropdown">

                                        <div class="user_info_holder">
                                            <div class="ratio img-responsive img-circle" style="background-image: url({{ Auth::user()->picture }});"></div>
                                            <div class="user_name">{{ Auth::user()->name }}</div>
                                        </div>


                                        <router-link :to="{name: 'userSettings'}" class="dropdown-item"><i class="settings_nav_icon"> </i> {{ __('custom.settings') }}</router-link>

                                        @if (Auth::user()->role == 'super_user')
                                            <router-link :to="{name: 'userStats'}" class="dropdown-item"><i class="stats_nav_icon"> </i>{{ __('custom.statistics') }}</router-link>
                                        @endif

                                        <router-link :to="{name: 'notificationIndex'}" class="dropdown-item"><i class="notif_nav_icon"> </i>{{ __('custom.notifications') }} <span class="notification-informer" v-if="notifications">@{{ notifications }}</span></router-link>

                                        <a class="dropdown-item" href="mailto:support@my3d.cloud"><i class="support_nav_icon"> </i>{{ trans('custom.support') }}</a>

                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"><i class="logout_nav_icon"> </i>
                                            {{ __('custom.logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @auth
            <div class="container">
                <div class="logo-container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="/images/logo.png" />
                    </a>
                </div>
            </div>
        @endauth
        <main class="py-4">
            @if (session('vue-warning'))
                <div class="alert alert-warning container vue-warning">
                    {{ session('vue-warning') }}
                </div>
            @endif
            @yield('content')
        </main>

        <div class="col-sm-12 text-center small pb-4">
            {{env('APP_NAME')}} @ 2021. All Rights Reserved
        </div>
        @if (Auth::user())
            <back-to-top bottom="50px" right="50px">
                <button type="button" class="btn btn-info btn-to-top"><i class="fa fa-chevron-up"></i></button>
            </back-to-top>
            <modal-file-upload></modal-file-upload>
        @endif

    </div>
</body>
</html>
