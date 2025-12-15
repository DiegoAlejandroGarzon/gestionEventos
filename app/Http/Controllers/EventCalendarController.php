<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventCalendarController extends Controller
{
    public function index()
    {
        $events = Event::all();

        return response()->json(
            $events->map(function ($event) {
                return [
                    'id'    => $event->id,
                    'title' => $event->name,
                    'start' => $event->event_date . ' ' . $event->start_time,
                    'end'   => $event->event_date_end
                        ? $event->event_date_end . ' ' . $event->end_time
                        : null,
                    'color' => $event->color_one ?? '#2563eb',
                    'url'   => route('event.generatePublicLink', $event->id),
                    'extendedProps' => [
                        'description' => $event->description,
                        'address'     => $event->address,
                        'city'        => optional($event->city)->name,
                        'publicLink'  => route('event.generatePublicLink', $event->id),
                    ],
                ];
            })
        );
    }
}
