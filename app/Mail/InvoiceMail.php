<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $pdfData;

    public function __construct($pedido, $pdfData)
    {
        $this->pedido  = $pedido;
        $this->pdfData = $pdfData;
    }

    public function build()
    {
        return $this
            ->subject("Tu factura #{$this->pedido->id}")
            ->view('emails.invoice')
            ->with(['pedido' => $this->pedido])
            ->attachData($this->pdfData, "factura_{$this->pedido->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
