document.getElementById('favoriteForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = event.target;
    var formData = new FormData(form);

    fetch(form.action, {
        method: form.method,
        body: formData
    }).then(response => response.text())
      .then(data => {
          // 画像を切り替える
          var img = document.getElementById('favoriteImage');
          if (formData.get('action') === 'follow') {
              img.src = 'img/star.png';
              form.querySelector('input[name="action"]').value = 'unfollow';
          } else {
              img.src = 'img/notstar.png';
              form.querySelector('input[name="action"]').value = 'follow';
          }
      }).catch(error => console.error('エラー:', error));
});
