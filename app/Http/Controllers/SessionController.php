<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\Batch;
use App\Models\IntermediateAddress;
use App\Models\Wallet;
use App\Models\Transaction;
use Session, Str, Response;

class SessionController extends BaseController
{   
    
    public function create(Request $request) {

        // Session::put('TNLFGHUIWA');
        
        $batch = Batch::whereSessionId(Session::get('session_id'))->whereNull('status')->first();
        
        if(is_null($batch)) {

            $batch = Batch::create([
                'session_id' => strtoupper(Str::random(10)),
                'address' => $this->bitcoind->wallet('test_wallet')->getNewAddress()->get()
            ]);

            IntermediateAddress::create([
                'batch_id' => $batch->id,
                'address' => $this->bitcoind->wallet('wallet1')->getNewAddress()->get(),
                'wallet' => 'wallet1',
                'status' => 0
            ]);

            Session::put('session_id', $batch->session_id);
            Session::save();
        }
        
        return view('add-session', compact('batch'));
    }

    public function intermediateAddessStore(Request $request) {
        $batch = Batch::whereSessionId(Session::get('session_id'))->first();
        $addresses = \Arr::pluck($batch->intermediateAddrersses->toArray(), ['wallet']);
        $addresses[] = 'test_wallet';
        $addresses[] = 'commissions';
        
        $query = Wallet::whereNotIn('name', $addresses);
        $walletCount = $query->count();
        
        if($walletCount) {
            $wallet = $query->value('name');
            
            $newAddress = $this->bitcoind->wallet($wallet)->getNewAddress()->get();
            
            IntermediateAddress::create([
                'batch_id' => $batch->id,
                'address' => $newAddress,
                'wallet' => $wallet,
                'status' => 0
            ]);
            return Response::json(['data' => ['address' => $newAddress, 'count' => $walletCount]], 200);
        }

        return Response::json(['data' => ['error' => 'wallets not available']], 403);
        
    }

    public function addJob(Request $request) {
        \App\Jobs\ProcessBatch::dispatch(Session::get('session_id'));

        Batch::whereSessionId(Session::get('session_id'))->update(['final_address' => $request->final_address]);
        
        return Response::json(['data' => ['message' => 'Your Job Has Been Added'], 'status' => true], 200);
    }

    public function processBatch($sessionId) {
        $batch = Batch::whereSessionId($sessionId)->first();

        $balance = $this->isReceived($batch->address);
        
        // check bitcoins received        
        if(!$balance)
            return Response::json(['data' => ['error' => 'Bitcoins not received'], 'status' => false], 200);

        // check available balance
        if(!count($balance))
            throw new \Exception('Unspent Not Found');

        $rawTransactionPayload = array(
            array(
                'txid' => $balance['txid'],
                'vout' => $balance['vout']
            ),
        );
        
        $privateKey = $this->bitcoind->dumpPrivKey($batch->address)->get();

        $wallets = $this->bitcoind->listWallets()->get();
        $totalWallets = is_array($wallets)? count($wallets): 1;

        if($totalWallets >= $batch->intermediateAddrersses->count()) {

            $addresses = [];
            $commission = 0;
            foreach($batch->intermediateAddrersses as $key => $wallet) {

                $address = $wallet->address;
                
                $amount = ($balance['amount'] / count($wallets));

                $commission =+ (5 / 100) * $amount;
                $totalCommission =+ (5 / 100) * $amount;
                
                $addresses[] = array($address => number_format($amount - $commission, 8));

                $transactions[] = array($address => $wallet->wallet, 'type' => 1);
            }
            
            $address = $this->bitcoind->wallet('commissions')->getNewAddress()->get();
            
            $transactions[] = array($address => 'commissions', 'type' => 2);

            $addresses[] = array($address => number_format($totalCommission, 8 ));
            
            // make transaction
            $rawTransactionOutput = $addresses;

            $unsigedHash = $this->bitcoind->createRawTransaction($rawTransactionPayload, $rawTransactionOutput)->get();
            $rawTransactionPayload[0]['scriptPubKey'] = $balance['scriptPubKey'];
            $hash = $this->bitcoind->signRawTransactionWithKey($unsigedHash, array($privateKey))->get();
            $txid = $this->bitcoind->sendRawTransaction($hash['hex'])->get();

            $request = new Request;
            $request->txid = $txid;
            $request->transactions = $transactions;
            $request->batchId = $batch->id;
            
            $this->store($request);
        }
    }

    public function processIntermediate($intermediateId) {
        $wallet = IntermediateAddress::find($intermediateId);

        $balance = $this->isReceived($wallet->address, $wallet->wallet);
        
        // check bitcoins received        
        if(!$balance)
            return Response::json(['data' => ['error' => 'Bitcoins not received'], 'status' => false], 200);

        // check available balance
        if(!count($balance))
            throw new \Exception('Unspent Not Found');

        $rawTransactionPayload = array(
            array(
                'txid' => $balance['txid'],
                'vout' => $balance['vout']
            ),
        );
        
        $privateKey = $this->bitcoind->dumpPrivKey($wallet->address)->get();

        $address = $wallet->batch->final_address;

        $amount = number_format($balance['amount'] - 0.00000110, 8);

        $transactions[] = array($address => $wallet->wallet, 'type' => 1);
        
        // make transaction
        $rawTransactionOutput = array($address => $amount);

        $unsigedHash = $this->bitcoind->createRawTransaction($rawTransactionPayload, $rawTransactionOutput)->get();
        $rawTransactionPayload[0]['scriptPubKey'] = $balance['scriptPubKey'];
        $hash = $this->bitcoind->signRawTransactionWithKey($unsigedHash, array($privateKey))->get();
        $txid = $this->bitcoind->sendRawTransaction($hash['hex'])->get();

        $request = new Request;
        $request->txid = $txid;
        $request->transactions = $transactions;
        $request->batchId = $wallet->batch->id;
        $request->wallet = $wallet->wallet;
        
        $this->store($request);
    }

    public function isReceived($address, $wallet = 'test_wallet') {

        $transactions = $this->bitcoind->wallet($wallet)->listUnspent()->get();

        if(isset($transactions['address'])) {
            $received = $transactions['address'] == $address ? $transactions: false;
        }   
        else {
            $received = \Arr::where($transactions, function ($value, $key) use ($address) {
                return $value['address'] == $address;
            });

            $received = head($received);
        }

        if($received)
            return $received;

        return false;
    }

    public function store(Request $request) {

        $txid = $request->txid;
        $transactions = $request->transactions;

        $batchId = $request->batchId;

        $transaction = $this->bitcoind->wallet($request->wallet ?? 'test_wallet')->getTransaction($txid)->get()['details'];

        foreach($transaction as $key => $value) {
            
            $walletId = Wallet::whereName($transactions[$key][$value['address']])->value('id');

            Transaction::create([
                'wallet_id' => $walletId,
                'batch_id' => $batchId,
                'type' => $transactions[$key]['type'],
                'amount' => $value['amount'],
                'address' => $value['address'],
                'txid' => $txid,
                'btc_details' => json_encode($value),
            ]);
        }

    }

    public function search($sessionId) {
        $status = Batch::whereSessionId($sessionId)->value('status');

        $data = ['started' => 'Bitcoins received, awaiting confirmation', 'confirmed' => 'Transaction conformed, sending to intermediate addesses', 'sent-intermediate' => 'Bitcoins sent to intermediate addresses', 'failed' => 'Transaction failed', 'complete' => 'Transaction Complete'];

        if($status)
            return Response::json(['data' => ['message' => $data[$status]], 'status' => true], 200);
        else
            return Response::json(['data' => ['message' => 'Bitcoins not received'], 'status' => false], 200);

    }
}
