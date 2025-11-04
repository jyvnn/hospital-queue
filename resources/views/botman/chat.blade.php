<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chatbot</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; }
        .chat { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); overflow: hidden; }
        .messages { height: 400px; padding: 16px; overflow-y: auto; border-bottom: 1px solid #eee; }
        .message { margin-bottom: 12px; }
        .message.user { text-align: right; }
        .message .bubble { display: inline-block; padding: 10px 14px; border-radius: 16px; max-width: 75%; }
        .message.user .bubble { background: #0ea5a4; color: #fff; }
        .message.bot .bubble { background: #eef2ff; color: #0f172a; }
        .input { display:flex; gap:8px; padding: 12px; }
        .input input { flex:1; padding: 10px 12px; border-radius: 6px; border:1px solid #ddd; }
        .input button { padding: 10px 14px; border-radius:6px; border:0; background:#2563eb; color:#fff; }
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
const messagesEl = document.getElementById('messages');
const input = document.getElementById('messageInput');
const btn = document.getElementById('sendBtn');

function appendMessage(text, who){
    const wrapper = document.createElement('div');
    wrapper.className = 'message ' + (who === 'user' ? 'user' : 'bot');
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = text;
    wrapper.appendChild(bubble);
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
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
