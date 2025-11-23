<?php
// admin/db.php
$host = "localhost";
$user = "root";
$pass = "";
$db = "arnet_db";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Handle upload
if(isset($_POST['upload'])){
    $name = $_POST['name'];
    $hours_ago = $_POST['hours'];
    
    // Upload thumbnail
    $thumb = $_FILES['thumbnail']['name'];
    $tmp_thumb = $_FILES['thumbnail']['tmp_name'];
    move_uploaded_file($tmp_thumb, "../assets/trailer_thumbs/".$thumb);

    // Upload video
    $video = $_FILES['video']['name'];
    $tmp_video = $_FILES['video']['tmp_name'];
    move_uploaded_file($tmp_video, "../assets/uploads/".$video);

    $sql = "INSERT INTO trailers (name, thumbnail, video, hours_ago) VALUES ('$name','$thumb','$video','$hours_ago')";
    $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<h1>Admin Dashboard</h1>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Trailer Name" required><br>
    <input type="number" name="hours" placeholder="Hours Ago" required><br>
    <input type="file" name="thumbnail" required><br>
    <input type="file" name="video" required><br>
    <button type="submit" name="upload">Upload Trailer</button>
</form>

<h2>Uploaded Trailers</h2>
<?php
$result = $conn->query("SELECT * FROM trailers ORDER BY id DESC");
while($row = $result->fetch_assoc()){
    echo "<div>
        <img src='../assets/trailer_thumbs/".$row['thumbnail']."' width='100'>
        ".$row['name']." - ".$row['hours_ago']." hours ago
        <a href='delete.php?id=".$row['id']."'>Delete</a>
    </div>";
}
?>
</body>
</html>
