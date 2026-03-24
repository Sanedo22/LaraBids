<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function summary(Auction $auction)
    {
        // Ensure user is the winner
        if ($auction->winner_id !== auth()->id()) {
            return redirect()->back()->with('error', 'You are not the winner of this auction.');
        }

        // Check if already paid
        $existingPayment = Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
        if ($existingPayment) {
            return redirect()->route('user.winning-items')->with('info', 'This auction has already been paid for.');
        }

        $winningBid = $auction->current_price;
        $commission = $winningBid * 0.05; // 5% Platform Commission
        $totalAmount = $winningBid; // Buyer pays the winning bid amount

        return view('website.payments.summary', compact('auction', 'winningBid', 'commission', 'totalAmount'));
    }

    public function checkout(Auction $auction)
    {
        // Ensure user is the winner
        if ($auction->winner_id !== auth()->id()) {
            return redirect()->back()->with('error', 'You are not the winner of this auction.');
        }

        // Check if already paid
        $existingPayment = Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
        if ($existingPayment) {
            return redirect()->route('user.winning-items')->with('info', 'This auction has already been paid for.');
        }

        $txnid = 'TXN_' . Str::upper(Str::random(10));
        $winningBid = $auction->current_price;
        $commission = $winningBid * 0.05;
        $amount = $winningBid;
        $productinfo = "Payment for Auction #" . $auction->id . ": " . $auction->title;
        $firstname = auth()->user()->name;
        $email = auth()->user()->email;
        $phone = auth()->user()->phone ?? '9999999999';

        // Create or update pending payment record
        Payment::updateOrCreate(
            ['auction_id' => $auction->id, 'user_id' => auth()->id(), 'status' => 'pending'],
            [
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'additional_data' => [
                    'winning_bid' => $winningBid,
                    'commission' => $commission,
                    'commission_percentage' => 5.00,
                    'payout_amount' => $winningBid - $commission,
                ]
            ]
        );

        // Hash generation: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT
        $hashString = "{$this->key}|{$txnid}|{$amount}|{$productinfo}|{$firstname}|{$email}|||||||||||{$this->salt}";
        $hash = strtolower(hash('sha512', $hashString));

        $data = [
            'key' => $this->key,
            'txnid' => $txnid,
            'amount' => $amount,
            'productinfo' => $productinfo,
            'firstname' => $firstname,
            'email' => $email,
            'phone' => $phone,
            'surl' => url('/api/payment/payu/callback'),
            'furl' => url('/api/payment/payu/callback'),
            'hash' => $hash,
            'action' => $this->baseUrl,
        ];

        return view('website.payments.payu_checkout', compact('data'));
    }

    public function callback(Request $request)
    {
        $status = $request->status;
        $firstname = $request->firstname;
        $amount = $request->amount;
        $txnid = $request->txnid;
        $posted_hash = $request->hash;
        $key = $request->key;
        $productinfo = $request->productinfo;
        $email = $request->email;
        $salt = $this->salt;

        // Additional params from PayU
        $udf1 = $request->udf1; $udf2 = $request->udf2; $udf3 = $request->udf3; 
        $udf4 = $request->udf4; $udf5 = $request->udf5;

        // Verify reverse hash: SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
        $retHashSeq = "{$salt}|{$status}||||||{$udf5}|{$udf4}|{$udf3}|{$udf2}|{$udf1}|{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
        $hash = strtolower(hash('sha512', $retHashSeq));

        $payment = Payment::where('txnid', $txnid)->firstOrFail();

        if ($hash != $posted_hash) {
            $payment->update([
                'status' => 'failed',
                'additional_data' => array_merge($request->all(), ['error' => 'Hash mismatch']),
            ]);
            return redirect()->route('user.winning-items')->with('error', 'Payment verification failed (Invalid Hash).');
        }

        if ($status === 'success') {
            $payment->update([
                'status' => 'success',
                'payu_id' => $request->mihpayid,
                'additional_data' => $request->all(),
            ]);

            // Optional: Mark auction as paid if we had a paid_at field, or notify seller
            
            return redirect()->route('user.winning-items')->with('success', 'Payment successful! Order confirmed.');
        } else {
            $payment->update([
                'status' => 'failed',
                'additional_data' => $request->all(),
            ]);
            return redirect()->route('user.winning-items')->with('error', 'Payment failed or cancelled.');
        }
    }
}
