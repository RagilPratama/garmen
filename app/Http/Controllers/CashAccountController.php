<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CashAccountController extends Controller
{
    public function index()
    {
        return response()->json(CashAccount::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:gudang,garmen',
            'balance' => 'required|numeric',
        ]);
        $account = CashAccount::create($validated);
        return response()->json($account, 201);
    }

    public function update(Request $request, CashAccount $cashAccount)
    {
        $validated = $request->validate([
            'balance' => 'required|numeric',
        ]);
        $cashAccount->update($validated);
        return response()->json($cashAccount);
    }

    public function destroy(CashAccount $cashAccount)
    {
        $cashAccount->delete();
        return response()->json(null, 204);
    }
}
?>