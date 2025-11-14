<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('receipts', 'public');

        Receipt::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'amount' => $request->amount,
            'image_path' => $path,
        ]);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        $path = storage_path('app/' . $receipt->path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
