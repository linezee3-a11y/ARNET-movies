<?php
$conn = new mysqli("localhost","root","","arnet_db");
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM trailers WHERE id=$id");
$row = $result->fetch_assoc();
unlink("../assets/uploads/".$row['video']);
unlink("../assets/trailer_thumbs/".$row['thumbnail']);
$conn->query("DELETE FROM trailers WHERE id=$id");
header("Location: admin.php");
?>
