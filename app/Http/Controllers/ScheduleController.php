<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Poli;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Schedule::class);

        $schedules = Schedule::with(['doctor', 'poli'])->latest()->paginate(15);

        return view('schedules.index', compact('schedules'));
    }

    public function board()
    {
        $this->authorize('viewAny', Schedule::class);

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        $schedules = Schedule::with(['doctor', 'poli'])
            ->where('is_active', true)
            ->orderBy('doctor_id')
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($rows) {
                $byDay = $rows->groupBy('day_of_week');

                return [
                    'doctor' => $rows->first()->doctor,
                    'days' => $byDay->map(function ($dayRows) {
                        return $dayRows->map(fn ($s) => [
                            'id' => $s->id,
                            'poli' => $s->poli?->name,
                            'start' => $s->start_time?->format('H:i'),
                            'end' => $s->end_time?->format('H:i'),
                            'quota' => $s->daily_quota,
                        ])->values();
                    }),
                ];
            })
            ->values();

        return view('schedules.board', compact('schedules', 'days'));
    }

    public function create()
    {
        $this->authorize('create', Schedule::class);

        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        $polis = Poli::where('is_active', true)->orderBy('name')->get();

        return view('schedules.create', compact('doctors', 'polis'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Schedule::class);

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'poli_id' => 'required|exists:polis,id',
            'day_of_week' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'daily_quota' => 'required|integer|min:1|max:200',
            'consultation_fee' => 'required|numeric|min:0',
        ]);

        $existing = Schedule::where('doctor_id', $validated['doctor_id'])
            ->where('poli_id', $validated['poli_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('start_time', $validated['start_time'])
            ->exists();

        if ($existing) {
            return back()->withErrors(['start_time' => 'This schedule already exists for this doctor, poli, and day.'])->withInput();
        }

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function show(Schedule $schedule)
    {
        $this->authorize('view', $schedule);

        $schedule->load(['doctor', 'poli']);

        return view('schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        $polis = Poli::where('is_active', true)->orderBy('name')->get();

        return view('schedules.edit', compact('schedule', 'doctors', 'polis'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'poli_id' => 'required|exists:polis,id',
            'day_of_week' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'daily_quota' => 'required|integer|min:1|max:200',
            'consultation_fee' => 'required|numeric|min:0',
        ]);

        $existing = Schedule::where('doctor_id', $validated['doctor_id'])
            ->where('poli_id', $validated['poli_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('start_time', $validated['start_time'])
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($existing) {
            return back()->withErrors(['start_time' => 'This schedule already exists for this doctor, poli, and day.'])->withInput();
        }

        $schedule->update($validated);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule);

        if ($schedule->appointments()->exists()) {
            return redirect()->route('schedules.index')
                ->with('error', 'Cannot delete schedule with existing appointments.');
        }

        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
