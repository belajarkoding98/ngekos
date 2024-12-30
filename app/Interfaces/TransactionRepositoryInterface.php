<?php

namespace App\Interfaces;

interface TransactionRepositoryInterface
{
    public function getTransactionFromSession();

    public function saveTransactionFromSession($data);
}