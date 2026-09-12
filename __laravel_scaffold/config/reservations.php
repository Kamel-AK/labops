<?php

return [
    'max_duration_minutes' => (int) env('RESERVATION_MAX_DURATION_MINUTES', 240),
    'advance_booking_days' => (int) env('RESERVATION_ADVANCE_BOOKING_DAYS', 14),
    'active_statuses' => ['confirmed', 'checked_in'],
];
