<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FraudAlert;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $q = $request->q;

        $results = [];

        // Transactions
        $transactions = Transaction::where('transaction_id', 'like', "%{$q}%")
            ->orWhere('sender_name', 'like', "%{$q}%")
            ->orWhere('receiver_name', 'like', "%{$q}%")
            ->orWhere('reference', 'like', "%{$q}%")
            ->limit(5)->get();

        foreach ($transactions as $t) {
            $results[] = [
                'type'     => 'Transaction',
                'icon'     => 'arrow-left-right',
                'color'    => 'primary',
                'title'    => $t->transaction_id,
                'subtitle' => $t->sender_name . ' → ' . $t->receiver_name . ' · ' . $t->currency . ' ' . number_format($t->amount, 2),
                'url'      => route('admin.transactions.show', $t->id),
            ];
        }

        // Users
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(5)->get();

        foreach ($users as $u) {
            $results[] = [
                'type'     => 'User',
                'icon'     => 'person',
                'color'    => 'success',
                'title'    => $u->name,
                'subtitle' => $u->email,
                'url'      => route('admin.users.show', $u->id),
            ];
        }

        // Employees
        $employees = Employee::with('user')
            ->where('employee_id', 'like', "%{$q}%")
            ->orWhere('designation', 'like', "%{$q}%")
            ->orWhereHas('user', fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
            ->limit(5)->get();

        foreach ($employees as $emp) {
            $results[] = [
                'type'     => 'Employee',
                'icon'     => 'person-badge',
                'color'    => 'info',
                'title'    => $emp->full_name,
                'subtitle' => $emp->employee_id . ' · ' . $emp->designation,
                'url'      => route('admin.employees.show', $emp->id),
            ];
        }

        // Tasks
        $tasks = Task::where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(3)->get();

        foreach ($tasks as $task) {
            $results[] = [
                'type'     => 'Task',
                'icon'     => 'kanban',
                'color'    => 'warning',
                'title'    => $task->title,
                'subtitle' => 'Priority: ' . $task->priority . ' · Status: ' . $task->status,
                'url'      => '#',
            ];
        }

        // Fraud Alerts
        $alerts = FraudAlert::where('description', 'like', "%{$q}%")
            ->orWhere('alert_type', 'like', "%{$q}%")
            ->limit(3)->get();

        foreach ($alerts as $alert) {
            $results[] = [
                'type'     => 'Fraud Alert',
                'icon'     => 'shield-exclamation',
                'color'    => 'danger',
                'title'    => ucfirst(str_replace('_', ' ', $alert->alert_type)),
                'subtitle' => 'Severity: ' . $alert->severity . ' · ' . $alert->status,
                'url'      => route('admin.fraud-alerts.show', $alert->id),
            ];
        }

        return response()->json([
            'success' => true,
            'query'   => $q,
            'count'   => count($results),
            'results' => $results,
        ]);
    }
}
