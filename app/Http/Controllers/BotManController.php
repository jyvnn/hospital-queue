<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        if ($message === '') {
            return response()->json(['reply' => "I didn't receive a message. Try typing 'hi' or 'help'."], 400);
        }

        $lower = strtolower($message);

        if (strpos($lower, 'hi') !== false || strpos($lower, 'hello') !== false) {
            $reply = 'Hello — I am the hospital chatbot. How can I help you today?';
        } elseif (strpos($lower, 'help') !== false) {
            $reply = "You can ask me things like:\n - 'list doctors'\n - 'my appointments'\n - 'how to register'";
        } elseif (strpos($lower, 'list doctors') !== false || strpos($lower, 'doctors') !== false) {
            $reply = 'You can view doctors on the Doctors page: ' . url('/doctors');
        } elseif (strpos($lower, 'appointments') !== false) {
            $reply = 'To see appointments open: ' . url('/appointments');
        } else {
            // fallback simple reply
            $reply = "Sorry, I don't understand that yet. Try 'help' or say 'hi'.";
        }

        return response()->json(['reply' => $reply]);
    }
}
