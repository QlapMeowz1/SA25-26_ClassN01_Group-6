<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BetService;
use Illuminate\Http\Request;

class BetController extends Controller
{
    protected $service;

    public function __construct(BetService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $history = $this->service->getUserBetHistory(auth()->user());
        return view('bets.index', ['history' => $history]);
    }

    public function show($id)
    {
        $bet = \App\Models\Bet::with('gameMatch', 'betOnUser')->findOrFail($id);
        return view('bets.show', ['bet' => $bet]);
    }
}
