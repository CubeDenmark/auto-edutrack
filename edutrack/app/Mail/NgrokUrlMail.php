<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class NgrokUrlMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $ngrokUrl) {}

    public function build()
    {
        return $this->from(new Address('cubesdenmark0624@gmail.com', 'EduTrack'))
                    ->subject('New URL for Client')
                    ->view('emails.ngrok')
                    ->with(['ngrokUrl' => $this->ngrokUrl]);
    }
}




// 1.0 working
// namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;

// class NgrokUrlMail extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $ngrokUrl;

//     /**
//      * Create a new message instance.
//      */
//     public function __construct($ngrokUrl)
//     {
//         $this->ngrokUrl = $ngrokUrl;
//     }

//     /**
//      * Build the message.
//      */
//     public function build()
//     {
//         return $this->subject('Ngrok URL Update')
//                     ->view('emails.ngrok')
//                     ->with(['ngrokUrl' => $this->ngrokUrl]);
//     }
// }
