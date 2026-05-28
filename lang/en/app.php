<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Language Lines — English
    |--------------------------------------------------------------------------
    */

    'app_name'  => 'Transaction Monitor',
    'dashboard' => 'Dashboard',
    'welcome'   => 'Welcome back, :name',

    // Navigation
    'nav' => [
        'transactions'  => 'Transactions',
        'fraud_alerts'  => 'Fraud Alerts',
        'users'         => 'Users',
        'employees'     => 'Employees',
        'attendance'    => 'Attendance',
        'tasks'         => 'Tasks',
        'departments'   => 'Departments',
        'holidays'      => 'Holidays',
        'projects'      => 'Projects',
        'reports'       => 'Reports',
        'settings'      => 'Settings',
        'logout'        => 'Logout',
        'profile'       => 'Profile',
    ],

    // Transactions
    'transactions' => [
        'title'          => 'Transactions',
        'create'         => 'New Transaction',
        'edit'           => 'Edit Transaction',
        'id'             => 'Transaction ID',
        'type'           => 'Type',
        'amount'         => 'Amount',
        'status'         => 'Status',
        'sender'         => 'Sender',
        'receiver'       => 'Receiver',
        'flagged'        => 'Flagged',
        'risk_score'     => 'Risk Score',
        'created_at'     => 'Date',
        'no_records'     => 'No transactions found.',
    ],

    // Fraud
    'fraud' => [
        'title'          => 'Fraud Alerts',
        'open'           => 'Open',
        'investigating'  => 'Investigating',
        'resolved'       => 'Resolved',
        'dismissed'      => 'Dismissed',
        'severity'       => 'Severity',
        'critical'       => 'Critical',
        'high'           => 'High',
        'medium'         => 'Medium',
        'low'            => 'Low',
        'assign'         => 'Assign',
        'resolve'        => 'Resolve',
    ],

    // Employees / Attendance
    'attendance' => [
        'title'       => 'Attendance',
        'check_in'    => 'Check In',
        'check_out'   => 'Check Out',
        'present'     => 'Present',
        'absent'      => 'Absent',
        'on_leave'    => 'On Leave',
        'late'        => 'Late',
        'hours'       => 'Hours Worked',
    ],

    // Common
    'common' => [
        'save'          => 'Save',
        'cancel'        => 'Cancel',
        'delete'        => 'Delete',
        'edit'          => 'Edit',
        'view'          => 'View',
        'search'        => 'Search',
        'filter'        => 'Filter',
        'export'        => 'Export',
        'actions'       => 'Actions',
        'status'        => 'Status',
        'active'        => 'Active',
        'inactive'      => 'Inactive',
        'yes'           => 'Yes',
        'no'            => 'No',
        'confirm_delete'=> 'Are you sure you want to delete this?',
        'success'       => 'Operation completed successfully.',
        'error'         => 'An error occurred. Please try again.',
        'no_data'       => 'No records found.',
        'loading'       => 'Loading...',
        'of'            => 'of',
        'page'          => 'Page',
        'per_page'      => 'Per page',
    ],

    // Auth
    'auth' => [
        'login'              => 'Sign In',
        'logout'             => 'Sign Out',
        'email'              => 'Email Address',
        'password'           => 'Password',
        'remember'           => 'Remember Me',
        'forgot_password'    => 'Forgot Password?',
        'failed'             => 'These credentials do not match our records.',
    ],
];
