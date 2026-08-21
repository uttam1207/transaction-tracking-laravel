<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BreedingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BreedingController extends Controller
{
    private function farmerAnimalIds(): \Illuminate\Support\Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return Animal::where('created_by', $user->getAuthIdentifier())->pluck('id');
    }

    private function authorizeFarmerBreeding(BreedingRecord $record): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($record->animal_id)) {
            abort(403, 'You do not have access to this breeding record.');
        }
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isFarmer = $user->isFarmer();

        $query = BreedingRecord::with('animal');

        if ($isFarmer) {
            $farmerAnimalIds = $this->farmerAnimalIds();
            $query->whereIn('animal_id', $farmerAnimalIds);
        }

        if ($request->search) {
            $query->whereHas('animal', fn($q) => $q
                ->where('tag_number', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%'));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($isFarmer) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();

        if ($isFarmer) {
            $farmerAnimalIds = $this->farmerAnimalIds();
            $summary = [
                'inseminated'           => BreedingRecord::whereIn('animal_id', $farmerAnimalIds)->where('status', 'AI Done')->count(),
                'pregnant'              => BreedingRecord::whereIn('animal_id', $farmerAnimalIds)->where('status', 'Confirmed Pregnant')->count(),
                'expected_calving_month'=> BreedingRecord::whereIn('animal_id', $farmerAnimalIds)->whereMonth('expected_calving_date', now()->month)->count(),
                'repeat_breeders'       => BreedingRecord::whereIn('animal_id', $farmerAnimalIds)->where('status', 'Repeat Breeder')->count(),
            ];
        } else {
            $summary = [
                'inseminated'           => BreedingRecord::where('status', 'AI Done')->count(),
                'pregnant'              => BreedingRecord::where('status', 'Confirmed Pregnant')->count(),
                'expected_calving_month'=> BreedingRecord::whereMonth('expected_calving_date', now()->month)->count(),
                'repeat_breeders'       => BreedingRecord::where('status', 'Repeat Breeder')->count(),
            ];
        }

        return view('admin.breeding.index', compact('records', 'animals', 'summary', 'isFarmer'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($user->isFarmer()) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();
        return view('admin.breeding.create', compact('animals'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'animal_id'             => 'required|exists:animals,id',
            'heat_date'             => 'required|date',
            'ai_date'               => 'nullable|date',
            'bull_semen_code'       => 'nullable|string|max:100',
            'status'                => 'required|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder,Not Pregnant',
            'expected_calving_date' => 'nullable|date',
            'certificate_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Ensure farmer can only add records for their own animals
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($validated['animal_id'])) {
            return back()->with('error', 'You can only add breeding records for your own animals.');
        }

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_path'] = $request->file('certificate_file')->store('breeding-certificates', 'uploads');
        }
        unset($validated['certificate_file']);

        BreedingRecord::create($validated);

        if ($validated['status'] === 'Confirmed Pregnant') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Pregnant']);
        }

        return redirect()->route('admin.breeding.index')->with('success', 'Breeding record saved.');
    }

    public function show(BreedingRecord $breedingRecord)
    {
        $this->authorizeFarmerBreeding($breedingRecord);
        $breedingRecord->load('animal');
        return view('admin.breeding.show', compact('breedingRecord'));
    }

    public function edit(BreedingRecord $breedingRecord)
    {
        $this->authorizeFarmerBreeding($breedingRecord);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($user->isFarmer()) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();

        return view('admin.breeding.edit', compact('breedingRecord', 'animals'));
    }

    public function update(Request $request, BreedingRecord $breedingRecord)
    {
        $this->authorizeFarmerBreeding($breedingRecord);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'animal_id'             => 'required|exists:animals,id',
            'heat_date'             => 'required|date',
            'ai_date'               => 'nullable|date',
            'bull_semen_code'       => 'nullable|string|max:100',
            'status'                => 'required|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder',
            'pregnancy_check_date'  => 'nullable|date',
            'is_pregnant'           => 'nullable|boolean',
            'expected_calving_date' => 'nullable|date',
            'actual_calving_date'   => 'nullable|date',
            'calf_tag_number'       => 'nullable|string|max:50',
            'certificate_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Ensure farmer can only assign their own animals
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($validated['animal_id'])) {
            return back()->with('error', 'You can only use your own animals in breeding records.');
        }

        if ($request->hasFile('certificate_file')) {
            if ($breedingRecord->certificate_path) {
                Storage::disk('uploads')->delete($breedingRecord->certificate_path);
            }
            $validated['certificate_path'] = $request->file('certificate_file')->store('breeding-certificates', 'uploads');
        }
        unset($validated['certificate_file']);

        $breedingRecord->update($validated);

        if ($validated['status'] === 'Confirmed Pregnant') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Pregnant']);
        } elseif (in_array($validated['status'], ['Calved', 'Not Pregnant'])) {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Open']);
        }

        return redirect()->route('admin.breeding.index')
            ->with('success', 'Breeding record updated.');
    }

    public function destroy(BreedingRecord $breedingRecord)
    {
        $this->authorizeFarmerBreeding($breedingRecord);
        $breedingRecord->delete();
        return redirect()->route('admin.breeding.index')
            ->with('success', 'Breeding record deleted.');
    }
}