<?php

namespace App\Interfaces;

interface TransactionRepositoryInterface
{
    public function getTransactionFromSession();

    public function saveTransactionFromSession($data);

    public function saveTransaction($data);

    public function getTransactionByCode($code);

    public function getTransactionByCodeEmailPhone($code, $email, $phone);
}