<?php

return [
    'visitor' => [
        'name' => 'Visitor',
        'permissions' => [
            'project_files_view',
            'project_documents_view'
        ]
    ],

    'manager' => [
        'name' => 'Manager',
        'permissions' => [
            'project_files_view',
            'project_documents_view',
            'project_files_manage',
            'project_documents_manage',
            'project_manage',
        ]
    ],

    'administrator' => [
        'name' => 'Administrator',
        'permissions' => [
            'project_files_view',
            'project_documents_view',
            'project_files_manage',
            'project_documents_manage',
            'project_manage',
            'project_delete',
            'project_view_logs',
            'project_restore_deleted',
            'project_transfer',
            'company_create',
            'company_edit',
            'project_restore',
        ]
    ],

    'super_user' => [
        'name' => 'Super user',
        'permissions' => [
            'all'
        ]
    ]
];
