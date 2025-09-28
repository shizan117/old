<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CornMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $message;
    public $subject;
    public $name;
    public function __construct($message, $subject, $name)
    {
        $this->message = $message;
        $this->subject = $subject;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$host = request()->getHost();
        return $this->markdown('emails.admin.corn_mail')->subject($this->subject)->from('noreply@'.$host, $this->name);
    }
}
