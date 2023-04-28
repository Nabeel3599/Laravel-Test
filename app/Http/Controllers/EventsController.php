<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Date;

class EventsController extends BaseController
{
    public function getWarmupEvents()
    {
        return Event::all();
    }

    /* TODO: complete getEventsWithWorkshops so that it returns all events including the workshops
     Requirements:
    - maximum 2 sql queries
    - Don't post process query result in PHP
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - partial or not working answers also get graded so make sure you commit what you have

    Sample response on GET /events:
    ```json
    [
        {
            "id": 1,
            "name": "Laravel convention 2020",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 1,
                    "start": "2020-02-21 10:00:00",
                    "end": "2020-02-21 16:00:00",
                    "event_id": 1,
                    "name": "Illuminate your knowledge of the laravel code base",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        },
        {
            "id": 2,
            "name": "Laravel convention 2021",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 2,
                    "start": "2021-10-21 10:00:00",
                    "end": "2021-10-21 18:00:00",
                    "event_id": 2,
                    "name": "The new Eloquent - load more with less",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                },
                {
                    "id": 3,
                    "start": "2021-11-21 09:00:00",
                    "end": "2021-11-21 17:00:00",
                    "event_id": 2,
                    "name": "AutoEx - handles exceptions 100% automatic",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        },
        {
            "id": 3,
            "name": "React convention 2021",
            "created_at": "2021-04-25T09:32:27.000000Z",
            "updated_at": "2021-04-25T09:32:27.000000Z",
            "workshops": [
                {
                    "id": 4,
                    "start": "2021-08-21 10:00:00",
                    "end": "2021-08-21 18:00:00",
                    "event_id": 3,
                    "name": "#NoClass pure functional programming",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                },
                {
                    "id": 5,
                    "start": "2021-08-21 09:00:00",
                    "end": "2021-08-21 17:00:00",
                    "event_id": 3,
                    "name": "Navigating the function jungle",
                    "created_at": "2021-04-25T09:32:27.000000Z",
                    "updated_at": "2021-04-25T09:32:27.000000Z"
                }
            ]
        }
    ]
     */

    public function getEventsWithWorkshops()
    {
        // throw new \Exception('implement in coding task 1');

        $result = Event::select(
            'events.id AS event_id',
            'events.name AS event_name',
            'events.created_at AS event_created_at',
            'events.updated_at AS event_updated_at',
            'workshops.id AS workshop_id',
            'workshops.start AS workshop_start',
            'workshops.end AS workshop_end',
            'workshops.event_id AS workshop_event_id',
            'workshops.name AS workshop_name',
            'workshops.created_at AS workshop_created_at',
            'workshops.updated_at AS workshop_updated_at'
        )
            ->leftJoin('workshops', 'events.id', '=', 'workshops.event_id')
            ->orderBy('events.id', 'ASC')
            ->get();


        $events = [];
        foreach ($result as $row) {
            $eventId = $row->event_id;
            if (!isset($events[$eventId])) {
                $events[$eventId] = [
                    'id' => $eventId,
                    'name' => $row->event_name,
                    'created_at' => $row->event_created_at,
                    'updated_at' => $row->event_updated_at,
                    'workshops' => [],
                ];
            }

            if (!is_null($row->workshop_id)) {
                $events[$eventId]['workshops'][] = [
                    'id' => $row->workshop_id,
                    'start' => $row->workshop_start,
                    'end' => $row->workshop_end,
                    'event_id' => $row->workshop_event_id,
                    'name' => $row->workshop_name,
                    'created_at' => $row->workshop_created_at,
                    'updated_at' => $row->workshop_updated_at,
                ];
            }
        }

        return array_values($events);
    }


    /* TODO: complete getFutureEventWithWorkshops so that it returns events with workshops, that have not yet started
    Requirements:
    - only events that have not yet started should be included
    - the event starting time is determined by the first workshop of the event
    - the eloquent expressions should result in maximum 3 SQL queries, no matter the amount of events
    - Don't post process query result in PHP
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - partial or not working answers also get graded so make sure you commit what you have
    - join, whereIn, min, groupBy, havingRaw might be helpful
    - in the sample data set  the event with id 1 is already in the past and should therefore be excluded

    Sample response on GET /futureevents:
    ```json
    [
        {
            "id": 2,
            "name": "Laravel convention 2021",
            "created_at": "2021-04-20T07:01:14.000000Z",
            "updated_at": "2021-04-20T07:01:14.000000Z",
            "workshops": [
                {
                    "id": 2,
                    "start": "2021-10-21 10:00:00",
                    "end": "2021-10-21 18:00:00",
                    "event_id": 2,
                    "name": "The new Eloquent - load more with less",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                },
                {
                    "id": 3,
                    "start": "2021-11-21 09:00:00",
                    "end": "2021-11-21 17:00:00",
                    "event_id": 2,
                    "name": "AutoEx - handles exceptions 100% automatic",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                }
            ]
        },
        {
            "id": 3,
            "name": "React convention 2021",
            "created_at": "2021-04-20T07:01:14.000000Z",
            "updated_at": "2021-04-20T07:01:14.000000Z",
            "workshops": [
                {
                    "id": 4,
                    "start": "2021-08-21 10:00:00",
                    "end": "2021-08-21 18:00:00",
                    "event_id": 3,
                    "name": "#NoClass pure functional programming",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                },
                {
                    "id": 5,
                    "start": "2021-08-21 09:00:00",
                    "end": "2021-08-21 17:00:00",
                    "event_id": 3,
                    "name": "Navigating the function jungle",
                    "created_at": "2021-04-20T07:01:14.000000Z",
                    "updated_at": "2021-04-20T07:01:14.000000Z"
                }
            ]
        }
    ]
    ```
     */

    public function getFutureEventsWithWorkshops()
    {
        // throw new \Exception('implement in coding task 2');
        $futureEvents = Event::select('events.id', 'events.name', 'events.created_at', 'events.updated_at', 'workshops.id as workshop_id', 'workshops.start', 'workshops.end', 'workshops.name as workshop_name', 'workshops.created_at as workshop_created_at', 'workshops.updated_at as workshop_updated_at')
            ->join('workshops', 'events.id', '=', 'workshops.event_id')
            ->groupBy('events.id', 'workshops.id')
            ->havingRaw('MIN(workshops.start) > NOW()')
            ->get();

        $events = [];
        foreach ($futureEvents as $event) {
            $eventId = $event->id;
            if (!isset($events[$eventId])) {
                $events[$eventId] = [
                    'id' => $eventId,
                    'name' => $event->name,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                    'workshops' => [],
                ];
            }

            if ($event->workshop_id) {
                $events[$eventId]['workshops'][] = [
                    'id' => $event->workshop_id,
                    'start' => $event->start,
                    'end' => $event->end,
                    'event_id' => $eventId,
                    'name' => $event->workshop_name,
                    'created_at' => $event->workshop_created_at,
                    'updated_at' => $event->workshop_updated_at,
                ];
            }
        }

        return array_values($events);
    }
}
