<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\UserServicePackage;
use App\Models\ServicePackage;
use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function payWithMomo(Request $request)
    {
        $user = $request->user();

        $validQueryKeys = ['service_package_id'];
        $queryParams = $request->only($validQueryKeys);
        $diff = array_diff(array_keys($request->all()), array_keys($queryParams));

        if (!empty($diff)) {
            return ApiResponse::error(__('transaction.invalid_query'), [
                'invalid_keys' => $diff
            ], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($queryParams, [
            'service_package_id' => 'required|integer|exists:service_packages,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                __('transaction.invalid_query'),
                $validator->errors(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        }

        $packageId = $request->input('service_package_id');
        $package = ServicePackage::findOrFail($packageId);

        $partnerCode = config('services.momo.partner_code');
        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');
        $endpoint = config('services.momo.endpoint');

        $requestId = (string) Str::uuid();
        $orderId = (string) Str::uuid();
        $amount = (int) $package->price;
        $orderInfo = $package->title . ' - ' . $package->id;
        $redirectUrl = route('payment.momo.redirect');
        $ipnUrl = route('payment.momo.ipn');
        $extraData = base64_encode(json_encode(['user_id' => $user->id]));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData"
            . "&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo"
            . "&partnerCode=$partnerCode&redirectUrl=$redirectUrl"
            . "&requestId=$requestId&requestType=captureWallet";

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature,
            'lang' => 'vi'
        ];

        $response = Http::timeout(30)->post("$endpoint/v2/gateway/api/create", $payload)->json();

        if (!isset($response['payUrl'])) {
            return ApiResponse::error(
                'MoMo response invalid',
                $response,
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponse::success([
                [
                    'payUrl' => $response['payUrl'],
                    'qrCodeUrl' => $response['qrCodeUrl'] ?? null,
                ]
            ], __('payment.created_successfully'), HttpStatusCode::CREATED);
    }

    public function momoIpn(Request $request)
    {
        $data = $request->all();
        Log::info('MoMo IPN', $data);

        $secretKey = config('services.momo.secret_key');

        $rawHash = join('&', [
            "accessKey={$data['accessKey']}",
            "amount={$data['amount']}",
            "extraData={$data['extraData']}",
            "message={$data['message']}",
            "orderId={$data['orderId']}",
            "orderInfo={$data['orderInfo']}",
            "orderType={$data['orderType']}",
            "partnerCode={$data['partnerCode']}",
            "payType={$data['payType']}",
            "requestId={$data['requestId']}",
            "responseTime={$data['responseTime']}",
            "resultCode={$data['resultCode']}",
            "transId={$data['transId']}",
        ]);

        $calcSign = hash_hmac('sha256', $rawHash, $secretKey);

        if ($calcSign !== $data['signature'] || (int) $data['resultCode'] !== 0) {
            return ApiResponse::error(
                __('payment.invalid_signature_or_failed'),
                ['moMo_data' => $data],
                HttpStatusCode::BAD_REQUEST
            );
        }

        $extra = json_decode(base64_decode($data['extraData']), true);
        $userId = $extra['user_id'] ?? null;
        if (!$userId) {
            return ApiResponse::error(
                __('payment.missing_user_id'),
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        try {
            $orderParts = explode(' - ', $data['orderInfo']);
            $packageId = (int) trim(end($orderParts));
            if (!$packageId) {
                throw new \Exception('Invalid package ID');
            }

            $package = ServicePackage::findOrFail($packageId);
        } catch (\Exception $e) {
            Log::warning('Could not process orderInfo or find package:', [
                'orderInfo' => $data['orderInfo'],
                'exception' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                __('payment.invalid_package'),
                [],
                HttpStatusCode::UNPROCESSABLE_ENTITY
            );
        }

        UserServicePackage::create([
            'user_id' => $userId,
            'service_package_id' => $package->id,
            'register_date' => Carbon::now(),
            'expire_date' => Carbon::now()->addMonth(),
            'payment_method' => UserServicePackage::METHOD_MOMO,
            'amount' => $data['amount'],
            'status' => UserServicePackage::STATUS_PAID,
            'order_id' => $data['order_id'],
            'trans_id' => $data['trans_id'],
        ]);

        return ApiResponse::success(
            [],
            __('payment.created_successfully'),
            HttpStatusCode::OK
        );
    }

    public function momoRedirect(Request $request)
    {
        return redirect('/')->with('success', __('payment.payment_success'));
    }
}
