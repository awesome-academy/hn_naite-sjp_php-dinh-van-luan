<?php

namespace App\Http\Controllers\User;

use Illuminate\Validation\ValidationException;
use App\Services\Wallet\WalletServiceFactory;
use App\Services\Wallet\WalletValidator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;

class WalletController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = WalletValidator::validate($request);

            $service = WalletServiceFactory::make($validated['wallet_type']);

            DB::beginTransaction();

            $wallet = $service->create(new Request($validated));

            DB::commit();

            return ApiResponse::success([
                'wallet' => $wallet,
            ], __('wallet.created_successfully'), HttpStatusCode::CREATED);
        } catch (ValidationException $e) {
            return ApiResponse::error(__('wallet.invalid_data'), $e->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(__('wallet.created_failed') . $e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
