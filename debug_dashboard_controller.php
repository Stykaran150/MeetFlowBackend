<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'Boss User')->first();
Auth::login($user);

$request = Illuminate\Http\Request::create('/api/dashboard/stats', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

echo "--- Testing getStats ---\n";
try {
    $controller = new App\Http\Controllers\Api\DashboardController();
    $response = $controller->getStats($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    print_r($response->getData(true));
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n--- Testing getRecentActivity ---\n";
try {
    $response = $controller->getRecentActivity($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    // print_r($response->getData(true)); // potentially large
    echo "Data count: " . count($response->getData(true)['data']) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n--- Testing getUpcomingDeadlines ---\n";
try {
    $response = $controller->getUpcomingDeadlines($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    print_r($response->getData(true));
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
