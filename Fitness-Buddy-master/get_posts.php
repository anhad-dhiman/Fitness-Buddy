<?php
require 'db.php';

$stmt = $pdo->query("
    SELECT posts.id, posts.content, posts.created_at, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($posts);
?>
