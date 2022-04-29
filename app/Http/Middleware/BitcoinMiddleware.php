<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Denpa\Bitcoin\Client as BitcoinClient;
use Session;

class BitcoinMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $bitcoind = new BitcoinClient('http://someuser:somepassword@localhost:18332/');
            $bitcoind->listWallets()->get();
            return $next($request);
        }
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            Session::flash('error', $e->getMessage());
            return redirect()->back();
        }
        return $next($request);
    }
}
