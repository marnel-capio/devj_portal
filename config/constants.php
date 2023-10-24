<?php

return [
    "DEPARTMENT" => "Dev J",
    //Employees table - roles
    'ADMIN_ROLE_VALUE' => 1,
    'MANAGER_ROLE_VALUE' => 2,
    'ENGINEER_ROLE_VALUE' => 3,

    //project role
    'PROJECT_ROLE_TEAM_LEAD' => 1,
    'PROJECT_ROLE_PROGRAMMER' => 2,
    'PROJECT_ROLE_QA' => 3,

    //Employee table - approved_status
    'APPROVED_STATUS_REJECTED' => 1,
    'APPROVED_STATUS_APPROVED' => 2,
    'APPROVED_STATUS_PENDING' => 3,
    'APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE' => 4,
    'CANCEL_REGIST' => 5,
    'CANCEL_LINK' => 6,

    //Approve Status Text
    'APPROVED_STATUS_REJECTED_TEXT' => 'Denied',
    'APPROVED_STATUS_APPROVED_TEXT' => 'Approved',
    'APPROVED_STATUS_PENDING_TEXT' => 'Pending Approval',
    'APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT' => 'Pending Update Approval',

    //Employee table - position
    'POSITION_JR_RESEARCH_DEVELOPMENT_VALUE' => 1,
    'POSITION_ASSISTANT_RESEARCH_DEVELOPMENT_VALUE' => 2,
    'POSITION_SR_ASSISTANT_RESEARCH_DEVELOPMENT_VALUE' => 3,
    'POSITION_ASSOC_RESEARCH_DEVELOPMENT_VALUE' => 4,
    'POSITION_SR_ASSOC_RESEARCH_DEVELOPMENT_VALUE' => 5,
    'POSITION_SUPERVISOR_VALUE' => 6,
    'POSITION_ADVISER_VALUE' => 7,
    'POSITION_TECHNICAL_SPECIALIST_VALUE' => 8,
    'POSITION_APPLICATION_DEVELOPER_VALUE' => 9,
    'POSITION_ASSSITANT_MANAGER_VALUE' => 98,
    'POSITION_MANAGER_VALUE' => 99,
    
    'POSITION_1_NAME' => 'Junior Research and Development',
    'POSITION_2_NAME' => 'Assistant Research and Development',
    'POSITION_3_NAME' => 'Senior Assistant Research and Development',
    'POSITION_4_NAME' => 'Associate Research and Development',
    'POSITION_5_NAME' => 'Senior Associate Research and Development',
    'POSITION_6_NAME' => 'Supervisor',
    'POSITION_7_NAME' => 'Adviser',
    'POSITION_8_NAME' => 'Technical Specialist',
    'POSITION_9_NAME' => 'Application Developer',
    'POSITION_98_NAME' => 'Assistant Manager',
    'POSITION_99_NAME' => 'Manager',

    'POSITIONS' => [
        1 => 'Junior Research and Development',
        2 => 'Assistant Research and Development',
        3 => 'Senior Assistant Research and Development',
        4 => 'Associate Research and Development',
        5 => 'Senior Associate Research and Development',
        6 => 'Supervisor',
        7 => 'Adviser',
        8 => 'Technical Specialist',
        9 => 'Application Developer',
        98 => 'Assistant Manager',
        99 => 'Manager',

    ],

    // Employee table - passport type
    'PASSPORT_TYPE_1_NAME' => 'Ordinary',
    'PASSPORT_TYPE_2_NAME' => 'Official',
    'PASSPORT_TYPE_3_NAME' => 'Diplomatic',
    
    'PASSPORT_TYPE_ORDINARY_VALUE' => 1,
    'PASSPORT_TYPE_OFFICIAL_VALUE' => 2,
    'PASSPORT_TYPE_DIPLOMATIC_VALUE' => 3,
    
    'PASSPORT_TYPE' => [
        1 => 'Ordinary',
        2 => 'Official',
        3 => 'Diplomatic',
    ],

    // Employee table - passport status
    'PASSPORT_STATUS_1_NAME' => 'With Passport',
    'PASSPORT_STATUS_2_NAME' => 'With Appointment',
    'PASSPORT_STATUS_3_NAME' => 'Without Passport',
    'PASSPORT_STATUS_4_NAME' => 'Waiting for Delivery',

    'PASSPORT_STATUS_WITH_PASSPORT_VALUE' => 1,
    'PASSPORT_STATUS_WITH_APPOINTMENT_VALUE' => 2,
    'PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE' => 3,
    'PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE' => 4,

    'PASSPORT_STATUS_LIST' => [
        'withPassport' => [
                            "name" => 'With Passport',
                            "val" => 1,
                        ],
        'waitingDelivery' => [
                            "name" => 'Waiting for Delivery',
                            "val" => 4,
                        ],
        'withAppointment' => [
                            "name" => 'With Appointment',
                            "val" => 2,
                        ],
        'withoutPassport' => [
                            "name" => 'Without Appointment',
                            "val" => 3,
                        ],
    ],

    // Identifies when the notification will start to display
    // 1. As Information notification
    'PASSPORT_STATUS_1_INFO_START' => [
        'unit' => 'months',
        'value' => 6,
    ],
    'PASSPORT_STATUS_2_INFO_START' => ($info_2_and_4 = [
        'unit' => 'months',
        'value' => 3,
    ]),
    'PASSPORT_STATUS_3_INFO_START' => [
        'unit' => 'n/a',
        'value' => 0,
    ],
    'PASSPORT_STATUS_4_INFO_START' => $info_2_and_4,

    // 2. As Warning notification
    'PASSPORT_STATUS_1_WARNING_START' => [
        'unit' => 'months',
        'value' => 3,
    ],
    'PASSPORT_STATUS_2_WARNING_START' => ($warning_2_and_4 = [
        'unit' => 'days',
        'value' => 7,
    ]),
    'PASSPORT_STATUS_3_WARNING_START' => [
        'unit' => 'always',
        'value' => 0,
    ],
    'PASSPORT_STATUS_4_WARNING_START' => $warning_2_and_4,

    //Employee - BU List - for BU transfer - Dev J is excluded
    'BU_LIST' => [
        1  => 'Dev A',
        2  => 'Dev B',
        3  => 'Dev C',
        4  => 'Dev D',
        5  => 'Dev E',
        6  => 'Dev F',
        7  => 'Dev G',
        8  => 'Dev H',
        9  => 'Dev I',
        10  => 'Dev K',
        11  => 'Dev L',
        12  => 'Dev J',
        13  => 'Dev M',
        14  => 'Dev N',
        15  => 'Dev 2',
        16  => 'Dev 3',
        17  => 'Dev 5',
        18  => 'Dev 6',
        19  => 'C4I',
    ],

    //Employee Mail Type
    'MAIL_NEW_REGISTRATION_REQUEST' => 1,
    'MAIL_NEW_REGISTRATION_APPROVAL' => 2,
    'MAIL_NEW_REGISTRATION_REJECTION' => 3,
    'MAIL_EMPLOYEE_UPDATE_REQUEST' => 4,
    'MAIL_EMPLOYEE_UPDATE_APPROVAL' => 5,
    'MAIL_EMPLOYEE_UPDATE_REJECTION' => 6,
    'MAIL_EMPLOYEE_UPDATE_BY_MANAGER' => 7,
    'MAIL_EMPLOYEE_PROJECT_LINK_REQUEST' => 8,
    'MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST' => 9,
    'MAIL_EMPLOYEE_PROJECT_LINK_BY_MANAGER' => 10,
    'MAIL_EMPLOYEE_LAPTOP_LINK_BY_MANAGER' => 11,
    'MAIL_EMPLOYEE_DEACTIVATION' => 12,
    'MAIL_EMPLOYEE_SURRENDER_LAPTOP_NOTIFICATION' => 13,
    'MAIL_EMPLOYEE_REACTIVATION' => 14,
    'MAIL_EMPLOYEE_CANCEL_UPDATE' => 15,

    //Software Mail Type
    'MAIL_SOFTWARE_NEW_REQUEST' => 1,
    'MAIL_SOFTWARE_NEW_APPROVAL' => 2,
    'MAIL_SOFTWARE_NEW_REJECTION' => 3,
    'MAIL_SOFTWARE_UPDATE_REQUEST' => 4,
    'MAIL_SOFTWARE_UPDATE_APPROVAL' => 5,
    'MAIL_SOFTWARE_UPDATE_REJECT' => 6,
    'MAIL_SOFTWARE_PROJECT_LINK_REQUEST' => 7,
    'MAIL_SOFTWARE_REGIST_CANCEL' => 8,

    //software type
    'SOFTWARE_TYPE_999' => 999,    //SOFTWARE_TYPE_PHONE_DRIVERS_VALUE

    //softwaretype name
    'SOFTWARE_TYPE_999_NAME' => 'Others',

    //software range buffers
    'SOFTWARE_RANGE_BUFFER' => 3,
    'SOFTWARE_RANGE_HEADER_BUFFER' => 1,
    'SOFTWARE_RANGE_INITIAL_VALUE' => 5,
    'SOFTWARE_TYPE_COUNTER_INCREMENT' => 1,
    'SOFTWARE_TYPE_EMPTY' => 0,
    'SOFTWARE_TYPE_COUNT' =>  6,
    'SOFTWARE_TYPE_MAX_CHAR_LINE' => 25,
    'SOFTWARE_NAME_MAX_CHAR_LINE' => 25,
    'SOFTWARE_REMARKS_MAX_CHAR_LINE' => 50,
    'DEFAULT_ROW_HEIGHT_VALUE' => '15',
    'DEFAULT_ROW_HEIGHT_UNIT' => 'pt',
    
    //laptop mail
    'MAIL_LAPTOP_NEW_REGISTRATION_REQUEST' => 1,
    'MAIL_LAPTOP_NEW_REGISTRATION_REJECTION' => 2,
    'MAIL_LAPTOP_NEW_REGISTRATION_APPROVAL' => 3,
    'MAIL_LAPTOP_DETAIL_UPDATE_REQUEST' => 4,
    'MAIL_LAPTOP_DETAIL_UPDATE_APPROVAL' => 5,
    'MAIL_LAPTOP_DETAIL_UPDATE_REJECTION' => 6,
    'MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST' => 7,
    'MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL' => 8,
    'MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION' => 9,
    'MAIL_LAPTOP_NEW_LINKAGE_BY_MANAGER_NOTIF' => 10,
    'MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST' => 11,
    'MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_APPROVAL' => 12,
    'MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REJECTION' => 13,
    'MAIL_LAPTOP_LINKAGE_UPDATE_BY_MANAGER_NOTIF' => 14,
    'MAIL_LAPTOP_CANCEL_REGIST' => 15,
    'MAIL_LAPTOP_CANCEL_UPDATE' => 16,
    'MAIL_LAPTOP_CANCEL_LINK' => 17,
    'MAIL_LAPTOP_CANCEL_LINK_UPDATE' => 18,

    //Search filter constant
    'SOFTWARE_FILTER_STATUS_ALL' => 5,
    'SOFTWARE_FILTER_TYPE_ALL' => 7,

    //Server Module
    'SIZE_UNITS' => [
        1 => 'B',
        2 => 'kB',
        3 => 'MB',
        4 => 'GB',
        5 => 'TB'
    ],

    'STATUS_NAMES' => [
        1 => 'Normal',
        2 => 'Stable',
        3 => 'Critical'
    ],

    'STATUS_CLASS' => [
        1 => 'text-black',
        2 => 'text-primary',
        3 => 'text-danger',
    ],

    'KB_TO_BYTES' => 1024,

    'SERVER_FILE_SHEET_COVER' => 1,
    'SERVER_FILE_SHEET_REVISION' => 2,
    'SOFTWARE_FILTER_TYPE_ALL' => 999,
    'SOFTWARE_FILTER_TYPE_ALL_NAME' => 'All',
    'FILE_TYPE_PDF' => 'pdf',

    //project roles
    'PROJECT_ROLES' => [
        1 => 'Team Lead',
        2 => 'Programmer',
        3 => 'QA',
    ],

    //projects mail
    'MAIL_PROJECT_EMPLOYEE_LINKAGE_BY_MANAGER' => 1,
    'MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST' => 2,
    'MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_BY_MANAGER' => 3,
    'MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST' => 4,
    'MAIL_PROJECT_DETAIL_UPDATE_REJECTION' => 5,
    'MAIL_PROJECT_DETAIL_UPDATE_APPROVAL' => 6,
    'MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_REJECTION' => 7,
    'MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL' => 8,
    'MAIL_PROJECT_NEW_LINKAGE_CANCELLATION' => 9,
    'MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_CANCELLATION' => 10,
    
    //Filter for Project List
    'PROJECT_STATUS_FILTER_ALL' => 3,
    'PROJECT_STATUS_FILTER_ONGOING' => 1,
    'PROJECT_STATUS_FILTER_FINISH' => 2,

    'PROJECT_STATUS_ONGOING_TEXT' => 'On-Going',
    'PROJECT_STATUS_FINISH_TEXT' => 'Finished',


    'SYSTEM_EMAIL' => 'devj-portal@awsys-i.com',

    // Message
    'SEND_NOTIFICATION_MESSAGE_SUCCESS' => "Successfully sent notifications to all active employees.",
    'DOWNLOAD_MESSAGE_SUCCESS' => "Successfully downloaded file.",

];