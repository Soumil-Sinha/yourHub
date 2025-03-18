<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$videos = [];

if ($search_query) {
    // Search in video titles and descriptions
    $stmt = $pdo->prepare("SELECT v.*, u.username, u.profile_picture, 
        (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'like') as like_count,
        (SELECT COUNT(*) FROM likes WHERE video_id = v.id AND type = 'dislike') as dislike_count
        FROM videos v 
        JOIN users u ON v.user_id = u.id 
        WHERE v.title LIKE ? OR v.description LIKE ?
        ORDER BY v.upload_date DESC");
    
    $search_pattern = "%{$search_query}%";
    $stmt->execute([$search_pattern, $search_pattern]);
    $videos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - YourHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">YourHub</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="upload.php">Upload Video</a>
            <a href="your_videos.php">Your Videos</a>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search videos..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Search Results</h1>
        
        <?php if ($search_query): ?>
            <p>Showing results for "<?php echo htmlspecialchars($search_query); ?>"</p>
            
            <?php if (empty($videos)): ?>
                <div class="no-videos">
                    <p>No videos found matching your search.</p>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        <?php else: ?>
            <div class="no-videos">
                <p>Please enter a search term to find videos.</p>
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