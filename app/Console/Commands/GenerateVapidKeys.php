<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'pwa:generate-vapid';
    protected $description = 'Generate VAPID keys for Web Push Notifications';

    public function handle()
    {
        $this->info('Generating VAPID keys...');
        
        $keys = VAPID::createVapidKeys();
        
        $this->info("\nAdd these to your .env file:\n");
        $this->info("VAPID_PUBLIC_KEY={$keys['publicKey']}");
        $this->info("VAPID_PRIVATE_KEY={$keys['privateKey']}");
        
        // Optionally save to .env
        if ($this->confirm('Do you want to automatically add these to your .env file?')) {
            $this->addToEnv('VAPID_PUBLIC_KEY', $keys['publicKey']);
            $this->addToEnv('VAPID_PRIVATE_KEY', $keys['privateKey']);
            $this->info('Keys added to .env file successfully!');
        }
        
        return Command::SUCCESS;
    }
    
    protected function addToEnv($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            if (strpos($content, "{$key}=") !== false) {
                $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}\n";
            }
            
            file_put_contents($path, $content);
        }
    }
}