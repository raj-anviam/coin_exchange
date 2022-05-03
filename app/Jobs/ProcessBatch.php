<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Denpa\Bitcoin\Client as BitcoinClient;
use App\Http\Controllers\SessionController;
use App\Models\Batch;
use App\Models\IntermediateAddress;
use Session;

class ProcessBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $bitcoind, $sessionId;
    public $tries = 3;
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $sessionId = $this->sessionId;
            $batch = Batch::whereSessionId($sessionId)->first();
            $this->bitcoind = new BitcoinClient('http://someuser:somepassword@localhost:18332/');
            $session = new SessionController;

            if(is_null($batch->status)) {

                // do {
                    $received = $this->receiveTransaction($batch->address);
                    if($received) {
                        // update sessions - transaction recieved, awaiting confirmation
                        Batch::where('session_id', $sessionId)->update(['status' => 'started']);
                    }
                    
                // }
                // while(!$received);
                
                // recursion
                sleep(10);
                \App\Jobs\ProcessBatch::dispatch($sessionId);
                return true;
            }

            if($batch->status == 'started') {

                // do {
                    // check btc recieved
                    $balance = $this->confirmTransaction($sessionId, $session);
                // }
                // while(!$balance);
                
                // recursion
                sleep(10);
                \App\Jobs\ProcessBatch::dispatch($sessionId);
                return true;
            }

            if($batch->status == 'received' || $batch->status == 'sent-intermediate') {

                // check btc recieved and confirmed in intermediate wallets
                $wallets = $batch->intermediateAddrersses;
                foreach($wallets as $wallet) {
                    
                    do {
                        $received = $this->receiveTransaction($wallet->address, $wallet->wallet);
                        if($received) {
                            IntermediateAddress::whereId($wallet->id)->update(['status' => 'started']);
                            // return false;
                        }
                    }
                    while(!$received);
                    
                    do {
                        // check btc confirmed
                        $confirmed = $session->isReceived($wallet->address, $wallet->wallet);
                        
                        if($confirmed) {
                            IntermediateAddress::whereId($wallet->id)->update(['status' => 'received']);
                            
                            $test = $session->processIntermediate($wallet->id);
                            
                            IntermediateAddress::whereId($wallet->id)->update(['status' => 'sent-final']);
                        }

                        sleep(10);
                        error_log('bitcoins not confirmed in intermediate wallet, retrying in 10 seconds ..............');
                        // return false;
                    }
                    while(!$confirmed);

                    
                }
            }

            Batch::where('session_id', $this->sessionId)->update(['status' => 'complete']);

            return true;
        }
        catch(\Exception $e) {  
            \Log::error($e);
            Session::flash('error', $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed() {
        Batch::where('session_id', $this->sessionId)->update(['status' => 'failed']);
    }
    
    public function receiveTransaction($address, $wallet = 'test_wallet') {
        // get all transactions
        $transactions = $this->bitcoind->wallet($wallet)->listTransactions()->get();


        // check transaction on given address
        if(isset($transactions['address'])) {
            $received = $transactions['address'] == $address ? $transactions: false;
        }
        else {
            $received = \Arr::where($transactions, function ($value, $key) use ($address) {
                return $value['address'] == $address;
            });

            $received = head($received);
        }

        if($received) {
            return $received;
        }
        else {
            sleep(10);
            error_log('bitcoins not received, retrying in 10 seconds ..............');
            return false;
        }
    }

    public function confirmTransaction($sessionId, $session) {
        $batch = Batch::whereSessionId($sessionId)->first();
                
        $balance = $session->isReceived($batch->address);

        if($balance) {
            // update sessions - balance received
            Batch::where('session_id', $sessionId)->update(['status' => 'received']);
            
            // process the batch
            $session->processBatch($sessionId);

            Batch::where('session_id', $sessionId)->update(['status' => 'sent-intermediate']);

            return $balance;
        }

        // sleep(10);
        error_log('bitcoins not confirmed, retrying in 10 seconds ..............');
        return false;
    }
}
