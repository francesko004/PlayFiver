<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdraw;
use App\Models\SuitPayPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Gateways\SuitpayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuitPayController extends Controller
{
    use SuitpayTrait;


    /*** @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callbackMethodPayment(Request $request)
    {
        $data = $request->all();
        \DB::table('debug')->insert(['text' => json_encode($request->all())]);

        return response()->json([], 200);
    }

    /*** @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function callbackMethod(Request $request)
    {
        $data = $request->all();
        Log::info($data);
        if (isset($data['idTransaction']) && $data['typeTransaction'] == 'PIX') {
            if ($data['statusTransaction'] == "PAID_OUT" || $data['statusTransaction'] == "PAYMENT_ACCEPT") {
                if (self::finalizePayment($data['idTransaction'], $request->input("id"))) {
                    return response()->json([], 200);
                }
            }
        }
    }

    /*** @param Request $request
     * @return null
     */
    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }

    /*** Show the form for creating a new resource.
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }
}
