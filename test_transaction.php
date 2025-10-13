<?php

require_once 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

// Create app instance
$app = new \Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Bootstrap the app
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

// Set up facades
Facade::setFacadeApplication($app);

// Get the first donor
$donatur = \App\Models\Donatur::first();
echo "Using donor: " . $donatur->nama . " (Type: " . $donatur->jenis_donatur . ")\n";

// Create a test transaction
$transactionData = [
    'donatur_id' => $donatur->id,
    'jenis_zis' => 'zakat',
    'jumlah' => 100000,
    'tanggal_transaksi' => date('Y-m-d'),
    'keterangan' => 'Test transaction'
];

echo "Creating transaction with data:\n";
print_r($transactionData);

// Call the controller method directly for testing
$controller = new \App\Http\Controllers\Api\ZisTransactionController();
$request = new \Illuminate\Http\Request();
$request->setMethod('POST');
$request->request->add($transactionData);

try {
    $response = $controller->store($request);
    echo "Response:\n";
    print_r($response->getData());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}