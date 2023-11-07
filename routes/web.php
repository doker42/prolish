<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

Auth::routes();
Route::get('user/verify/{token}', 'Auth\RegisterController@verifyUser');
Route::get('user/company_approve/{token}/{action}/{lang}', 'UserController@companyApproveUser')->name('company_approve');
Route::get('user/verify_new_email/{token}', 'UserController@verifyNewEmail')->name('verify_new_email');

Route::get('file/upload/{code}', 'ItemController@externalDownload');
Route::get('file/view/{code}', 'ItemController@externalView');

Route::get('logouted/{message}/{lang}', function ($message, $lang) {
    Session::put('applocale', $lang);
    return redirect('/login')->with('status', trans($message,[], $lang));
});

// Localization
Route::get('js/lang.js', function () {
    $lang = config('app.locale');

    $files   = glob(resource_path('lang/' . $lang . '/*.php'));
    $strings = [];

    foreach ($files as $file) {
        $name           = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    echo('window.i18n = ' . json_encode($strings) . ';');
    exit();
});

Route::get('lang/{lang}', ['as' => 'lang.switch', 'uses' => 'LanguageController@switchLang']);

Route::get('test', 'IndexController@test');

// PayPal
Route::get('paypal/express-checkout', 'PaypalController@expressCheckout')->name('paypal.express-checkout');
Route::get('paypal/express-checkout-success', 'PaypalController@expressCheckoutSuccess');
Route::post('paypal/notify', 'PaypalController@notify');

// Mollie
//Route::name('webhooks.mollie')->post('webhooks/mollie', 'MollieWebhookController@handle');

Route::get('public_projects',  'ProjectController@public');

Route::get('companies_autocompleate',  'CompanyController@searchCompany');
Route::get('companies_invited',  'UserController@searchInvite');

Route::get('public', function() {
    return view('public_project');
});

//Stripe
Route::post('stripe/webhook', 'WebhookController@handleWebhook');


Route::middleware('auth')->group(function($route) {

    $route->get('', 'IndexController@show');

    // Potree
    $route->get('potree/{id}', 'PotreeController@show');
    $route->get('download', 'FileController@show');

    $route->get('memberships/{id}/payment/card', 'MembershipController@card_payment');
    $route->get('memberships/{id}/invoice', 'MembershipController@show_invoice');
    Route::get('company_switch/{company_id}', ['as' => 'company.switch', 'uses' => 'UserController@switchCompany']);

    // API
    $route->group(['prefix' => 'api/v1/'], function($route) {

        $route->group(['prefix' => 'projects'], function ($route) {
            // Projects
            $route->get('', 'ProjectController@index');
            $route->post('', 'ProjectController@store');
            $route->get('emails',  'ProjectController@emails');
            $route->get('visibility_counter', 'ProjectController@visibilityCounter');
            $route->get('check_create', 'ProjectController@check_create');
            $route->get('manageable',  'ProjectController@manageableIndex');
            $route->get('{id}',  'ProjectController@show');
            $route->put('{id}',  'ProjectController@update');
            $route->put('{id}/favourite',  'ProjectController@favourite');
            $route->put('{id}/archive',  'ProjectController@dele');
            $route->delete('{id}',  'ProjectController@destroy');
            $route->delete('{id}/force', 'ProjectController@forceDelete');
            $route->put('{id}/restore', 'ProjectController@restore');



            // Project Share/Visibility
            $route->get('{id}/visibility', 'ProjectController@visibility');
            $route->post('{id}/visibility', 'ProjectController@visibility_add');
            $route->put('{id}/visibility', 'ProjectController@visibility_change');
            $route->delete('{id}/visibility', 'ProjectController@visibility_delete');
            $route->delete('{id}/visibility_leave', 'ProjectController@leaveProject');

            // Project Logs
            $route->get('{id}/logs', 'ProjectLogController@index');

            // Project Gallery
            $route->get('{id}/gallery', 'ProjectGalleryController@index');
            $route->post('{id}/gallery', 'ProjectGalleryController@store');
            $route->delete('{id}/gallery/{image_id}', 'ProjectGalleryController@destroy');
            $route->post('{id}/gallery/folder', 'ProjectGalleryController@create_folder');
            $route->delete('{id}/gallery/folder/{folder_id}', 'ProjectGalleryController@destroy_folder');

            $route->get('{id}/gallery/download', 'ProjectGalleryController@downloadGallery');
            $route->get('/gallery/folder/{folder_id}/download', 'ProjectGalleryController@downloadGalleryFolder');

            // Items
            $route->get('{project_id}/item/{id}', 'ItemController@show');
            $route->put('{project_id}/item/{id}', 'ItemController@update');
            $route->post('{project_id}/item', 'ItemController@store');
            $route->delete('{project_id}/item/{id}', 'ItemController@destroy');

            $route->put('{project_id}/item/{id}/restore', 'ItemController@restore');
            $route->post('{project_id}/item/{id}/convert', 'ItemController@convert');
            $route->delete('{project_id}/item/{id}/force', 'ItemController@forceDelete');

            $route->delete('{project_id}/item/{id}/file/{type}', 'ItemController@removeFile');

            // Contacts
            $route->get('{project_id}/contact/{id}', 'ContactController@show');
            $route->put('{project_id}/contact/{id}', 'ContactController@update');
            $route->post('{project_id}/contact', 'ContactController@store');
            $route->delete('{project_id}/contact/{id}', 'ContactController@destroy');
        });

        $route->group(['prefix' => 'user'], function ($route) {
            $route->get('', 'UserController@me');
            $route->put('', 'UserController@update');
            $route->post('', 'UserController@store');
            $route->put('passwords', 'UserController@updatePassword');
            $route->get('list', 'UserController@index');
            $route->get('roles', 'UserController@roles');
            $route->get('delete_change_email_request', 'UserController@deleteChangeEmailRequest');
            $route->get('personal_permissions', 'UserController@personalPermissions');
            $route->get('companies', 'UserController@companies');
            $route->put('{id}', 'UserController@update');
            $route->get('{id}', 'UserController@show');
            $route->delete('{id}', 'UserController@destroy');

        });

        $route->group(['prefix' => 'usersettings'], function ($route) {
            $route->get('', 'UserController@userSettings');
            $route->put('', 'UserController@updateUserSettings');
        });

        $route->group(['prefix' => 'companies'], function ($route) {
            $route->get('', 'CompanyController@index');
            $route->get('verified', 'CompanyController@indexVerified');
            $route->get('all_verified', 'CompanyController@indexAllVerified');
            $route->get('list_values', 'CompanyController@listValues');
            $route->post('', 'CompanyController@store');
            $route->get('temp_storage_credentials', 'CompanyController@getTempStorageCredentials');
            $route->get('{id}/{action}', 'CompanyController@show');
            $route->put('{id}', 'CompanyController@update');
            $route->delete('{id}', 'CompanyController@destroy');
            $route->post('{id}/invite', 'CompanyController@invite');
            $route->get('{id}/profile', 'CompanyController@profile');
            $route->post('{id}/message', 'CompanyController@messageAdmins');
            $route->post('{id}/verify_request', 'CompanyController@verifyRequest');
            $route->delete('{id}/user/{user_id}', 'CompanyController@destroy_user');
            $route->put('{id}/user/{user_id}', 'CompanyController@change_user_role');
            $route->delete('{id}/visibility_leave', 'CompanyController@leaveCompany');
            $route->get('temp_files', 'CompanyController@indexTempFiles');
            $route->post('storage_file_to_project', 'CompanyController@moveTempFile');
            $route->post('storage_file_to_gallery', 'CompanyController@moveTempFileGallery');
            $route->post('delete_storage_file', 'CompanyController@deleteTempFile');
        });

        $route->group(['prefix' => 'notifications'], function ($route) {
            $route->get('', 'NotificationController@index');
            $route->post('', 'NotificationController@store');
            $route->post('set_seen', 'NotificationsUnseenController@deleteUnseen');
            $route->get('unseen', 'NotificationsUnseenController@index');
            $route->get('{id}', 'NotificationController@show');
            $route->put('{id}', 'NotificationController@update');
            $route->delete('{id}', 'NotificationController@destroy');
        });

        $route->group(['prefix' => 'memberships'], function ($route) {
            $route->get('', 'MembershipController@index');
            $route->post('', 'MembershipController@store');
            $route->put('approve', 'MembershipController@approve');
            $route->get('invoices', 'MembershipController@invoices');
            $route->get('/create_session/{prise_id}/{company_id}/{regular}', 'MembershipController@createSessionForCheckout');
        });

        $route->group(['prefix' => 'subscription'], function ($route) {
            $route->get('', 'SubscriptionController@getActive');
            $route->put('unsubscribe', 'SubscriptionController@unsubscribe');
        });

        $route->group(['prefix' => 'statistics'], function ($route) {
            $route->get('', 'ActivityLogController@index');
            $route->get('numbers', 'ActivityLogController@numbers');
            $route->post('online', 'ActivityLogController@stubOnline');
        });

        // Storage
        $route->get('storage', 'IndexController@storage');

        // File
        $route->post('upload', 'FileController@store');
        $route->post('upload-chunk', 'UploadController@upload');

        // Project Item
        $route->group(['prefix' => 'projectitems'], function ($route) {
            $route->get('', 'ItemController@index');
            $route->get('uploaders', 'ItemController@uploadersIndex');
        });
        // Potree
        $route->get('potree/types', 'PotreeController@types');

        $route->group(['prefix' => 'transfer'], function ($route) {
            $route->get('', 'TransferRequest@index');
            $route->post('', 'TransferRequest@store');
            $route->put('', 'TransferRequest@process');
        });


    });


});
