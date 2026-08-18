<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VitalSignController extends Controller
{
    public function create(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $vitalSign = $appointment->vitalSign;

        return view('vital-signs.create', compact('appointment', 'vitalSign'));
    }

    public function store(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'temperature' => 'required|numeric',
            'blood_pressure_systolic' => 'required|integer',
            'blood_pressure_diastolic' => 'required|integer',
            'heart_rate' => 'required|integer',
            'respiratory_rate' => 'required|integer',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'oxygen_saturation' => 'required|integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['recorded_by'] = \Illuminate\Support\Facades\Auth::id();

        $appointment->vitalSign()->updateOrCreate([], $validated);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Tanda vital berhasil disimpan.');
    }
}
