<?php include("../db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Trailers | ARNET Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
  <h2>ARNET Admin</h2>
  <ul>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="upload.php">Upload Trailer</a></li>
    <li><a href="trailer_list.php" class="active">Manage Trailers</a></li>
  </ul>
</div>

<div class="content">
  <h1>All Uploaded Trailers</h1>
  <table>
    <tr>
      <th>Thumbnail</th>
      <th>Title</th>
      <th>Uploaded</th>
      <th>Action</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM trailers ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
      echo "<tr>
        <td><img src='../assets/trailer_thumbs/{$row['thumbnail']}' width='100'></td>
        <td>{$row['title']}</td>
        <td>{$row['uploaded_at']}</td>
        <td><a href='?delete={$row['id']}' class='delete'>Delete</a></td>
      </tr>";
    }

    if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      $conn->query("DELETE FROM trailers WHERE id=$id");
      header("Location: trailer_list.php");
    }
    ?>
  </table>
</div>
</body>
</html>
