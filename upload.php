<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    
    // Handle video upload
    $filename = '';
    $thumbnail = '';
    
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $allowed_video_exts = ['mp4', 'webm', 'ogg'];
        
        if (in_array($video_ext, $allowed_video_exts)) {
            $filename = uniqid() . '.' . $video_ext;
            $video_path = 'uploads/videos/' . $filename;
            
            if (!file_exists('uploads/videos')) {
                mkdir('uploads/videos', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                // Handle thumbnail upload
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                    $thumbnail_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                    $allowed_thumbnail_exts = ['jpg', 'jpeg', 'png'];
                    
                    if (in_array($thumbnail_ext, $allowed_thumbnail_exts)) {
                        $thumbnail = uniqid() . '.' . $thumbnail_ext;
                        $thumbnail_path = 'uploads/thumbnails/' . $thumbnail;
                        
                        if (!file_exists('uploads/thumbnails')) {
                            mkdir('uploads/thumbnails', 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
                            // Insert into database with views = 0
                            $stmt = $pdo->prepare("INSERT INTO videos (title, description, filename, thumbnail, user_id, views, upload_date) VALUES (?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)");
                            if ($stmt->execute([$title, $description, $filename, $thumbnail, $user_id])) {
                                header("Location: index.php");
                                exit();
                            }
                        }
                    }
                }
            }
        }
    }
    
    $error = "Error uploading video. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourHub - Upload Video</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">YourHub</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="upload-form">
            <h2>Upload Video</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Video Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="video">Video File (MP4, WebM, OGG)</label>
                    <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/ogg" required>
                </div>
                <div class="form-group">
                    <label for="thumbnail">Thumbnail (JPG, PNG)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png" required>
                </div>
                <button type="submit">Upload Video</button>
            </form>
        </div>
    </div>
</body>
</html> 