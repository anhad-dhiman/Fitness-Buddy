<?php
require 'db.php';  // Include the database connection

$stmt = $conn->query("
    SELECT posts.id, posts.content, posts.created_at, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is logged in
session_start();
$user_id = $_SESSION['user_id'] ?? null;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Buddy - Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script defer src="home.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Recent Posts</h2>

        <!-- Check if the user is logged in -->
        <?php if ($user_id): ?>
            <div class="mb-3">
                <form action="create_post.php" method="POST">
                    <div class="mb-3">
                        <label for="content" class="form-label">Create a Post</label>
                        <textarea name="content" id="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Post</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Display posts or message if no posts -->
        <div id="posts-container">
            <?php if (empty($posts)): ?>
                <p>No posts yet :(</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($post['content']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">Posted by <?= htmlspecialchars($post['username']) ?> • <?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?></small>
                            </p>
                            <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-primary">View Post</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

