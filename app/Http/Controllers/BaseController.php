<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Denpa\Bitcoin\Client as BitcoinClient;
use App\Models\Wallet;
use Session;

class BaseController extends Controller
{
    protected $bitcoind;

    function __construct() {
        try {
            $this->bitcoind = new BitcoinClient('http://someuser:somepassword@localhost:18332/');

            // load available wallets
            $wallets = Wallet::all();
            $loadedWallets = $this->bitcoind->listWallets()->get();

            foreach($wallets as $wallet) {
                if(!in_array($wallet->name, $loadedWallets))
                    $this->bitcoind->loadWallet($wallet->name);
            }
        }
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            // dd($e->getMessage());
            Session::flash($e->getMessage());
            return redirect('/');
        }
    }
}