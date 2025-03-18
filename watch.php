<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$video_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Increment view count
$stmt = $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?");
$stmt->execute([$video_id]);

// Fetch video details with user info and like counts
$stmt = $pdo->prepare("SELECT v.*, u.username, u.profile_picture,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'like') as like_count,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'dislike') as dislike_count,
    (SELECT type FROM likes WHERE video_id = v.id AND user_id = ?) as user_reaction
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    WHERE v.id = ?");
$stmt->execute([$user_id, $video_id]);
$video = $stmt->fetch();

if (!$video) {
    header("Location: index.php");
    exit();
}

// Handle likes/dislikes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reaction'])) {
    $type = $_POST['reaction'];
    if ($type == 'like' || $type == 'dislike') {
        // Remove existing reaction if any
        $stmt = $pdo->prepare("DELETE FROM likes WHERE video_id = ? AND user_id = ?");
        $stmt->execute([$video_id, $user_id]);
        
        // Add new reaction if it's different from previous
        if ($video['user_reaction'] !== $type) {
            $stmt = $pdo->prepare("INSERT INTO likes (video_id, user_id, type) VALUES (?, ?, ?)");
            $stmt->execute([$video_id, $user_id, $type]);
        }
        header("Location: watch.php?id=" . $video_id);
        exit();
    }
}

// Handle comments
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (video_id, user_id, comment, posted_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$video_id, $user_id, $comment]);
    }
}

// Fetch comments
$stmt = $pdo->prepare("SELECT c.*, u.username, u.profile_picture 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.video_id = ? 
    ORDER BY c.posted_at DESC");
$stmt->execute([$video_id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?> - YourHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">YourHub</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="upload.php">Upload Video</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="video-container">
        <div class="video-player">
            <video controls autoplay>
                <source src="uploads/videos/<?php echo htmlspecialchars($video['filename']); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div class="video-details">
            <h1><?php echo htmlspecialchars($video['title']); ?></h1>
            <div class="video-meta">
                <div class="author-info">
                    <?php if ($video['profile_picture']): ?>
                        <img src="uploads/profile_pictures/<?php echo htmlspecialchars($video['profile_picture']); ?>" 
                             alt="<?php echo htmlspecialchars($video['username']); ?>" 
                             class="author-avatar">
                    <?php endif; ?>
                    <p class="video-author">Uploaded by <?php echo htmlspecialchars($video['username']); ?></p>
                </div>
                <div class="video-stats">
                    <span><i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?> views</span>
                    <form method="POST" action="" class="reaction-form">
                        <button type="submit" name="reaction" value="like" class="reaction-btn <?php echo $video['user_reaction'] === 'like' ? 'active' : ''; ?>">
                            <i class="fas fa-thumbs-up"></i> <?php echo number_format($video['like_count']); ?>
                        </button>
                        <button type="submit" name="reaction" value="dislike" class="reaction-btn <?php echo $video['user_reaction'] === 'dislike' ? 'active' : ''; ?>">
                            <i class="fas fa-thumbs-down"></i> <?php echo number_format($video['dislike_count']); ?>
                        </button>
                    </form>
                </div>
                <p class="video-date"><?php echo date('M d, Y', strtotime($video['upload_date'])); ?></p>
            </div>
            <div class="video-description">
                <?php echo nl2br(htmlspecialchars($video['description'])); ?>
            </div>
        </div>

        <div class="comments-section">
            <h3>Comments</h3>
            <form method="POST" action="" class="comment-form">
                <textarea name="comment" placeholder="Add a comment..." required></textarea>
                <button type="submit">Comment</button>
            </form>
            
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <?php if ($comment['profile_picture']): ?>
                                <img src="uploads/profile_pictures/<?php echo htmlspecialchars($comment['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($comment['username']); ?>" 
                                     class="comment-avatar">
                            <?php endif; ?>
                            <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <span class="comment-date"><?php echo date('M d, Y', strtotime($comment['posted_at'])); ?></span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const body = document.body;
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
            } else {
                body.setAttribute('data-theme', 'dark');
            }
        }

        // Check for saved theme preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.setAttribute('data-theme', 'dark');
        }
    </script>
</body>
</html> 