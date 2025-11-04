<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chatbot</title>
    <style>
        /*
         CHAT STYLE GUIDE / SECTION LABELS
         ---------------------------------
         This file contains the inline CSS and JS for the minimal chat UI.
         If you want to change sizes, colors or behavior, edit the sections below:

         - Chat container:       CSS selector `.chat` (width, background, radius)
         - Messages area:        CSS selector `.messages` (height, padding, scroll)
         - Bot bubble:           CSS selector `.message.bot .bubble` (font-size, padding, color)
         - User bubble:          CSS selector `.message.user .bubble` (font-size, padding, color)
         - Input area:           CSS selector `.input` and `.input input` / `.input button`
         - Responsive rules:     @media (max-width: 700px) (mobile sized overrides)

         JS sections:
         - appendMessage(text, who): creates and inserts a message bubble
         - sendMessage(): sends the message to the `/botman` endpoint and appends the reply

         RECOMMENDED PRESET: MEDIUM (applied here)
         - Bot: 1.3rem (desktop)
         - User: 1.15rem (desktop)
         - Mobile sizes are reduced under the media query
        */
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; }

        /* Chat container */
        .chat { max-width: 920px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 6px 18px rgba(2,6,23,.06); overflow: hidden; }

        /* Messages area */
        .messages { height: 620px; padding: 28px; overflow-y: auto; border-bottom: 1px solid #eee; }
        .message { margin-bottom: 18px; }
        .message.user { text-align: right; }

          /* Animation helpers: messages start slightly down & transparent, then
              transition to their final position and full opacity for a smooth pop */
          .message { opacity: 0; transform: translateY(10px); transition: opacity 240ms ease, transform 240ms cubic-bezier(.2,.8,.2,1); }
          .message.visible { opacity: 1; transform: translateY(0); }

        /* ------------------------------------------------------------------ */
        /* BOT BUBBLE - purpose: readable system/help replies (MEDIUM preset)  */
        /* Desktop: font-size 1.3rem; medium padding for balanced layout       */
        /* ------------------------------------------------------------------ */
        .message.bot .bubble {
            background: #eef2ff;
            color: #0f172a;
            font-size: 1.3rem;      /* medium desktop bot size */
            line-height: 1.45;
            padding: 18px 22px;     /* medium bubble padding */
            border-radius: 20px;
            max-width: 82%;
            font-weight: 400;       /* normal, not bold */
            display: inline-block;
        }

        /* ------------------------------------------------------------------ */
        /* USER BUBBLE - purpose: display user-entered text (MEDIUM preset)   */
        /* Desktop: font-size 1.15rem; medium padding for touch targets       */
        /* ------------------------------------------------------------------ */
        .message.user .bubble {
            background: #059e9a;
            color: #fff;
            font-size: 1.15rem;     /* medium desktop user size */
            line-height: 1.45;
            padding: 16px 20px;     /* medium bubble padding for user */
            border-radius: 20px;
            max-width: 82%;
            font-weight: 400;       /* normal, not bold */
            display: inline-block;
            box-shadow: 0 3px 10px rgba(5,158,154,0.08);
        }

        /* Input area styling */
        .input { display:flex; gap:8px; padding: 16px; }
        .input input { flex:1; padding: 14px 16px; border-radius: 10px; border:1px solid #cfcfcf; font-size: 1.12rem; }
        .input button { padding: 12px 18px; border-radius:10px; border:0; background:#2563eb; color:#fff; font-weight:600; font-size:1.05rem; }

    /* Keep bot bubble size consistent in messages list (MEDIUM preset) */
    .messages .message.bot .bubble { font-size: 1.3rem; }

        /* Responsive adjustments for small screens */
        @media (max-width: 700px) {
            .chat { margin: 20px; max-width: calc(100% - 40px); }
            .messages { height: 520px; }
            /* scale down bubbles on mobile to avoid overflow (mobile MEDIUM) */
            .message.bot .bubble { font-size: 1.05rem; padding: 10px 12px; max-width: 92%; }
            .message.user .bubble { font-size: 0.95rem; padding: 8px 10px; max-width: 92%; }
            .input input { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="chat">
        <div class="messages" id="messages"></div>
        <div class="input">
            <input id="messageInput" placeholder="Type a message (try: hi, help, doctors)" />
            <button id="sendBtn">Send</button>
        </div>
    </div>

<script>
// JS SECTION LABELS:
// - appendMessage(text, who): creates a message bubble and inserts it into the DOM
// - sendMessage(): POSTs to /botman and appends replies
const messagesEl = document.getElementById('messages');
const input = document.getElementById('messageInput');
const btn = document.getElementById('sendBtn');

function appendMessage(text, who){
    // create container and bubble, then append
    const wrapper = document.createElement('div');
    // start with hidden state; we'll flip to visible to trigger CSS transition
    wrapper.className = 'message ' + (who === 'user' ? 'user' : 'bot') + ' hidden';
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = text;
    wrapper.appendChild(bubble);
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    // Trigger animation on next frame to ensure transition runs
    requestAnimationFrame(() => {
        wrapper.classList.remove('hidden');
        wrapper.classList.add('visible');
    });
}

async function sendMessage(){
    const text = input.value.trim();
    if(!text) return;
    appendMessage(text, 'user');
    input.value = '';
    try{
        const res = await fetch('{{ url('/botman') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        if(res.ok && data.reply){
            appendMessage(data.reply, 'bot');
        } else if(data.reply){
            appendMessage(data.reply, 'bot');
        } else {
            appendMessage('No reply from server', 'bot');
        }
    } catch (err) {
        appendMessage('Error: ' + err.message, 'bot');
    }
}

btn.addEventListener('click', sendMessage);
input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ sendMessage(); } });

// welcome message
appendMessage('Welcome! Type "hi" or "help" to get started.', 'bot');
</script>
</body>
</html>
