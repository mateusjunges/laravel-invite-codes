<?php

return [
    /*
    |--------------------------------------------------------------------------
    |  Models
    |--------------------------------------------------------------------------
    |
    | When using this package, we need to know which Eloquent Model should be used
    | to retrieve your invites. Of course, it is just the basics models
    | needed, but you can use whatever you like.
    |
    */
    'models' => [
        'invite_model' => \Junges\InviteCodes\Http\Models\Invite::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    | Specify the basics authentication tables that you are using.
    | Once you required this package, the following tables are
    | created by default when you run the command
    |
    | php artisan migrate
    |
    | If you want to change this tables, please keep the basic structure unchanged.
    |
    */
    'tables' => [
        'invites_table' => 'invites',
    ],

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    | To use the ProtectedByInviteCode middleware provided by this package, you need to
    | specify the email column you use in the model you use for authentication.
    | If not specified, only invite code with no use restrictions can be used in this middleware.
    |
    */
    'user' => [
        'email_column' => 'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom migrations
    |--------------------------------------------------------------------------
    | If you want to publish this package migrations and edit with new custom columns, change it to true.
    */
    'custom_migrations' => false,
];
