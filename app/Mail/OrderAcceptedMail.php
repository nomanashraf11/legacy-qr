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
            $order = $this->data['order'];
            $orderNumber = $order->id;
            try {
                // Generate PDF eagerly so we catch errors here; lazy closure would throw during send
                $pdf = Pdf::loadView('admin.pages.reseller.invoiceView', compact('order'));
                $pdfContent = $pdf->output();
                $attachments[] = Attachment::fromData(fn () => $pdfContent, "Invoice-{$orderNumber}.pdf")
                    ->withMime('application/pdf');
            } catch (\Throwable $e) {
                \Log::warning('Failed to attach invoice PDF to order confirmation email: ' . $e->getMessage(), [
                    'order_uuid' => $order->uuid ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);
                // Email still sends without attachment
            }
        }

        return $attachments;
    }
}
