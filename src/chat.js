document.addEventListener('DOMContentLoaded', () => {
    const chatArea = document.getElementById('chat-area');
    const sendButton = document.getElementById('send-btn');
    const messageInput = document.getElementById('textarea');
    const partnerId = new URLSearchParams(window.location.search).get('user_id');

    // メッセージ送信
    sendButton.addEventListener('click', (e) => {
        e.preventDefault();
        const text = messageInput.value;
        if (text.trim() === '') return;

        fetch('send-message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text, partner_id: partnerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                loadMessages(); // 再読み込み
            }
        });
    });

    // メッセージをロード
    function loadMessages() {
        fetch(`get-messages.php?partner_id=${partnerId}`)
        .then(response => response.json())
        .then(data => {
            chatArea.innerHTML = data.map(message => `
                <div class="${message.send_id == logged_in_user_id ? 'person1' : 'person2'}">
                    <div class="chat">
                        <small class="chat-time">${message.message_time}</small>
                        <span>${message.message_detail}</span>
                    </div>
                </div>
            `).join('');
        });
    }

    // 定期的にメッセージを更新
    setInterval(loadMessages, 5000);
});
