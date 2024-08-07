<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Traits\Gateways\DigitoPayTrait;
use App\Traits\Gateways\EzzepayTrait;
use App\Traits\Gateways\SuitpayTrait;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    use SuitpayTrait, DigitoPayTrait, EzzepayTrait;

    /*** @param Request $request
     * @return array|false[]
     */
    public function submitPayment(Request $request)
    {
        switch ($request->gateway) {
            case 'suitpay':
                return self::requestQrcode($request);
            case 'digitopay':
                return self::requestQrcodeDigito($request);
            case 'ezzepay':
                return self::requestQrcodeEzze($request);
        }
    }

    /*** Show the form for creating a new resource.
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    /*** Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::whereUserId(auth('api')->id())->paginate();
        return response()->json(['deposits' => $deposits], 200);
    }
}
