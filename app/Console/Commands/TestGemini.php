<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGemini extends Command
{
    protected $signature = 'test:gemini';
    protected $description = 'Test Gemini API connection and list models';

    public function handle()
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            $this->error('GEMINI_API_KEY not found in .env');
            return;
        }

        try {
            $client = \Gemini::client($apiKey);
            $this->info("Client Class: " . get_class($client));
            $this->info("Methods: " . implode(', ', get_class_methods($client)));
            
            // Try to find a model listing method if obvious
            if (method_exists($client, 'models')) {
                 $response = $client->models()->list();
                 $this->info("Response type: " . gettype($response));
                 if (is_object($response)) {
                    $this->info("Response class: " . get_class($response));
                 }
                 
                 // If it's a wrapper, try to extract proper list
                 $models = $response;
                 if (isset($response->models)) {
                     $models = $response->models;
                 }
                 
                 $this->info("Models count: " . (is_array($models) || $models instanceof \Countable ? count($models) : 'N/A'));
                 
                 $this->info("First item dump:");
                 $first = null;
                 foreach($models as $m) { $first = $m; break; }
                 $this->info(var_export($first, true));
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
