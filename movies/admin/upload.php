<?php include("../db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Trailer | ARNET Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
  <h2>ARNET Admin</h2>
  <ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="upload.php" class="active">Upload Trailer</a></li>
    <li><a href="trailer_list.php">Manage Trailers</a></li>
  </ul>
</div>

<div class="content">
  <h1>Upload New Trailer</h1>
  <form action="" method="post" enctype="multipart/form-data">
    <label>Title:</label>
    <input type="text" name="title" required>

    <label>Description:</label>
    <textarea name="description" rows="4"></textarea>

    <label>Video URL (YouTube / local path):</label>
    <input type="text" name="video_url" required>

    <label>Thumbnail Image:</label>
    <input type="file" name="thumbnail" accept="image/*" required>

    <button type="submit" name="upload">Upload Trailer</button>
  </form>

  <?php
  if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $video_url = $_POST['video_url'];

    $thumbName = $_FILES['thumbnail']['name'];
    $thumbTmp = $_FILES['thumbnail']['tmp_name'];
    $target = "../assets/trailer_thumbs/" . basename($thumbName);
    move_uploaded_file($thumbTmp, $target);

    $sql = "INSERT INTO trailers (title, description, video_url, thumbnail)
            VALUES ('$title', '$description', '$video_url', '$thumbName')";
    if ($conn->query($sql)) {
      echo "<p class='success'>✅ Trailer uploaded successfully!</p>";
    } else {
      echo "<p class='error'>❌ Error: " . $conn->error . "</p>";
    }
  }
  ?>
</div>
</body>
</html>
