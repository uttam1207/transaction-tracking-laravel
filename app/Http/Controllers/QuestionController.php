<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /* ────────────────────────────────────────
       QUESTIONS
    ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Question::with(['user', 'answers'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        // Filter by status tab
        if ($request->filter === 'open') {
            $query->where('status', 'open');
        } elseif ($request->filter === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($request->filter === 'closed') {
            $query->where('status', 'closed');
        } elseif ($request->filter === 'mine') {
            $query->where('user_id', Auth::id());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $questions = $query->paginate(15)->withQueryString();

        $counts = [
            'all'      => Question::count(),
            'open'     => Question::where('status', 'open')->count(),
            'resolved' => Question::where('status', 'resolved')->count(),
            'mine'     => Question::where('user_id', Auth::id())->count(),
        ];

        return view('questions.index', compact('questions', 'counts'));
    }

    public function create()
    {
        return view('questions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255|min:10',
            'body'  => 'required|string|min:20',
        ]);

        $question = Question::create([
            'user_id' => Auth::id(),
            'title'   => $data['title'],
            'body'    => $data['body'],
            'status'  => 'open',
        ]);

        return redirect()->route('questions.show', $question)
            ->with('success', 'Your question has been posted.');
    }

    public function show(Question $question)
    {
        // Increment views (skip if same session already viewed)
        $viewKey = 'question_viewed_' . $question->id;
        if (! session()->has($viewKey)) {
            $question->incrementViews();
            session()->put($viewKey, true);
        }

        $question->load(['user', 'answers.user']);

        return view('questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $this->authorizeEdit($question);

        return view('questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $this->authorizeEdit($question);

        $data = $request->validate([
            'title'  => 'required|string|max:255|min:10',
            'body'   => 'required|string|min:20',
            'status' => 'required|in:open,resolved,closed',
        ]);

        // Only super_admin can pin questions
        if (Auth::user()->isSuperAdmin() && $request->has('is_pinned')) {
            $data['is_pinned'] = $request->boolean('is_pinned');
        }

        $question->update($data);

        return redirect()->route('questions.show', $question)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $this->authorizeDelete($question);

        $question->delete();

        return redirect()->route('questions.index')
            ->with('success', 'Question deleted.');
    }

    /* ────────────────────────────────────────
       ANSWERS
    ──────────────────────────────────────── */

    public function storeAnswer(Request $request, Question $question)
    {
        $data = $request->validate([
            'body' => 'required|string|min:10',
        ]);

        $question->answers()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        // Auto-resolve question when it gets its first answer
        if ($question->status === 'open' && $question->answers()->count() === 1) {
            // keep it open – owner decides when to resolve
        }

        return redirect()->route('questions.show', $question)
            ->with('success', 'Your answer has been posted.');
    }

    public function updateAnswer(Request $request, Question $question, Answer $answer)
    {
        $this->authorizeAnswerEdit($answer);

        $data = $request->validate([
            'body' => 'required|string|min:10',
        ]);

        $answer->update(['body' => $data['body']]);

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer updated.');
    }

    public function destroyAnswer(Question $question, Answer $answer)
    {
        $this->authorizeAnswerDelete($answer);

        $answer->delete();

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer deleted.');
    }

    public function acceptAnswer(Question $question, Answer $answer)
    {
        // Only the question owner or super_admin can accept an answer
        if (! Auth::user()->isSuperAdmin() && $question->user_id !== Auth::id()) {
            abort(403, 'You are not authorised to accept answers on this question.');
        }

        // Unaccept all other answers first
        $question->answers()->update(['is_accepted' => false]);
        $answer->update(['is_accepted' => true]);

        // Mark question as resolved
        $question->update(['status' => 'resolved']);

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer marked as accepted and question resolved.');
    }

    /* ────────────────────────────────────────
       PRIVATE HELPERS
    ──────────────────────────────────────── */

    private function authorizeEdit(Question $question): void
    {
        if (! $question->canEdit(Auth::user())) {
            abort(403, 'You can only edit your own questions.');
        }
    }

    private function authorizeDelete(Question $question): void
    {
        if (! $question->canDelete(Auth::user())) {
            abort(403, 'You can only delete your own questions.');
        }
    }

    private function authorizeAnswerEdit(Answer $answer): void
    {
        if (! $answer->canEdit(Auth::user())) {
            abort(403, 'You can only edit your own answers.');
        }
    }

    private function authorizeAnswerDelete(Answer $answer): void
    {
        if (! $answer->canDelete(Auth::user())) {
            abort(403, 'You can only delete your own answers.');
        }
    }
}
