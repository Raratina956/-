document.addEventListener('DOMContentLoaded', () => {
    const chatArea = document.getElementById('chat-area');
    const sendButton = document.getElementById('send-btn');
    const messageInput = document.getElementById('textarea');
    const partnerId = new URLSearchParams(window.location.search).get('user_id');

    // メッセージ送信
    sendButton.addEventListener('click', (e) => {
        e.preventDefault();
    
        const text = messageInput.value.trim();
        if (text === '') return;
    
        const formData = new FormData();
        formData.append('text', text); // 必須パラメータ
        formData.append('partner_id', partnerId); // 必須パラメータ
    
        fetch('send-message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                loadMessages();
            } else {
                console.error(data.error);
            }
        })
        .catch(error => console.error('通信エラー:', error));
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
