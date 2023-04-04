<?php

return [
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
    'POSITION_ASSSITANT_MANAGER_VALUE' => 8,
    'POSITION_MANAGER_VALUE' => 9,
    
    'POSITION_1_NAME' => 'Junior Research and Development',
    'POSITION_2_NAME' => 'Assistant Research and Development',
    'POSITION_3_NAME' => 'Senior Assistant Research and Development',
    'POSITION_4_NAME' => 'Associate Research and Development',
    'POSITION_5_NAME' => 'Senior Associate Research and Development',
    'POSITION_6_NAME' => 'Supervisor',
    'POSITION_7_NAME' => 'Adviser',
    'POSITION_8_NAME' => 'Assistant Manager',
    'POSITION_9_NAME' => 'Manager',

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

    //Software Mail Type
    'MAIL_SOFTWARE_NEW_REQUEST' => 1,
    'MAIL_SOFTWARE_NEW_APPROVAL' => 2,
    'MAIL_SOFTWARE_NEW_REJECTION' => 3,
    'MAIL_SOFTWARE_UPDATE_REQUEST' => 4,
    'MAIL_SOFTWARE_UPDATE_APPROVAL' => 5,
    'MAIL_SOFTWARE_UPDATE_REJECT' => 6,
    'MAIL_SOFTWARE_PROJECT_LINK_REQUEST' => 7,

    //software type
    'SOFTWARE_TYPE_999' => 999,    //SOFTWARE_TYPE_PHONE_DRIVERS_VALUE

    //softwaretype name
    'SOFTWARE_TYPE_999_NAME' => 'Others',

    //software range buffers
    'SOFTWARE_RANGE_BUFFER' => 4,
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

    //Search filter constant
    'SOFTWARE_FILTER_STATUS_ALL' => 5,
    'SOFTWARE_FILTER_TYPE_ALL' => 999,
    'SOFTWARE_FILTER_TYPE_PROD_UTIL' => 1,
    'SOFTWARE_FILTER_TYPE_ALL_NAME' => 'All',
    'FILE_TYPE_PDF' => 'pdf',
    
];