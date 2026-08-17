<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\RecruitmentApplication;
use App\Models\RecruitmentJob;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    // ── Jobs ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = RecruitmentJob::with('department', 'designation', 'createdBy');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('job_code', 'like', '%' . $request->search . '%')
            );
        }

        $jobs        = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();
        $designations= Designation::active()->orderBy('name')->get();

        $stats = [
            'open'   => RecruitmentJob::where('status', 'open')->count(),
            'filled' => RecruitmentJob::where('status', 'filled')->count(),
            'draft'  => RecruitmentJob::where('status', 'draft')->count(),
        ];

        return view('admin.recruitment.index', compact('jobs', 'departments', 'designations', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:150',
            'job_code'        => 'required|string|max:50|unique:recruitment_jobs,job_code',
            'department_id'   => 'nullable|exists:departments,id',
            'designation_id'  => 'nullable|exists:designations,id',
            'vacancies'       => 'required|integer|min:1',
            'employment_type' => 'required|string|max:50',
            'status'          => 'required|in:draft,open,on_hold,closed,filled',
            'posted_date'     => 'nullable|date',
            'closing_date'    => 'nullable|date|after_or_equal:posted_date',
        ]);

        $job = RecruitmentJob::create(array_merge(
            $request->only(['title', 'job_code', 'department_id', 'designation_id', 'vacancies',
                            'description', 'requirements', 'employment_type', 'salary_range_min',
                            'salary_range_max', 'posted_date', 'closing_date', 'location', 'status']),
            ['created_by' => auth()->id()]
        ));

        return response()->json(['success' => true, 'message' => 'Job posted.', 'job' => $job->load('department', 'designation')]);
    }

    public function update(Request $request, RecruitmentJob $recruitmentJob)
    {
        $request->validate([
            'title'    => 'required|string|max:150',
            'status'   => 'required|in:draft,open,on_hold,closed,filled',
            'vacancies'=> 'required|integer|min:1',
        ]);

        $recruitmentJob->update($request->only([
            'title', 'department_id', 'designation_id', 'vacancies', 'description',
            'requirements', 'employment_type', 'salary_range_min', 'salary_range_max',
            'posted_date', 'closing_date', 'location', 'status',
        ]));

        return response()->json(['success' => true, 'message' => 'Job updated.']);
    }

    public function destroy(RecruitmentJob $recruitmentJob)
    {
        $recruitmentJob->delete();
        return response()->json(['success' => true, 'message' => 'Job deleted.']);
    }

    // ── Applications ──────────────────────────────────────────────────────────

    public function applications(Request $request, RecruitmentJob $recruitmentJob)
    {
        $applications = $recruitmentJob->applications()
            ->when($request->stage, fn($q) => $q->where('stage', $request->stage))
            ->latest('applied_at')
            ->paginate(20);

        return view('admin.recruitment.applications', compact('recruitmentJob', 'applications'));
    }

    public function storeApplication(Request $request, RecruitmentJob $recruitmentJob)
    {
        $request->validate([
            'applicant_name'  => 'required|string|max:150',
            'applicant_email' => 'required|email|max:150',
            'applicant_phone' => 'nullable|string|max:20',
            'stage'           => 'required|in:applied,screening,shortlisted,interview_scheduled,interviewed,offer_sent,hired,rejected',
        ]);

        // Prevent duplicate application from same email for the same job
        $duplicate = $recruitmentJob->applications()
            ->where('applicant_email', $request->applicant_email)
            ->exists();
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'An application from this email address already exists for this job.',
            ], 422);
        }

        $app = $recruitmentJob->applications()->create($request->only([
            'applicant_name', 'applicant_email', 'applicant_phone',
            'cover_letter', 'stage', 'notes', 'offered_salary', 'interview_date',
        ]));

        return response()->json(['success' => true, 'message' => 'Application added.', 'application' => $app]);
    }

    public function updateApplication(Request $request, RecruitmentApplication $recruitmentApplication)
    {
        $request->validate([
            'stage' => 'required|in:applied,screening,shortlisted,interview_scheduled,interviewed,offer_sent,hired,rejected',
        ]);

        $recruitmentApplication->update($request->only([
            'stage', 'notes', 'offered_salary', 'interview_date',
        ]));

        return response()->json(['success' => true, 'message' => 'Application updated.']);
    }
}
