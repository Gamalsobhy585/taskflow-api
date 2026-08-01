<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(
    'tasks:dispatch-overdue-notifications'
)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();