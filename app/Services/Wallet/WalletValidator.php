<?php

namespace App\Services\Wallet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WalletValidator
{
    public static function validate(Request $request): array
    {
        $commonRules = [
            'name'        => 'required|string|max:255',
            'balance'     => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'wallet_type' => 'required|in:basic,saving,credit',
        ];

        $savingRules = [
            'initial_amount' => 'required|numeric|min:0',
            'target_amount' => 'required|numeric|min:0',
            'end_date'      => 'required|date|after:today',
        ];

        $creditRules = [
            'credit_limit'      => 'required|numeric|min:0',
            'statement_date'    => 'required|date',
            'payment_due_date'  => 'required|date|after_or_equal:statement_date',
        ];

        $rules = [];

        switch ($request->input('wallet_type')) {
            case 'basic':
                $rules = $commonRules;
                break;
            case 'saving':
                $rules = $commonRules + $savingRules;
                break;
            case 'credit':
                $rules = $commonRules + $creditRules;
                break;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
