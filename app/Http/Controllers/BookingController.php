<?php

namespace App\Http\Controllers;

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
        // dd(session()->get('transaction'));

        return redirect()->route('booking.information', $slug);
    }

    public function information($slug)
    {
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $transaction = $this->transactionRepositoy->getTransactionFromSession();
        
        $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

        return view('pages.booking.information', compact('boardingHouse', 'transaction', 'room'));
    }

    public function check()
    {
        return view('pages.check-booking');
    }
}
