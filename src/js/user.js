document.getElementById('followButton').addEventListener('click', function() {
    const isFollowing = this.classList.toggle('following');
    fetch('/follow', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            userId: 1, // 認証されたユーザーIDを設定
            followedId: 2, // フォロー対象のユーザーIDを設定
            following: isFollowing 
        })
    });
});