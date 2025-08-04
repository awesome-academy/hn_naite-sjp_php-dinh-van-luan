<?php

namespace App\Http\Controllers\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Services\Wallet\WalletServiceFactory;
use App\Http\Resources\Wallet\WalletResource;
use App\Services\Wallet\WalletValidator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;
use App\Models\Wallet;

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
            return ApiResponse::error(__('wallet.created_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function getWalletsByUser(Request $request)
    {
        try {
            $userId = $request->query('user_id') ?? Auth::id();

            $wallets = Wallet::with(['savingWallet', 'creditWallet'])
            ->where('user_id', $userId)
            ->get();

            return ApiResponse::success([
                'wallets' => $wallets->map(fn ($wallet) => WalletResource::makeFrom($wallet)),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error(__('wallet.fetch_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function getWalletDetail($id)
    {
        try {
            $wallet =  Wallet::with(['savingWallet', 'creditWallet'])->findOrFail($id);

            return ApiResponse::success([
                'wallet' => WalletResource::makeFrom($wallet),
            ]);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('wallet.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Exception $e) {
            return ApiResponse::error(__('wallet.fetch_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
