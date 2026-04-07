<?php
require 'vendor/autoload.php'; 
$app = require_once 'bootstrap/app.php'; 
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); 
$kernel->bootstrap(); 
$request = Illuminate\Http\Request::create('/admin/disputes/data', 'GET', ['status' => 'all']); 
$controller = app(App\Http\Controllers\Admin\DisputeController::class); 
$response = $controller->data($request); 
file_put_contents('output_test.json', $response->getContent());
