<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\QueueTicket;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        /**
         * Badge counts rendered in both side navigation partials.
         */
        View::composer(['layouts.nav.resident', 'layouts.nav.admin'], function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            if ($user->isAdmin()) {
                $counts = [
                    'open_complaints' => Complaint::whereIn('status', ['received', 'in_progress', 'on_hold'])->count(),
                    'pending_docs' => DocumentRequest::whereIn('status', ['pending', 'under_review'])->count(),
                    'pending_appts' => Appointment::where('status', 'pending')
                        ->whereDate('appointment_date', '>=', now()->toDateString())
                        ->count(),
                    'waiting' => QueueTicket::forDate()->whereIn('status', ['waiting', 'called', 'serving'])->count(),
                ];
            } else {
                $counts = [
                    'complaints' => Complaint::mine($user->id)
                        ->whereIn('status', ['received', 'in_progress', 'on_hold'])
                        ->count(),
                    'documents' => DocumentRequest::where('user_id', $user->id)
                        ->whereNotIn('status', ['released', 'rejected'])
                        ->count(),
                    'appointments' => Appointment::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                ];
            }

            $view->with('navCounts', $counts);
        });
    }
}
