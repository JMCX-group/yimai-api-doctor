<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\AdmissionsMsg;
use App\Api\Transformers\Transformer;
use App\Api\Transformers\UserTransformer;
use App\AppointmentMsg;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\RadioStation;
use App\User;

class InitController extends BaseController
{
    /**
     * Get init info.
     * 
     * @return array|mixed
     */
    public function index()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        /**
         * Get user info.
         */
        $retUser = UserTransformer::transformUser($user);

        /**
         * Get all relations.
         */
        $relationData = Transformer::userListTransform(DoctorRelation::getFriends($user->id));
        $relations = [
            'same' => User::getSameTypeContactCount($user->hospital_id, $user->dept_id, $user->college_id),
            'unread' => DoctorRelation::getNewFriendsIdList($user->id)['unread'],
            'count' => [
                'doctor' => count($relationData['friends']),
                'hospital' => $relationData['hospital_count']
            ],
            'friends' => $relationData['friends']
        ];

        /**
         * Get recent contacts.
         */
        $contactRecords = DoctorContactRecord::where('doctor_id', $user->id)->lists('contacts_id_list');
        $contactRecordsIdList = (count($contactRecords) != 0) ? explode(',', $contactRecords[0]) : $contactRecords;
        $contacts = User::whereIn('id', $contactRecordsIdList)->get();
        $retContact = array();
        foreach ($contacts as $contact) {
            array_push($retContact, Transformer::contactsTransform($contact));
        }

        /**
         * Get all system notification.
         */
        $radioStationUnreadCount = RadioStation::leftJoin('radio_read', function ($join) use ($user) {
            $join->on('radio_stations.id', '=', 'radio_read.radio_station_id')
                ->where('radio_read.user_id', '=', $user->id);
        })
            ->where('status', 0)
            ->where('valid', '>', date('Y-m-d H:i:s'))
            ->where('radio_read.value', 1)
            ->count();

        /**
         * Get all admissions msg.
         */
        $admissionsUnreadCount = AdmissionsMsg::where('doctor_id', $user->id)
            ->where('read_status', 0)
            ->count();

        /**
         * Get all appointment msg.
         */
        $appointmentUnreadCount = AppointmentMsg::where('locums_id', $user->id)
            ->where('read_status', 0)
            ->count();

        return [
            'user' => $retUser,
            'relations' => $relations,
            'recent_contacts' => $retContact,
            'sys_info' => [
                'radio_unread_count' => $radioStationUnreadCount,
                'admissions_unread_count' => $admissionsUnreadCount,
                'appointment_unread_count' => $appointmentUnreadCount
            ]
        ];
    }
}
