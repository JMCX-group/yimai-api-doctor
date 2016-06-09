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

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        /**
         * Api Doc
         */
        $api->get('/', 'ApiController@index');
        $api->get('index', 'TestController@index');

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
         * Token Auth
         */
        $api->group(['middleware' => 'jwt.auth'], function ($api) {
            // Test
            $api->post('post', 'TestController@postFun');

            // Init
            $api->group(['prefix' => 'init'], function ($api) {
                $api->get('/', 'InitController@index');
            });

            // Doctor
            $api->group(['prefix' => 'user'], function ($api) {
                $api->get('me', 'AuthController@getAuthenticatedUser');
                $api->get('/{doctor}', 'UserController@findDoctor');
                $api->post('/', 'UserController@update');
                $api->post('search', 'UserController@searchUser');
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
            });

            // Dept
            $api->group(['prefix' => 'dept'], function ($api) {
                $api->get('/', 'DeptStandardController@index');
            });
            
            // Relation
            $api->group(['prefix' => 'relation'], function ($api) {
                $api->post('add-friend', 'DoctorRelationController@store');
                $api->post('confirm', 'DoctorRelationController@update');
                $api->get('/', 'DoctorRelationController@getRelations');
                $api->get('friends', 'DoctorRelationController@getRelationsFriends');
                $api->get('friends-friends', 'DoctorRelationController@getRelationsFriendsFriends');
                $api->get('common-friends/{friend}', 'DoctorRelationController@getCommonFriends');
                $api->get('new-friends', 'DoctorRelationController@getNewFriends');
                $api->post('push-recent-contacts', 'DoctorRelationController@pushRecentContacts');
                $api->post('remarks', 'DoctorRelationController@setRemarks');
                $api->post('del', 'DoctorRelationController@destroy');
            });
            
            // Radio
            $api->group(['prefix' => 'radio'], function ($api) {
                $api->get('/', 'RadioStationController@index');
                $api->post('read', 'RadioStationController@readStatus');
            });

            //Appointment
            $api->group(['prefix' => 'appointment'], function ($api) {
                $api->post('new', 'AppointmentController@store');
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
            });

            //Patient
            $api->group(['prefix' => 'patient'], function ($api) {
                $api->get('get-by-phone', 'PatientController@getInfoByPhone');
            });

            //Face-to-face
            $api->group(['prefix' => 'f2f-advice'], function ($api) {
                $api->post('new', 'FaceToFaceAdviceController@store');
            });
        });
    });
});
