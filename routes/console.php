<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kost:generate-invoices')->daily();
Schedule::command('kost:mark-overdue')->daily();
Schedule::command('kost:cancel-expired-bookings')->hourly();
Schedule::command('kost:prune-logs')->daily();
