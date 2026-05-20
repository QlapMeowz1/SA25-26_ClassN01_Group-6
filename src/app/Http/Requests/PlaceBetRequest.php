<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

        return [
            'bet_on_user_id' => ['required', 'in:' . ($match->player1_id ?? '') . ',' . ($match->player2_id ?? '')],
            'amount' => ['required', 'integer', 'min:10', 'max:' . $max],
        ];
    }
}
