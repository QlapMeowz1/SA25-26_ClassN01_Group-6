<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceBetRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $match = $this->route('match');
        $max = auth()->user()->virtual_coins ?? 0;
        $playerIds = array_filter([
            $match->player1_id ?? null,
            $match->player2_id ?? null,
        ]);

        return [
            'bet_on_user_id' => ['required', Rule::in($playerIds)],
            'amount' => ['required', 'integer', 'min:10', 'max:' . $max],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $match = $this->route('match');

            if (!$match->player1_id || !$match->player2_id) {
                $validator->errors()->add('bet_on_user_id', 'Betting opens after the match has two confirmed players.');
            }

            if ($match->status === 'completed') {
                $validator->errors()->add('bet_on_user_id', 'Betting is closed for completed matches.');
            }

            if (!$match->canAcceptBets()) {
                $validator->errors()->add('bet_on_user_id', 'This betting market is not open.');
            }
        });
    }
}
