<?php

namespace KenNebula\MPUPaymentIntegration;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use KenNebula\MPUPaymentIntegration\MPU;

class PackageServiceProvider extends ServiceProvider
{
    public function register() : void 
    {
        // Bind the MPU class to the service container
        $this->app->singleton(MPU::class, function($app) {
            return new MPU();
        });
    }

    public function boot() : void 
    {
        if ($this->app->runningInConsole()) {
            // Example: Publishing configuration file
            $this->publishes([
              __DIR__.'/config/config.php' => config_path('mpu.php'),
          ], 'config');
        
          }
    }

}