<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorMedicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $doctor = auth()->user();
        $query  = DoctorMedicine::where('doctor_user_id', $doctor->id);

        if ($q = $request->get('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('medicine_name', 'like', "%{$q}%")
                   ->orWhere('generic_name',  'like', "%{$q}%");
            });
        }
        if ($form = $request->get('form')) {
            $query->where('form', $form);
        }

        $medicines = $query->orderBy('medicine_name')->paginate(30)->withQueryString();
        $total     = DoctorMedicine::where('doctor_user_id', $doctor->id)->count();
        $forms     = DoctorMedicine::where('doctor_user_id', $doctor->id)
                        ->whereNotNull('form')->distinct()->pluck('form')->sort()->values();

        return view('doctor.medicines.index', compact('medicines', 'total', 'forms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_name'        => ['required', 'string', 'max:150'],
            'generic_name'         => ['nullable', 'string', 'max:150'],
            'form'                 => ['nullable', 'string', 'max:30'],
            'dosage'               => ['nullable', 'string', 'max:50'],
            'frequency'            => ['nullable', 'string', 'max:100'],
            'timing'               => ['nullable', 'in:before_food,after_food,with_food,empty_stomach,bed_time,any_time'],
            'duration_days'        => ['nullable', 'integer', 'min:1', 'max:365'],
            'special_instructions' => ['nullable', 'string', 'max:300'],
        ]);

        $medicine = DoctorMedicine::firstOrCreate(
            ['doctor_user_id' => auth()->id(), 'medicine_name' => $data['medicine_name']],
            $data,
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'medicine' => $medicine]);
        }

        return back()->with('success', '"' . $medicine->medicine_name . '" added to your medicine list.');
    }

    public function update(Request $request, DoctorMedicine $medicine)
    {
        $this->gate($medicine);

        $data = $request->validate([
            'medicine_name'        => ['required', 'string', 'max:150'],
            'generic_name'         => ['nullable', 'string', 'max:150'],
            'form'                 => ['nullable', 'string', 'max:30'],
            'dosage'               => ['nullable', 'string', 'max:50'],
            'frequency'            => ['nullable', 'string', 'max:100'],
            'timing'               => ['nullable', 'in:before_food,after_food,with_food,empty_stomach,bed_time,any_time'],
            'duration_days'        => ['nullable', 'integer', 'min:1', 'max:365'],
            'special_instructions' => ['nullable', 'string', 'max:300'],
        ]);

        $medicine->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'medicine' => $medicine->fresh()]);
        }

        return back()->with('success', 'Medicine updated.');
    }

    public function destroy(Request $request, DoctorMedicine $medicine)
    {
        $this->gate($medicine);
        $name = $medicine->medicine_name;
        $medicine->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '"' . $name . '" removed from your list.');
    }

    // AJAX endpoint — used by prescription create autocomplete
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $medicines = DoctorMedicine::where('doctor_user_id', auth()->id())
            ->active()
            ->when(strlen($q) >= 1, fn($sq) =>
                $sq->where(fn($w) =>
                    $w->where('medicine_name', 'like', "%{$q}%")
                      ->orWhere('generic_name',  'like', "%{$q}%")
                )
            )
            ->orderByRaw("medicine_name LIKE ? DESC", ["{$q}%"])
            ->orderBy('medicine_name')
            ->limit(15)
            ->get(['id', 'medicine_name', 'generic_name', 'form', 'dosage', 'frequency', 'timing', 'duration_days', 'special_instructions']);

        return response()->json($medicines);
    }

    private function gate(DoctorMedicine $m): void
    {
        if ($m->doctor_user_id !== auth()->id()) abort(403);
    }
}
