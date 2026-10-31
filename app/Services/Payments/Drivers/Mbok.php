<?php

namespace App\Services\Payments\Drivers;

/**
 * Bank of Khartoum mBoK (بنكك).
 *
 * mBoK has no public merchant checkout API, so the platform uses a hybrid flow:
 *  1. Customer sees the merchant's account number/name + exact amount + order number to put in the comment.
 *  2. Customer pays from the bankak app and uploads the screenshot + transaction ID (رقم العملية).
 *  3. ReceiptVerifier runs automatic checks (duplicate tx-id, duplicate image hash, amount sanity).
 *  4. Merchant/accountant approves in one tap (or via WhatsApp quick reply). Auto-approve hook is ready
 *     for when an official BoK merchant API / SMS-notification parser is connected (see docs).
 */
class Mbok extends BankTransfer
{
    public function key(): string
    {
        return 'mbok';
    }

    public function label(): string
    {
        return 'بنكك (mBoK)';
    }
}
