<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PayUController extends Controller
{
    private $key;
    private $salt;
    private $baseUrl;

    public function __construct()
    {
        $this->key = env('PAYU_KEY');
        $this->salt = env('PAYU_SALT');
        $this->baseUrl = env('PAYU_BASE_URL');
    }

    /**
     * Get payment parameters for a specific auction win.
     * Designed for mobile SDK integration.
     */
    public function getPaymentParams(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);
        $user = $request->user();

        // 1. Safety Measures: Authorization
        if ($auction->winner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not the winner of this auction.'
            ], 403);
        }

        // 2. Safety Measures: Check if already paid
        $existingPayment = Payment::where('auction_id', $auction->id)
            ->where('status', 'success')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'This auction has already been paid for.',
                'payment_id' => $existingPayment->payu_id,
                'status' => 'success'
            ], 422);
        }

        // 3. Prepare Payment Data
        $txnid = 'API_TXN_' . Str::upper(Str::random(10));
        $winningBid = $auction->current_price;
        $commission = $winningBid * 0.05; // 5% Platform Commission
        $amount = $winningBid;
        $productinfo = "Payment for Auction #" . $auction->id . ": " . $auction->title;
        $firstname = $user->name;
        $email = $user->email;
        $phone = $user->phone ?? '9999999999';

        // 4. Persistence: Log the pending transaction
        Payment::updateOrCreate(
            ['auction_id' => $auction->id, 'user_id' => $user->id, 'status' => 'pending'],
            [
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'additional_data' => [
                    'winning_bid' => $winningBid,
                    'commission' => $commission,
                    'commission_percentage' => 5.00,
                    'payout_amount' => $winningBid - $commission,
                    'source' => 'api'
                ]
            ]
        );

        // 5. Security: Server-side Hash Generation
        // Hash sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT
        $hashString = "{$this->key}|{$txnid}|{$amount}|{$productinfo}|{$firstname}|{$email}|||||||||||{$this->salt}";
        $hash = strtolower(hash('sha512', $hashString));

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $this->key,
                'txnid' => $txnid,
                'amount' => (string)$amount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'email' => $email,
                'phone' => $phone,
                'surl' => route('payment.payu.callback'),
                'furl' => route('payment.payu.callback'),
                'hash' => $hash,
                'action' => $this->baseUrl,
                'commission_details' => [
                    'winning_bid' => $winningBid,
                    'commission' => $commission,
                ]
            ]
        ]);
    }

    /**
     * Check the status of a specific transaction.
     */
    public function getPaymentStatus(Request $request, $txnid)
    {
        $payment = Payment::where('txnid', $txnid)->firstOrFail();

        // Safety Measure: Only the owner or an admin can check status
        if ($payment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to transaction status.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'txnid' => $payment->txnid,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'payu_id' => $payment->payu_id,
                'created_at' => $payment->created_at->toDateTimeString(),
                'updated_at' => $payment->updated_at->toDateTimeString(),
            ]
        ]);
    }
}
