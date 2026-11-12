<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New enquiry: '.$this->enquiry->fullName().' ('.config('gnt.form_names.'.$this->enquiry->form_name, $this->enquiry->form_name).')');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-enquiry');
    }
}
