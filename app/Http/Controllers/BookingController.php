<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingShowRequest;
use App\Http\Requests\CustomerInformationStoreRequest;
use App\Interfaces\BoardingHouseRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use Illuminate\Http\Request;

class BookingController extends Controller
{

private TransactionRepositoryInterface $transactionRepositoy;
private BoardingHouseRepositoryInterface $boardingHouseRepository;

public function __construct
(
    TransactionRepositoryInterface $transactionRepository,
    BoardingHouseRepositoryInterface $boardingHouseRepository,
    ) {
        $this->transactionRepositoy = $transactionRepository;
        $this->boardingHouseRepository = $boardingHouseRepository;
    }

    public function booking(Request $request, $slug)
    {
        $this->transactionRepositoy->saveTransactionFromSession($request->all());

        return redirect()->route('booking.information', $slug);
    }

    public function information($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $transaction = $this->transactionRepositoy->getTransactionFromSession();
        
        $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

        return view('pages.booking.information', compact('boardingHouse', 'transaction', 'room'));
    }

    public function saveInformation(CustomerInformationStoreRequest $request, $slug)
    {
        $data = $request->validated();
        
        $this->transactionRepositoy->saveTransactionFromSession($data);

        return redirect()->route('booking.checkout', $slug);
    } 

    public function checkout($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $transaction = $this->transactionRepositoy->getTransactionFromSession();
        
        $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

        return view('pages.booking.checkout', compact('boardingHouse', 'transaction', 'room'));
    }

    public function payment(Request $request)
    {
        $this->transactionRepositoy->saveTransactionFromSession(($request->all()));

        $transaction = $this->transactionRepositoy->saveTransaction($this->transactionRepositoy->getTransactionFromSession());

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = config('midtrans.isProduction');
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = config('midtrans.is3ds');

        $params = array(
            'transaction_details' => array(
                'order_id' => $transaction->code,
                'gross_amount' => $transaction->total_amount,
            ),
            'customer_details' => array(
                'first_name'       => $transaction->name,
                'email'            => $transaction->email,
                'phone'            => $transaction->phone_number,
            )
        );
        
        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

        return redirect($paymentUrl);
    }

    public function success(Request $request) 
    {
        $transaction = $this->transactionRepositoy->getTransactionByCode($request->order_id);

        if(!$transaction){
            return redirect()->route('home');
        }

        return view('pages.booking.success', compact('transaction'));
    }

    public function check()
    {
        return view('pages.booking.check-booking');
    }

    public function show(BookingShowRequest $request)
    {
        $transaction = $this->transactionRepositoy->getTransactionByCodeEmailPhone($request->code, $request->email, $request->phone_number);

        if(!$transaction) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan');
        }
        return view('pages.booking.detail', compact('transaction'));
    }
}
