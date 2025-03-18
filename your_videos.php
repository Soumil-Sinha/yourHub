<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle video deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_video'])) {
    $video_id = $_POST['video_id'];
    
    // First get the video details to delete files
    $stmt = $pdo->prepare("SELECT filename, thumbnail FROM videos WHERE id = ? AND user_id = ?");
    $stmt->execute([$video_id, $user_id]);
    $video = $stmt->fetch();
    
    if ($video) {
        // Delete video file
        if (file_exists('uploads/videos/' . $video['filename'])) {
            unlink('uploads/videos/' . $video['filename']);
        }
        
        // Delete thumbnail
        if (file_exists('uploads/thumbnails/' . $video['thumbnail'])) {
            unlink('uploads/thumbnails/' . $video['thumbnail']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
        $stmt->execute([$video_id, $user_id]);
        
        // Redirect to refresh the page
        header("Location: your_videos.php");
        exit();
    }
}

// Fetch user's videos
$stmt = $pdo->prepare("SELECT v.*, 
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'like') as like_count,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'dislike') as dislike_count,
    (SELECT COUNT(*) FROM comments WHERE video_id = v.id) as comment_count
    FROM videos v 
    WHERE v.user_id = ? 
    ORDER BY v.upload_date DESC");
$stmt->execute([$user_id]);
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Videos - YourHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">YourHub</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="upload.php">Upload Video</a>
            <a href="your_videos.php" class="active">Your Videos</a>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search videos..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Your Videos</h1>
        
        <?php if (empty($videos)): ?>
            <div class="no-videos">
                <p>You haven't uploaded any videos yet.</p>
                <a href="upload.php" class="btn">Upload Your First Video</a>
            </div>
        <?php else: ?>
            <div class="video-grid">
                <?php foreach ($videos as $video): ?>
                    <div class="video-card">
                        <div class="video-thumbnail-container">
                            <img src="uploads/thumbnails/<?php echo htmlspecialchars($video['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                 class="video-thumbnail">
                            <div class="video-overlay">
                                <a href="watch.php?id=<?php echo $video['id']; ?>" class="play-btn">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        </div>
                        <div class="video-info">
                            <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                            <div class="video-stats">
                                <span><i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?></span>
                                <span><i class="fas fa-thumbs-up"></i> <?php echo number_format($video['like_count']); ?></span>
                                <span><i class="fas fa-thumbs-down"></i> <?php echo number_format($video['dislike_count']); ?></span>
                                <span><i class="fas fa-comments"></i> <?php echo number_format($video['comment_count']); ?></span>
                            </div>
                            <p class="video-date">Uploaded on <?php echo date('M d, Y', strtotime($video['upload_date'])); ?></p>
                            <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="delete_video" class="delete-btn">
                                    <i class="fas fa-trash"></i> Delete Video
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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