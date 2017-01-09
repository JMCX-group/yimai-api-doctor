<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('article/{article_id}', 'ArticleController@getArticle');
Route::get('about/contact-us', 'AboutController@contactUs');
Route::get('about/introduction', 'AboutController@introduction');
Route::get('about/lawyer', 'AboutController@lawyer');
Route::get('agreement/doctor', 'AgreementController@doctor');
Route::get('share/index', 'ShareController@index');

Route::get('banner/{banner_id}', 'BannerController@getBannerContent');

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        /**
         * Api Doc
         */
        $api->get('/', 'ApiController@index');

        /**
         * Register & Login
         */
        $api->group(['prefix' => 'user'], function ($api) {
            $api->post('register', 'AuthController@register');
            $api->post('verify-code', 'AuthController@sendVerifyCode');
            $api->post('inviter', 'AuthController@getInviter');
            $api->post('login', 'AuthController@authenticate');
            $api->post('reset-pwd', 'AuthController@resetPassword');
        });

        /**
         * Banner
         */
        $api->get('get-banner-url', 'BannerController@index');

        $api->post('get-doctor', 'UserController@verifyDoctor');

        /**
         * Token Auth
         */
        $api->group(['middleware' => 'jwt.auth'], function ($api) {
            // Init
            $api->group(['prefix' => 'init'], function ($api) {
                $api->get('/', 'InitController@index');
            });

            // Doctor
            $api->group(['prefix' => 'user'], function ($api) {
                $api->get('me', 'AuthController@getAuthenticatedUser');
                $api->get('/{doctor}', 'UserController@findDoctor');
                $api->get('phone/{doctor}', 'UserController@findDoctor_byPhone');
                $api->post('/', 'UserController@update');
                $api->post('search', 'UserController@searchUser');
                $api->post('search/admissions', 'UserController@searchUser_admissions');
                $api->post('search/same-hospital', 'UserController@searchUser_sameHospital');
                $api->post('search/same-department', 'UserController@searchUser_sameDept');
                $api->post('search/same-college', 'UserController@searchUser_sameCollege');
                $api->post('upload-auth-img', 'UserController@uploadAuthPhotos');
                $api->post('reset-phone', 'AuthController@resetPhone');
            });

            // Search
            $api->group(['prefix' => 'search'], function ($api) {
                $api->post('doctor_info', 'SearchController@getDoctorInfoForDpCode');
                $api->post('doctors', 'SearchController@doctors');
            });

            // City
            $api->group(['prefix' => 'city'], function ($api) {
                $api->get('/', 'CityController@index');
                $api->get('group', 'CityController@cityGroup');
            });

            // Hospital
            $api->group(['prefix' => 'hospital'], function ($api) {
                $api->get('/', 'HospitalsController@index');
                $api->get('city/{city}', 'HospitalsController@inCityHospital');
                $api->get('{hospital}', 'HospitalsController@show');
                $api->get('search/{search_field}', 'HospitalsController@findHospital');
                $api->post('search/admissions', 'HospitalsController@findHospital_provinces');
            });

            // College
            $api->group(['prefix' => 'college'], function ($api) {
                $api->get('/all', 'CollegeController@index');
            });

            // Dept
            $api->group(['prefix' => 'dept'], function ($api) {
                $api->get('/', 'DeptStandardController@index');
            });
            
            // Tag
            $api->group(['prefix' => 'tag'], function ($api) {
                $api->get('/all', 'TagController@index');
                $api->get('/group', 'TagController@group');
            });
            
            // Relation
            $api->group(['prefix' => 'relation'], function ($api) {
                $api->post('add-friend', 'DoctorRelationController@store');
                $api->post('add-all', 'DoctorRelationController@addAll');
                $api->post('confirm', 'DoctorRelationController@update');
                $api->get('/', 'DoctorRelationController@getRelations');
                $api->get('friends', 'DoctorRelationController@getRelationsFriends');
                $api->get('friends-friends', 'DoctorRelationController@getRelationsFriendsFriends');
                $api->get('common-friends/{friend}', 'DoctorRelationController@getCommonFriends');
                $api->get('new-friends', 'DoctorRelationController@getNewFriends');
                $api->post('push-recent-contacts', 'DoctorRelationController@pushRecentContacts');
                $api->post('remarks', 'DoctorRelationController@setRemarks');
                $api->post('del', 'DoctorRelationController@destroy');
                $api->post('upload-address-book', 'DoctorRelationController@uploadAddressBook');
                $api->post('send-invite', 'DoctorRelationController@sendInvite');
            });
            
            // Radio
            $api->group(['prefix' => 'radio'], function ($api) {
                $api->get('/', 'RadioStationController@index');
                $api->post('read', 'RadioStationController@readStatus');
                $api->get('all-read', 'RadioStationController@allRead');
            });

            //Appointment
            $api->group(['prefix' => 'appointment'], function ($api) {
                $api->post('new', 'AppointmentController@store');
                $api->post('update', 'AppointmentController@update');
                $api->post('upload-img', 'AppointmentController@uploadImg');
                $api->get('detail/{appointment}', 'AppointmentController@getDetailInfo');
                $api->get('list', 'AppointmentController@getReservationRecord');
            });
            
            //Admissions
            $api->group(['prefix' => 'admissions'], function ($api) {
                $api->get('list', 'AdmissionsController@getAdmissionsRecord');
                $api->get('detail/{admissions}', 'AdmissionsController@getDetailInfo');
                $api->post('agree', 'AdmissionsController@agreeAdmissions');
                $api->post('refusal', 'AdmissionsController@refusalAdmissions');
                $api->post('complete', 'AdmissionsController@completeAdmissions');
                $api->post('rescheduled', 'AdmissionsController@rescheduledAdmissions');
                $api->post('cancel', 'AdmissionsController@cancelAdmissions');
                $api->post('transfer', 'AdmissionsController@transferAdmissions');
            });

            //Patient
            $api->group(['prefix' => 'patient'], function ($api) {
                $api->get('get-by-phone', 'PatientController@getInfoByPhone');
                $api->get('all', 'PatientController@all');
            });

            //Face-to-face
            $api->group(['prefix' => 'f2f-advice'], function ($api) {
                $api->post('new', 'FaceToFaceAdviceController@store');
            });

            //Message
            $api->group(['prefix' => 'msg'], function ($api) {
                $api->get('appointment/all', 'AppointmentMsgController@index');
                $api->get('appointment/new', 'AppointmentMsgController@newMessage');
                $api->post('appointment/read', 'AppointmentMsgController@readMessage');
                $api->get('appointment/all-read', 'AppointmentMsgController@allRead');
                $api->get('admissions/all', 'AdmissionsMsgController@index');
                $api->get('admissions/new', 'AdmissionsMsgController@newMessage');
                $api->post('admissions/read', 'AdmissionsMsgController@readMessage');
                $api->get('admissions/all-read', 'AdmissionsMsgController@allRead');
            });

            //Contacts
            $api->group(['prefix' => 'contacts'], function ($api) {
                $api->get('all', 'ContactController@index');
            });

            //Card
            $api->group(['prefix' => 'card'], function ($api) {
                $api->get('submit', 'CardController@submit');
                $api->get('resubmit', 'CardController@submit');
            });

            //Wallet
            $api->group(['prefix' => 'wallet'], function ($api) {
                $api->get('info', 'WalletController@info');
                $api->get('record', 'WalletController@recordGet');
                $api->post('record', 'WalletController@record');
                $api->get('detail/{id}', 'WalletController@detail');
                $api->get('withdraw', 'WalletController@withdraw');
            });

            //Bank
            $api->group(['prefix' => 'bank'], function ($api) {
                $api->get('info', 'BankController@index');
                $api->get('new', 'BankController@store');
                $api->post('update', 'BankController@update');
            });
        });
    });
});
