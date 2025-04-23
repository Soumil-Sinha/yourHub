<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
//FInal Update done
// Fetch videos with user info and view counts
$stmt = $pdo->query("SELECT v.*, u.username, u.profile_picture, 
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'like') as like_count,
    (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'dislike') as dislike_count
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    ORDER BY v.upload_date DESC");
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourHub - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">YourHub</a>
        <div class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="upload.php">Upload Video</a>
            <a href="your_videos.php">Your Videos</a>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search videos..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="theme-toggle">
        <button onclick="toggleTheme()">
            <span class="theme-icon">🌙</span>
            <span class="theme-text">Dark Mode</span>
        </button>
    </div>

    <div class="container">
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <div class="video-card">
                    <a href="watch.php?id=<?php echo $video['id']; ?>">
                        <img src="uploads/thumbnails/<?php echo htmlspecialchars($video['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                             class="video-thumbnail">
                        <div class="video-info">
                            <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                            <div class="video-meta">
                                <div class="author-info">
                                    <?php if ($video['profile_picture']): ?>
                                        <img src="uploads/profile_pictures/<?php echo htmlspecialchars($video['profile_picture']); ?>" 
                                             alt="<?php echo htmlspecialchars($video['username']); ?>" 
                                             class="author-avatar">
                                    <?php endif; ?>
                                    <p class="video-author"><?php echo htmlspecialchars($video['username']); ?></p>
                                </div>
                                <div class="video-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?></span>
                                    <span><i class="fas fa-thumbs-up"></i> <?php echo number_format($video['like_count']); ?></span>
                                    <span><i class="fas fa-thumbs-down"></i> <?php echo number_format($video['dislike_count']); ?></span>
                                </div>
                                <p class="video-date"><?php echo date('M d, Y', strtotime($video['upload_date'])); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.querySelector('.theme-icon');
            const themeText = document.querySelector('.theme-text');
            
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Dark Mode';
            } else {
                body.setAttribute('data-theme', 'dark');
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Light Mode';
            }
        }

        // Check for saved theme preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.setAttribute('data-theme', 'dark');
            document.querySelector('.theme-icon').textContent = '☀️';
            document.querySelector('.theme-text').textContent = 'Light Mode';
        }
    </script>
</body>
</html> 