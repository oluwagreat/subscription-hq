<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_email' => ['required','email'],
            'customer_phone' => ['nullable','numeric'],
            'amount' => ['required','integer'],
            'reference' => ['required','string','unique:transactions'],
            'callback_url' => ['nullable','url'],
            'plan_code' => ['string','nullable','exists:plans,plan_code'],
            'frequency' => ['string','nullable','in:daily,weekly,monthly,yearly'],
            'description' => ['nullable','string','max:500'],
        ];
    }
}
