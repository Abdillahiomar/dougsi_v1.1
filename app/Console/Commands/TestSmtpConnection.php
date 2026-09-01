<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestSmtpConnection extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Test SMTP connection';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('Test email from Laravel', function (Message $msg) use ($email) {
                $msg->to($email)->subject('SMTP Test');
            });
            
            $this->info('✅ Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
        }
    }
}