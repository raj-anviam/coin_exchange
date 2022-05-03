<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Denpa\Bitcoin\Client as BitcoinClient;
use App\Models\Wallet;
use Session, Config;

class BaseController extends Controller
{
    protected $bitcoind;

    function __construct() {
        try {
            $this->bitcoind = new BitcoinClient("http://" . env('BTCUSER') . ":" . env('BTCPASSWORD') . "@localhost:18332/");

            // load available wallets
            $wallets = Wallet::all();
            $loadedWallets = $this->bitcoind->listWallets()->get();

            $loadedWallets = is_array($loadedWallets)? $loadedWallets: array($loadedWallets);

            foreach($wallets as $wallet) {
                if(!in_array($wallet->name, $loadedWallets))
                    $this->bitcoind->loadWallet($wallet->name);
            }
        }
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            Session::flash($e->getMessage());
            return redirect('/');
        }
    }
}
