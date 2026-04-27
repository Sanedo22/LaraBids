<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$requestData = [
    'draw' => 1,
    'columns' => [
        ['data' => 'id', 'name' => 'id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'user_info', 'name' => 'user_id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'type', 'name' => 'type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'reason_short', 'name' => 'reason', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '']],
        ['data' => 'reporter_info', 'name' => 'reported_by', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'status', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'created_at', 'name' => 'created_at', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']],
        ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '']]
    ],
    'order' => [['column' => '0', 'dir' => 'desc']],
    'start' => 0,
    'length' => 10,
    'search' => ['value' => '', 'regex' => 'false'],
    'status' => 'all',
    'type' => 'all'
];

$request = Illuminate\Http\Request::create("/admin/disputes/data", "GET", $requestData);
$controller = app(App\Http\Controllers\Admin\DisputeController::class);
echo $controller->data($request)->getContent();
