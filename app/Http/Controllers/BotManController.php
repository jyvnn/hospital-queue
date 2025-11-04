<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class BotManController extends Controller
{
    /**
     * Show a minimal chat UI that posts to /botman
     */
    public function show()
    {
        return view('botman.chat');
    }

    /**
     * Handle incoming chat messages (sent via AJAX from the simple UI).
     *
     * This implementation is intentionally lightweight: it provides a
     * small set of canned responses so the chat UI is functional out of the box.
     * If you prefer to use the BotMan framework fully, we can update this
     * method to bootstrap BotMan and delegate handling to its driver.
     */
    public function handle(Request $request)
    {
        $message = trim((string) $request->input('message', ''));

        // If the user did not send a message
        if ($message === '') {
            return response()->json(['reply' => "Input Unrecognized. Please Try Again"], 400);
        }

        $lower = strtolower($message);

        //
        if (strpos($lower, 'hi') !== false || strpos($lower, 'hello') !== false) {
            $reply = 'Hello — I am DocChat. How can I help you today?';
        } elseif (strpos($lower, 'help') !== false) {
            // return a concise, maintainable list of supported commands
            $commands = [
                "`hi` — Greet the bot",
                "`help` — Show this list of commands",
                "`list doctors` / `doctors` — Show available doctors (name, specialty, availability)",
                "`queue` / `waiting` — Show queue summary and next patients",
            ];

            // Keep the help short and focused: one necessary command per line
            $reply = "You can use the following commands:\n" . implode("\n", array_map(function($c){ return ' - ' . $c; }, $commands));
        } elseif (strpos($lower, 'list doctors') !== false || strpos($lower, 'doctors') !== false) {
            // Return a readable list of doctors (name, specialty, availability)
            // Prefer showing availability information when present.
            $doctors = Doctor::orderBy('last_name')->get();

            if ($doctors->isEmpty()) {
                $reply = "There are currently no doctors in the system.";
            } else {
                $lines = [];
                foreach ($doctors as $d) {
                    $name = $d->full_name ?? trim(($d->first_name ?? '') . ' ' . ($d->last_name ?? ''));
                    $spec = $d->specialty ? " ({$d->specialty})" : '';
                    $avail = isset($d->availability) && $d->availability !== '' ? " — {$d->availability}" : '';
                    $lines[] = "Dr. {$name}{$spec}{$avail}";
                }
                // join lines with newlines for readability in chat UI
                $reply = "Available doctors:\n" . implode("\n", $lines);
            }
        } elseif (strpos($lower, 'queue') !== false || strpos($lower, 'waiting') !== false || strpos($lower, 'next patient') !== false || strpos($lower, 'call next') !== false) {
            // Provide a quick summary of the current queue (waiting / in progress) and the next few patients
            try {
                $waitingCount = Patient::where('status', 'Waiting')->count();
                $inProgressCount = Patient::where('status', 'In Progress')->count();

                $next = Patient::where('status', 'Waiting')
                    ->orderBy('check_in_time', 'asc')
                    ->first();

                $lines = ["Queue summary:", "- Waiting: {$waitingCount}", "- In Progress: {$inProgressCount}"];

                if ($next) {
                    $waited = Carbon::parse($next->check_in_time)->diffForHumans();
                    $assigned = $next->assigned_doctor_id ? (Doctor::find($next->assigned_doctor_id)->full_name ?? 'Dr. (unknown)') : 'Unassigned';
                    $lines[] = "Next: {$next->full_name} — checked in {$waited} — assigned: {$assigned}";
                } else {
                    $lines[] = 'Next: (none)';
                }

                $queueList = Patient::where('status', 'Waiting')
                    ->orderBy('check_in_time', 'asc')
                    ->limit(5)
                    ->get();

                if ($queueList->isNotEmpty()) {
                    $lines[] = "Top waiting patients:";
                    foreach ($queueList as $i => $p) {
                        $num = $i + 1;
                        $when = Carbon::parse($p->check_in_time)->diffForHumans();
                        $lines[] = "{$num}) {$p->full_name} — checked in {$when}";
                    }
                }

                $reply = implode("\n", $lines);
            } catch (\Exception $e) {
                // in case of DB issues, return a safe message
                $reply = 'Unable to retrieve queue right now. Please try again later.';
            }
        } elseif (strpos($lower, 'appointments') !== false) {
            // Show a short list of upcoming scheduled appointments (if any)
            $upcoming = Appointment::whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', Carbon::now())
                ->orderBy('scheduled_at')
                ->with('doctor')
                ->limit(5)
                ->get();

            if ($upcoming->isEmpty()) {
                $reply = 'There are no upcoming scheduled appointments. You can view the appointments page here: ' . url('/appointments');
            } else {
                $lines = ['Upcoming appointments:'];
                foreach ($upcoming as $a) {
                    $dt = Carbon::parse($a->scheduled_at)->toDayDateTimeString();
                    $doc = $a->doctor ? ('Dr. ' . ($a->doctor->full_name ?? ($a->doctor->first_name . ' ' . $a->doctor->last_name))) : 'Unassigned';
                    $status = $a->status ? " ({$a->status})" : '';
                    $lines[] = "- {$dt} — {$doc}{$status}";
                }
                $reply = implode("\n", $lines);
            }
        } else {
            // fallback simple reply
            $reply = "Sorry, I don't understand that yet. Try 'help' or say 'hi'.";
        }

        return response()->json(['reply' => $reply]);
    }
}
