<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Animal::with('group');

        // Farmers see only their own animals
        if ($user->isFarmer()) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }
        if ($request->filled('search')) {
            $query->where(fn($q) => $q
                ->where('tag_number', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%'));
        }

        $animals = $query->orderBy('tag_number')->paginate($request->per_page ?? 15);

        return $this->paginated($animals, 'Animals retrieved successfully.');
    }

    public function show(Request $request, Animal $animal)
    {
        $user = $request->user();
        if ($user->isFarmer() && $animal->created_by !== $user->id) {
            return $this->error('You do not have access to this animal.', 403);
        }

        $animal->load('group', 'milkEntries', 'breedingRecords', 'healthRecords');

        return $this->success($animal, 'Animal retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_number'        => 'required|string|max:50|unique:animals,tag_number',
            'animal_type'       => 'required|in:Cow,Buffalo,Bull,Heifer,Calf,Goat,Sheep,Ox',
            'name'              => 'nullable|string|max:100',
            'breed'             => 'nullable|string|max:100',
            'group_id'          => 'nullable|exists:animal_groups,id',
            'dob'               => 'nullable|date',
            'born_in_farm'      => 'nullable|boolean',
            'purchase_from'     => 'nullable|string|max:150',
            'purchase_date'     => 'nullable|date',
            'purchase_cost'     => 'nullable|numeric|min:0',
            'current_weight'    => 'nullable|numeric|min:0',
            'lactation_number'  => 'nullable|integer|min:0',
            'health_status'     => 'nullable|string|max:100',
            'pregnancy_status'  => 'nullable|in:Open,Pregnant,Dry',
            'owner_name'        => 'nullable|string|max:150',
            'shed_number'       => 'nullable|string|max:50',
            'status'            => 'nullable|in:Active,Sold,Dead,Transferred',
        ]);

        $validated['created_by'] = $request->user()->id;

        $animal = Animal::create($validated);

        return $this->success($animal->load('group'), 'Animal created successfully.', 201);
    }

    public function update(Request $request, Animal $animal)
    {
        $user = $request->user();
        if ($user->isFarmer() && $animal->created_by !== $user->id) {
            return $this->error('You do not have access to this animal.', 403);
        }

        $validated = $request->validate([
            'tag_number'        => 'sometimes|string|max:50|unique:animals,tag_number,'.$animal->id,
            'animal_type'       => 'sometimes|in:Cow,Buffalo,Bull,Heifer,Calf,Goat,Sheep,Ox',
            'name'              => 'nullable|string|max:100',
            'breed'             => 'nullable|string|max:100',
            'group_id'          => 'nullable|exists:animal_groups,id',
            'dob'               => 'nullable|date',
            'born_in_farm'      => 'nullable|boolean',
            'purchase_from'     => 'nullable|string|max:150',
            'purchase_date'     => 'nullable|date',
            'purchase_cost'     => 'nullable|numeric|min:0',
            'current_weight'    => 'nullable|numeric|min:0',
            'lactation_number'  => 'nullable|integer|min:0',
            'health_status'     => 'nullable|string|max:100',
            'pregnancy_status'  => 'nullable|in:Open,Pregnant,Dry',
            'owner_name'        => 'nullable|string|max:150',
            'shed_number'       => 'nullable|string|max:50',
            'status'            => 'nullable|in:Active,Sold,Dead,Transferred',
        ]);

        $animal->update($validated);

        return $this->success($animal->load('group'), 'Animal updated successfully.');
    }

    public function destroy(Request $request, Animal $animal)
    {
        if ($request->user()->isFarmer()) {
            return $this->error('Farmers cannot delete animals.', 403);
        }

        $animal->delete();

        return $this->success(null, 'Animal deleted successfully.');
    }

    public function summary(Request $request)
    {
        $user  = $request->user();
        $query = Animal::query();

        if ($user->isFarmer()) {
            $query->where('created_by', $user->id);
        }

        $data = [
            'total'    => (clone $query)->count(),
            'active'   => (clone $query)->where('status', 'Active')->count(),
            'by_type'  => (clone $query)->selectRaw('animal_type, COUNT(*) as count')
                ->groupBy('animal_type')->pluck('count', 'animal_type'),
            'pregnant' => (clone $query)->where('pregnancy_status', 'Pregnant')->count(),
            'milking'  => (clone $query)->where('status', 'Active')
                ->where('pregnancy_status', '!=', 'Dry')
                ->where('lactation_number', '>', 0)->count(),
        ];

        return $this->success($data, 'Animal summary retrieved.');
    }
}