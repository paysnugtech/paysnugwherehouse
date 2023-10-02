<?php
namespace App\Interfaces;

interface BankService {
    public function initiateTransfer($data);
    public function createAccountIndividual($data);
    public function checkStatus($transactionId);
    public function reversalStatus($transactionId);
    public function bankList();
    public function nameEnquiry($data);
}
