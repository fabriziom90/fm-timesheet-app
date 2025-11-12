<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
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
        
        return Inertia::render('Times/Index', ['entries' => $entries]);
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
        $year = $request->year;
        $month = $request->month;
        

        $entries = TimeEntry::where('user_id', Auth::id())
            ->whereRaw('YEAR(`date`) = ?', [$year])
            ->whereRaw('MONTH(`date`) = ?', [$month])
            ->orderBy('date')
            ->get();
        
        $totalHours = $entries->sum('duration_hours');

        $pdf = Pdf::loadView('reports.monthly', [
            'entries'    => $entries,
            'month'      => str_pad($month, 2, '0', STR_PAD_LEFT), // → 01 invece di 1
            'year'       => $year,
            'totalHours' => $totalHours,
            'user'       => Auth::user(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("report_{$year}_{$month}.pdf");
    }
}
