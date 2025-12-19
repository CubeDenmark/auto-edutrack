<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\NgrokUrlMail;

class SendNgrokUrl extends Command
{
    // protected $signature = 'ngrok:send-url';
    protected $signature = 'ngrok:send-url {--url=}';

    protected $description = 'Fetches the ngrok URL and sends an email notification.';


    public function handle()
    {
        // Get the URL from the command option
        $ngrokUrl = $this->option('url');

        // If no URL is provided, show an error and exit
        if (!$ngrokUrl) {
            $this->error('No URL provided. Use --url="your-ngrok-url"');
            return;
        }

        // Define the list of recipients
        $recipients = array_filter(explode(',', env('NGROK_RECIPIENTS')));

        // Validate emails
        $recipients = array_filter($recipients, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($recipients)) {
            $this->error("No valid recipients found!");
            return;
        }

        // Send the email
        Mail::to($recipients)->send(new NgrokUrlMail($ngrokUrl));

        $this->info("Ngrok URL sent successfully to: " . implode(', ', $recipients));
    }



    // NOTE:

    // The error "No valid recipients found!" means the script is not correctly reading the email addresses from NGROK_RECIPIENTS in your .env file.

    // Possible Causes & Fixes:
    // 1️⃣ Clear Config Cache
    // Laravel caches environment variables. Run this to refresh them:

    // sh
    // Copy
    // Edit
    // php artisan config:clear
    // php artisan cache:clear
    // Then, try again:

    // sh
    // Copy
    // Edit
    // php artisan ngrok:send-url --url="https://your-ngrok-url.ngrok-free.app"

    // public function handle()
    // {
    //     // Fetch ngrok URL from its local API
    //     // $response = Http::get('http://127.0.0.1:4040/api/tunnels');
    //     // $response = Http::get('curl http://host.docker.internal:4040/api/tunnels');
    //     $response = Http::get('http://host.docker.internal:4040/api/tunnels');


    //     if ($response->failed()) {
    //         $this->error('Failed to get Ngrok URL. Is ngrok running?');
    //         return;
    //     }

    //     $ngrokData = $response->json();
    //     $ngrokUrl = $ngrokData['tunnels'][0]['public_url'] ?? null;

    //     if (!$ngrokUrl) {
    //         $this->error('Ngrok URL not found.');
    //         return;
    //     }

    //     // Define the list of recipients
    //     $recipients = array_filter(explode(',', env('NGROK_RECIPIENTS'))); // Removes empty values


    //     // Remove empty or invalid emails
    //     $recipients = array_filter($recipients, function ($email) {
    //         return filter_var($email, FILTER_VALIDATE_EMAIL);
    //     });

    //     if (empty($recipients)) {
    //         $this->error("No valid recipients found!");
    //         return;
    //     }

    //     Mail::to($recipients)->send(new NgrokUrlMail($ngrokUrl));

    //     $this->info("Ngrok URL sent successfully to: " . implode(', ', $recipients));
    // }
}




//v1.2 not working
// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NgrokUrlMail;

// class SendNgrokUrl extends Command
// {
//     protected $signature = 'ngrok:send-url';
//     protected $description = 'Sends the Ngrok URL via email.';

//     public function handle()
//     {
//         // Retrieve the Ngrok URL from the environment variable
//         $ngrokUrl = getenv('NGROK_URL') ?: env('NGROK_URL');

//         if (!$ngrokUrl) {
//             $this->error('Ngrok URL is not set in environment variables.');
//             return;
//         }

//         // Get recipients from .env file
//         $recipients = array_filter(explode(',', env('NGROK_RECIPIENTS')));

//         // Validate email addresses
//         $recipients = array_filter($recipients, function ($email) {
//             return filter_var($email, FILTER_VALIDATE_EMAIL);
//         });

//         if (empty($recipients)) {
//             $this->error("No valid recipients found!");
//             return;
//         }

//         // Send email
//         Mail::to($recipients)->send(new NgrokUrlMail($ngrokUrl));

//         $this->info("Ngrok URL sent successfully to: " . implode(', ', $recipients));
//     }
// }




// 1.0 Working - single recipient
// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NgrokUrlMail;

// class SendNgrokUrl extends Command
// {
//     protected $signature = 'ngrok:send-url';
//     protected $description = 'Fetches the ngrok URL and sends an email notification.';

//     public function handle()
//     {
//         // Fetch ngrok URL from its local API
//         $response = Http::get('http://127.0.0.1:4040/api/tunnels');

//         if ($response->failed()) {
//             $this->error('Failed to get Ngrok URL. Is ngrok running?');
//             return;
//         }

//         $ngrokData = $response->json();
//         $ngrokUrl = $ngrokData['tunnels'][0]['public_url'] ?? null;

//         if (!$ngrokUrl) {
//             $this->error('Ngrok URL not found.');
//             return;
//         }

//         // Send email
//         Mail::to('cubedmk@gmail.com')->send(new NgrokUrlMail($ngrokUrl));

//         $this->info("Ngrok URL sent successfully: $ngrokUrl");
//     }
// }
