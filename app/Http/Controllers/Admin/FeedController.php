<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimalGroup;
use App\Models\FeedPlan;
use App\Services\FeedCalculationService;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function calculator(FeedCalculationService $feedService)
    {
        $data = $feedService->getFeedCalculationSummary();
        $animalGroups = AnimalGroup::with('feedPlans')->get();

        return view('admin.feed.calculator', compact('data', 'animalGroups'));
    }

    public function updateGroupCount(Request $request, AnimalGroup $group)
    {
        $request->validate([
            'head_count' => 'required|integer|min:0',
        ]);

        $group->update(['head_count' => $request->head_count]);

        return back()->with('success', "Updated head count for {$group->name}.");
    }

    public function updateFeedPlan(Request $request)
    {
        $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:feed_plans,id',
            'plans.*.quantity_per_animal_kg' => 'required|numeric|min:0',
        ]);

        foreach ($request->plans as $planData) {
            FeedPlan::where('id', $planData['id'])->update([
                'quantity_per_animal_kg' => $planData['quantity_per_animal_kg']
            ]);
        }

        return back()->with('success', 'Feed plans updated successfully. Auto calculations recalculated.');
    }
}
