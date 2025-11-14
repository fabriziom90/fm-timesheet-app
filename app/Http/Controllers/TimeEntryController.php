<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class TimeEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entries = TimeEntry::where('user_id', Auth::id())->orderBy('date','ASC')
            ->get();
            $receipts = Receipt::where('user_id', Auth::id())->orderBy('date', 'ASC')->get();
        
        return Inertia::render('Times/Index', ['entries' => $entries, 'receipts' => $receipts]);
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
        $data = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        $start = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
        $end   = \Carbon\Carbon::createFromFormat('H:i', $data['end_time']);

        if ($end->lessThan($start)) {
            $end->addDay(); // caso “turno che supera la mezzanotte”
        }

        $minutes = $end->diffInMinutes($start);
        $data['duration_hours'] = round($minutes / 60, 2);
        $data['user_id'] = Auth::id();
        
        TimeEntry::create($data);
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeEntry $timeEntry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeEntry $timeEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        $data = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string'
        ]);

        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end = Carbon::createFromFormat('H:i', $data['end_time']);
        
        if ($end->lessThan($start)) $end->addDay();
        $data['duration_minutes'] = $end->diffInMinutes($start);
        $timeEntry->update($data);
        
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);
        $timeEntry->delete();
        return back();
    }

    public function exportMonthlyPdf(Request $request)
    {   
        $year  = (int) trim($request->input('year'));
        $month = (int) ltrim(trim($request->input('month')), '0');

        if (!$year) $year = now()->year;
        if (!$month) $month = now()->month;

        // Ore lavorate
        $entries = TimeEntry::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $entriesFormatted = $entries->map(function($e) {
            $e->duration_formatted = $this->formatHours($e->duration_hours);
            return $e;
        });

        $totalHours = $entries->sum('duration_hours');
        $totalHoursFormatted = $this->formatHours($totalHours);
        // dd($totalHoursFormatted);
        $hourlyRate = 28;

        $totalHoursAmount = $totalHours * $hourlyRate;

        // Ricevute
        $receipts = Receipt::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $totalReceiptsAmount = $receipts->sum('amount');

        $grandTotal = $totalHoursAmount + $totalReceiptsAmount;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.monthly', [
            'entries' => $entriesFormatted,
            'receipts' => $receipts,
            'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
            'year' => $year,
            'totalHours' => $totalHoursFormatted,
            'totalHoursAmount' => number_format($totalHoursAmount, 2),
            'totalReceiptsAmount' => number_format($totalReceiptsAmount, 2),
            'grandTotal' => number_format($grandTotal, 2),
            'hourlyRate' => $hourlyRate,
            'user' => Auth::user(),
        ]);

        return $pdf->stream("report_{$year}_".str_pad($month,2,'0',STR_PAD_LEFT).".pdf");
    }

    private function formatHours($decimalHours)
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return sprintf("%d.%02d", $hours, $minutes);
    }
}
