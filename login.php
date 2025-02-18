<?php
require("db.php");
session_start();
$exist = 0; // Initialize the variable
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["user2"];
    $name = mysqli_real_escape_string($link, $name);

    $pass = $_POST["pass2"];
    $pass = mysqli_real_escape_string($link, $pass);
    $pass = hash("sha1", $pass, false);

    // Check if the user already exists
    $sql = "SELECT * FROM login WHERE name = ? AND password = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ss", $name, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $exist = 1;
        $sql = "SELECT mobile FROM login WHERE name = ? AND password = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ss", $name, $pass);
        $stmt->execute();
        $result = $stmt->get_result();  // Get the result set

        if ($row = $result->fetch_assoc()) {  // Fetch the first row
           $mob = $row['mobile'];  // Extract the mobile number
       } else {
           $mob = null;  // No match found
       }

        header("Location: access.php?name=" . urlencode($name) . "&mob=" . urlencode($mob));
        exit();
        

    } else {
        $exist = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="spark.css">
    <title>Spark</title>
</head>
<body>
    <div class="cont">
        <h3>Spark</h3>

        <div id="login">
            <form action="login.php" method="post">
                <input type="text" name="user2" id="user2" placeholder="username" required>
                <input type="password" name="pass2" id="pass2" placeholder="password" required>
                <button type="submit" name="form" value="login">Login</button>
                <p>Don't have an account? <span onclick="form();">Create</span></p>
                <?php
                if ($exist == 1) {
                    echo '<p id="green">Welcome back</p>';
                    header("Location: chat.php");
                   exit();
                } else if ($exist == 2) {
                    echo '<p id="red">Invalid userid or password</p>';
                }
                ?>
            </form>
        </div>
    </div>
    <Script>
    form = () => {
        window.location.href="index.php";
    }
</Script>
</body>
</html>
