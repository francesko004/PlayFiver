<?php

namespace App\Traits\Gateways;

use App\Helpers\Core;
use App\Models\AffiliateHistory;
use App\Models\AffiliateLogs;
use App\Models\AffiliateWithdraw;
use App\Models\Deposit;
use App\Models\DigitoPay;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewDepositNotification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

trait DigitoPayTrait
{
    protected static string $uriDigito;
    protected static string $clienteIdDigito;
    protected static string $clienteSecretDigito;

    private static function generateCredentialsDigito()
    {
        $setting = Gateway::first();
        if (!empty($setting)) {
            self::$uriDigito = $setting->getAttributes()['digitopay_uri'];

            self::$clienteIdDigito = $setting->getAttributes()['digitopay_cliente_id'];
            self::$clienteSecretDigito = $setting->getAttributes()['digitopay_cliente_secret'];
        }
    }
    private static function getToken()
    {
        try {
            $response = Http::post(self::$uriDigito . 'token/api', array_merge([
                "clientId" => self::$clienteIdDigito,
                "secret" => self::$clienteSecretDigito
            ]));
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['accessToken'])) {
                    return ['error' => '', 'acessToken' => $responseData['accessToken']];
                } else {
                    return ['error' => 'Internal Server Error', 'acessToken' => ""];
                }
            } else {
                return ['error' => 'Internal Server Error', 'acessToken' => ""];
            }
        } catch (Exception $e) {
            return ['error' => 'Internal Server Error', 'acessToken' => ""];
        }
    }
    public function requestQrcodeDigito($request)
    {
        try {
            $setting = Core::getSetting();
            $rules = [
                'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
                'cpf'    => ['required', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            self::generateCredentialsDigito();
            $token = self::getToken();
            if ($token['error'] != "") {
                return response()->json(['error' => 'Ocorreu uma falha ao entrar em contato com o banco.'], 500);
            }
            $idUnico = uniqid();

            if (self::setWebhook($token['acessToken'], $idUnico) == false) {
                return response()->json(['error' => 'Ocorreu uma falha ao entrar em contato com o banco.'], 500);
            }
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $token['acessToken']
            ])->post(self::$uriDigito . 'deposit', array_merge([
                "dueDate" => date('Y-m-d\TH:i:s\Z', strtotime('+1 day')),
                "paymentOptions" => ["PIX"],
                "person" => [
                    'cpf' => $request->input('cpf'),
                    'name' => auth('api')->user()->name,
                ],
                "value" => (float) $request->input("amount")

            ]));
            if ($response->successful()) {
                $responseData = $response->json();
                self::generateTransactionDigito($responseData['id'], $request->input("amount"), $idUnico);
                self::generateDepositDigito($responseData['id'], $request->input("amount"));
                return response()->json(['status' => true, 'idTransaction' => $responseData['id'], 'qrcode' => $responseData['pixCopiaECola']]);
            }
            return response()->json(['error' => "Ocorreu uma falha ao entrar em contato com o bancoe."], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    private static function setWebhook($token)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . $token
        ])->put(self::$uriDigito . 'digitopay/webhook/deposit', [
            "url" => url('/digitopay/callback?id_unico=' . self::$clienteIdDigito, [], true)
        ]);
        if ($response->successful()) {
            return true;
        } else {
            return false;
        }
    }
    public function webhookDigito($request)
    {
        self::generateCredentialsDigito();
        if (self::finalizaPaymentDigito($request->input("id")) == true && self::$clienteIdDigito == $request->input("id_unico")) {
            return response()->json([], 200);
        } else {
            return response()->json([], 500);
        }
    }
    private static function finalizaPaymentDigito($idTransaction)
    {


        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();
        $setting = Core::getSetting();
        if (!empty($transaction)) {
            $user = User::find($transaction->user_id);

            $wallet = Wallet::where('user_id', $transaction->user_id)->first();

            if (!empty($wallet)) {
                $setting = Setting::first();

                /// verifica se é o primeiro deposito, verifica as transações, somente se for transações concluidas
                $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                    ->where('status', 1)
                    ->count();

                if ($checkTransactions == 0 || empty($checkTransactions)) {
                    /// pagar o bonus
                    $bonus = Core::porcentagem_xn($setting->initial_bonus, $transaction->price);
                    $wallet->increment('balance_bonus', $bonus);
                    $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                }

                /// rollover deposito
                $wallet->update(['balance_deposit_rollover' => $transaction->price * intval($setting->rollover_deposit)]);

                /// acumular bonus
                Core::payBonusVip($wallet, $transaction->price);

                if ($wallet->increment('balance', $transaction->price)) {
                    if ($transaction->update(['status' => 1])) {
                        $deposit = Deposit::where('payment_id', $idTransaction)->where('status', 0)->first();
                        if (!empty($deposit)) {

                            /// fazer o deposito em cpa
                            $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                                ->where('commission_type', 'cpa')
                                //->where('deposited', 1)
                                ->where('status', 0)
                                ->first();

                            if (!empty($affHistoryCPA)) {

                                /// verifcia se já pode receber o cpa
                                $sponsorCpa = User::find($user->inviter);
                                if (!empty($sponsorCpa)) {
                                    if ($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                        $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();
                                        if (!empty($walletCpa)) {

                                            /// paga o valor de CPA
                                            $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa); /// coloca a comissão
                                            $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]); /// desativa cpa

                                        }
                                    } else {
                                        $affHistoryCPA->update(['deposited_amount' => $transaction->price]);
                                    }
                                }
                            }

                            if ($deposit->update(['status' => 1])) {
                                $admins = User::where('role_id', 0)->get();
                                foreach ($admins as $admin) {
                                    $admin->notify(new NewDepositNotification($user->name, $transaction->price));
                                }

                                return true;
                            }
                            return true;
                        }
                        return true;
                    }
                }

                return true;
            }
            return false;
        }
        return false;
    }
    public function pixCashOutDigito($id, $tipo)
    {
        $withdrawal = Withdrawal::find($id);
        self::generateCredentialsDigito();
        if ($tipo == "afiliado") {
            $withdrawal = AffiliateWithdraw::find($id);
        }
        $token = self::getToken();
        if ($token['error'] != "") {
            return false;
        }
        if ($withdrawal != null) {
            $tipo = null;
            $key = null;
            switch ($withdrawal->pix_type) {
                case 'document':
                    if (strlen($withdrawal->pix_key) == 11) {
                        $tipo = "CPF";
                    } else {
                        $tipo = "CNPJ";
                    }
                    $key = $withdrawal->pix_key;
                    break;
                case 'phoneNumber':
                    $key = "+55" .  $withdrawal->pix_key;
                    $tipo = "TELEFONE";
                    break;
                case 'email':
                    $key = $withdrawal->pix_key;
                    $tipo = "EMAIL";
                    break;
                case 'randomKey':
                    $key = $withdrawal->pix_key;
                    $tipo = "CHAVE_ALEATORIA";
                    break;
            }
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $token['acessToken']
            ])->post(self::$uriDigito . 'withdraw', [
                "value" => $withdrawal->amount,
                "endToEndId" => null,
                "paymentOptions" => [
                    "PIX"
                ],
                "person" => [
                    'name' => $withdrawal->name,
                    'pixKeyTypes' => $tipo,
                    "pixKey" => $key
                ],
            ]);
            Log::info($response->body());

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['success'])) {
                    $withdrawal->update(['status' => 1]);
                    DigitoPay::create([
                        "user_id" => $withdrawal->user_id,
                        "withdrawal_id" => $withdrawal->id,
                        "amount" => $withdrawal->amount,
                        "status" => 1
                    ]);
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }
    private static function generateDepositDigito($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id'   => $userId,
            'amount'    => $amount,
            'type'      => 'pix',
            'currency'  => $wallet->currency,
            'symbol'    => $wallet->symbol,
            'status'    => 0
        ]);
    }
    private static function generateTransactionDigito($idTransaction, $amount, $id)
    {
        $setting = Core::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0,
            "idUnico" => $id
        ]);
    }
}
