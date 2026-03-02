<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation – Living Legacy',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orderAccepted',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if (!empty($this->data['order']) && $this->data['order'] instanceof Order) {
            try {
                $order = $this->data['order'];
                $orderNumber = substr($order->uuid, 0, 8);
                $attachments[] = Attachment::fromData(function () use ($order) {
                    $pdf = Pdf::loadView('admin.pages.reseller.invoiceView', compact('order'));
                    return $pdf->output();
                }, "Invoice-{$orderNumber}.pdf")
                    ->withMime('application/pdf');
            } catch (\Throwable $e) {
                \Log::warning('Failed to attach invoice PDF to order confirmation email: ' . $e->getMessage());
            }
        }

        return $attachments;
    }
}
