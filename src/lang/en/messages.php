<?php

return [
    // Attendance
    'check_in_success' => 'Checked in successfully! Have a great day.',
    'already_checked_in' => 'You have already checked in today.',
    'not_attendance_time' => 'It is not currently within the attendance time.',
    'default_greeting' => 'Hello!',

    // Status
    'status_fetched' => 'Attendance status retrieved.',
    'today_list_fetched' => 'Today\'s attendance list retrieved.',
    'calendar_fetched' => 'Calendar data retrieved.',
    'attendance_list_fetched' => 'Attendance list retrieved.',
    'greeting_fetched' => 'Greeting retrieved.',

    // Admin
    'admin' => [
        'attendance_deleted' => 'Attendance record deleted.',
        'attendance_not_found' => 'Attendance record not found.',
        'consecutive_recalculated' => 'Consecutive days recalculated.',
        'settings_fetched' => 'Settings retrieved.',
        'settings_saved' => 'Settings saved.',
        'settings_save_failed' => 'Failed to save settings.',
        'delete_failed' => 'Failed to delete attendance record.',
    ],

    // Consecutive
    'consecutive' => [
        'weekly' => 'Weekly (7 days)',
        'monthly' => 'Monthly (30 days)',
        'yearly' => 'Yearly (365 days)',
    ],

    // Validation
    'validation' => [
        'greeting_max' => 'Greeting must be 200 characters or less.',
        'base_point_min' => 'Base point must be 0 or more.',
        'start_hour_min' => 'Start hour must be 0 or more.',
        'start_hour_max' => 'Start hour must be 23 or less.',
        'end_hour_min' => 'End hour must be 1 or more.',
        'end_hour_max' => 'End hour must be 24 or less.',
    ],

    // Auto attendance
    'auto_attendance' => [
        'success' => 'Auto attendance completed.',
        'disabled' => 'Auto attendance is disabled.',
    ],

    // Activity log
    'activity' => [
        'check_in' => 'Check in',
        'auto_check_in' => 'Auto check in',
        'admin_delete' => 'Admin attendance delete',
    ],
];
