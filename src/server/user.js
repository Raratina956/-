const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql');

const app = express();
app.use(bodyParser.json());

const db = mysql.createConnection({
    host: 'mysql310.phy.lolipop.lan',
    user: 'LAA1516821',
    password: 'nomodon',
    database: 'LAA1516821-spotlink'
});

db.connect(err => {
    if (err) throw err;
    console.log('MySQL connected...');
});

app.post('/follow', (req, res) => {
    const userId = req.body.userId; // 認証されたユーザーIDを取得
    const followedId = req.body.followedId;
    const isFollowing = req.body.following;

    if (isFollowing) {
        // フォロー
        const sql = 'INSERT INTO relationships (follower_id, followed_id) VALUES (?, ?)';
        db.query(sql, [userId, followedId], (err, result) => {
            if (err) throw err;
            res.sendStatus(200);
        });
    } else {
        // フォロー解除
        const sql = 'DELETE FROM relationships WHERE follower_id = ? AND followed_id = ?';
        db.query(sql, [userId, followedId], (err, result) => {
            if (err) throw err;
            res.sendStatus(200);
        });
    }
});

app.listen(3000, () => {
    console.log('Server is running on port 3000');
});
