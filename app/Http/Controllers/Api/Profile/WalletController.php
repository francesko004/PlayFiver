<?php

namespace App\Http\Controllers\Api\Profile;

use App\Helpers\Core;
use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdraw;
use App\Models\Setting;
use App\Models\SuitPayPayment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewWithdrawalNotification;
use App\Traits\Gateways\DigitoPayTrait;
use App\Traits\Gateways\EzzepayTrait;
use App\Traits\Gateways\SuitpayTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DigitoPayPayment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use SuitpayTrait, DigitoPayTrait, EzzepayTrait;
    public function index()
    {
        $wallet = Wallet::whereUserId(auth('api')->id())->where('active', 1)->first();
        return response()->json(['wallet' => $wallet], 200);
    }

    /*** @return \Illuminate\Http\JsonResponse
     */
    public function myWallet()
    {
        $wallets = Wallet::whereUserId(auth('api')->id())->get();
        return response()->json(['wallets' => $wallets], 200);
    }

    /*** @param $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function setWalletActive($id)
    {
        $checkWallet = Wallet::whereUserId(auth('api')->id())->where('active', 1)->first();
        if (!empty($checkWallet)) {
            $checkWallet->update(['active' => 0]);
        }

        $wallet = Wallet::find($id);
        if (!empty($wallet)) {
            $wallet->update(['active' => 1]);
            return response()->json(['wallet' => $wallet], 200);
        }
    }
    public function cancelWithdrawal($id, Request $request)
    {
        $tipo = $request->input("tipo");
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            back();
        }
        if ($tipo == 'user') {
            return $this->cancelWithdrawalUser($id);
        }

        if ($tipo == 'afiliado') {
            return $this->cancelWithdrawalAffiliate($id);
        }
    }
    public function withdrawalFromModal($id, Request $request)
    {
        $setting = Core::getSetting();
        $resultado = null;
        $tipo = $request->input("tipo");
        $user = Auth::user();
        $message = 'Saque solicitado com sucesso';
        if (!$user->hasRole('admin')) {
            back();
        }
        switch ($setting->default_gateway) {
            case 'suitpay':
                $withdrawal = Withdrawal::find($id);
                if ($tipo == "afiliado") {
                    $withdrawal = AffiliateWithdraw::find($id);
                }
                $withdrawal->update(['status' => 1]);

                $suitpayment = SuitPayPayment::create([
                    'withdrawal_id' => $withdrawal->id,
                    'user_id'       => $withdrawal->user_id,
                    'pix_key'       => $withdrawal->pix_key,
                    'pix_type'      => $withdrawal->pix_type,
                    'amount'        => $withdrawal->amount,
                    'observation'   => 'Saque direto',
                ]);
                $parm = [
                    'pix_key'           => $withdrawal->pix_key,
                    'pix_type'          => $withdrawal->pix_type,
                    'amount'            => $withdrawal->amount,
                    'suitpayment_id'    => $suitpayment->id
                ];
                $resultado = self::pixCashOut($parm);
                break;
            case 'digitopay':
                $message = "Para poder autorizar o saque, você precisa acessar o painel da digitopay para autorizar";
                $resultado = self::pixCashOutDigito($id, $tipo);
                break;
            case 'ezzepay':
                $resultado = self::pixCashOutEzze($id, $tipo);
                break;
        }

        if ($resultado == true) {
            Notification::make()
                ->title('Saque solicitado')
                ->body($message)
                ->success()
                ->send();

            return back();
        } else {
            Notification::make()
                ->title('Erro no saque')
                ->body('Erro ao solicitar o saque')
                ->danger()
                ->send();

            return back();
        }
    }
    private function cancelWithdrawalAffiliate($id)
    {
        $withdrawal = AffiliateWithdraw::find($id);
        if (!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if (!empty($wallet)) {
                $wallet->increment('refer_rewards', $withdrawal->amount);

                $withdrawal->update(['status' => 2]);
                Notification::make()
                    ->title('Saque cancelado')
                    ->body('Saque cancelado com sucesso')
                    ->success()
                    ->send();

                return back();
            }
            return back();
        }
        return back();
    }

    /*** @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function cancelWithdrawalUser($id)
    {
        $withdrawal = Withdrawal::find($id);
        if (!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if (!empty($wallet)) {
                $wallet->increment('balance_withdrawal', $withdrawal->amount);

                $withdrawal->update(['status' => 2]);
                Notification::make()
                    ->title('Saque cancelado')
                    ->body('Saque cancelado com sucesso')
                    ->success()
                    ->send();

                return back();
            }
            return back();
        }
        return back();
    }
    public function requestWithdrawal(Request $request)
    {
        $setting = Setting::first();

        /// Verificar se é afiliado
        if (auth('api')->check()) {

            if ($request->type === 'pix') {
                $rules = [
                    'amount'        => ['required', 'numeric', 'min:' . $setting->min_withdrawal, 'max:' . $setting->max_withdrawal],
                    'pix_type'      => 'required',
                    'accept_terms'  => 'required',
                ];

                switch ($request->pix_type) {
                    case 'document':
                        $rules['pix_key'] = 'required|cpf_ou_cnpj';
                        break;
                    case 'email':
                        $rules['pix_key'] = 'required|email';
                        break;
                    case 'phoneNumber':
                        $rules['pix_key'] = 'required';
                        break;
                    default:
                        $rules['pix_key'] = 'required';
                        break;
                }
            }

            if ($request->type === 'bank') {
                $rules = [
                    'amount'        => ['required', 'numeric', 'min:' . $setting->min_withdrawal, 'max:' . $setting->max_withdrawal],
                    'bank_info'     => 'required',
                    'accept_terms'  => 'required',
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            /// verificar o limite de saque
            if (!empty($setting->withdrawal_limit)) {
                switch ($setting->withdrawal_period) {
                    case 'daily':
                        $registrosDiarios = Withdrawal::whereDate('created_at', now()->toDateString())->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the daily withdrawal limit')], 400);
                        }
                        break;
                    case 'weekly':
                        $registrosDiarios = Withdrawal::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the weekly withdrawal limit')], 400);
                        }
                        break;
                    case 'monthly':
                        $registrosDiarios = Withdrawal::whereYear('created_at', now()->year)->whereMonth('data', now()->month)->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the monthly withdrawal limit')], 400);
                        }
                        break;
                    case 'yearly':
                        $registrosDiarios = Withdrawal::whereYear('created_at', now()->year)->count();
                        if ($registrosDiarios >= $setting->withdrawal_limit) {
                            return response()->json(['error' => trans('You have already reached the yearly withdrawal limit')], 400);
                        }
                        break;
                }
            }

            if ($request->amount > $setting->max_withdrawal) {
                return response()->json(['error' => 'Você excedeu o limite máximo permitido de: ' . $setting->max_withdrawal], 400);
            }
            $status = 0;
            if ($setting->withdrawal_autom == 1 && $request->amount <= $setting->limit_withdrawal) {
                $status = 1;
            }
            if ($request->accept_terms == true) {
                if (floatval($request->amount) > floatval(auth('api')->user()->wallet->balance_withdrawal)) {
                    return response()->json(['error' => 'Você não tem saldo suficiente'], 400);
                }

                $data = [];
                if ($request->type === 'pix') {
                    $data = [
                        'user_id'   => auth('api')->user()->id,
                        'amount'    => \Helper::amountPrepare($request->amount),
                        'type'      => $request->type,
                        'pix_key'   => $request->pix_key,
                        'pix_type'  => $request->pix_type,
                        'currency'  => $request->currency,
                        'symbol'    => $request->symbol,
                        'status'    => $status,
                        'cpf' => $request->cpf,
                        'name' => $request->name
                    ];
                }

                if ($request->type === 'bank') {
                    $data = [
                        'user_id'   => auth('api')->user()->id,
                        'amount'    => \Helper::amountPrepare($request->amount),
                        'type'      => $request->type,
                        'bank_info' => $request->bank_info,
                        'currency'  => $request->currency,
                        'symbol'    => $request->symbol,
                        'status'    => $status,
                        'cpf' => $request->cpf,
                        'name' => $request->name
                    ];
                }

                $withdrawal = Withdrawal::create($data);

                if ($withdrawal) {
                    $wallet = Wallet::where('user_id', auth('api')->id())->first();

                    $resultado = null;
                    if ($setting->withdrawal_autom == 0 || $request->amount > $setting->limit_withdrawal) {
                        $admins = User::where('role_id', 0)->get();
                        foreach ($admins as $admin) {
                            $admin->notify(new NewWithdrawalNotification(auth()->user()->name, $request->amount));
                        }
                        $resultado = true;
                    } else {

                        switch ($setting->default_gateway) {
                            case 'suitpay':

                                $withdrawal->update(['status' => 1]);

                                $suitpayment = SuitPayPayment::create([
                                    'withdrawal_id' => $withdrawal->id,
                                    'user_id'       => $withdrawal->user_id,
                                    'pix_key'       => $withdrawal->pix_key,
                                    'pix_type'      => $withdrawal->pix_type,
                                    'amount'        => $withdrawal->amount,
                                    'observation'   => 'Saque direto',
                                ]);
                                $parm = [
                                    'pix_key'           => $withdrawal->pix_key,
                                    'pix_type'          => $withdrawal->pix_type,
                                    'amount'            => $withdrawal->amount,
                                    'suitpayment_id'    => $suitpayment->id
                                ];
                                $resultado = self::pixCashOut($parm);
                                break;
                            case 'digitopay':
                                $resultado = self::pixCashOutDigito($withdrawal->id, "user");
                                break;
                            case 'ezzepay':
                                $resultado = self::pixCashOutEzze($withdrawal->id, "user");
                                break;
                        }
                    }
                    if ($resultado) {
                        $wallet->decrement('balance_withdrawal', floatval($request->amount));
                        return response()->json([
                            'status' => true,
                            'message' => 'Saque realizado com sucesso',
                        ], 200);
                    }
                    $withdrawal->update(["status" => 2]);
                    return response()->json(['error' => 'Erro ao realizar o saque'], 400);
                }
            }

            return response()->json(['error' => 'Você precisa aceitar os termos'], 400);
        }

        return response()->json(['error' => 'Erro ao realizar o saque'], 400);
    }
}
