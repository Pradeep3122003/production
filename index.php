<?php
require("db.php");
$exist = 0;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        $name = $_POST["user"];
        $name = mysqli_real_escape_string($link, $name);

        $mob = $_POST["mob"];
        $mob = mysqli_real_escape_string($link, $mob);

        $pass = $_POST["pass"];
        $pass = mysqli_real_escape_string($link, $pass);
        $pass = hash("sha1", $pass, false);

        // Check if the user already exists
        $sql = "SELECT * FROM login WHERE mobile = '$mob'";

        $result = $link->query($sql);

        if ($result->num_rows > 0) {
            $exist = 1;
        } else {
            // Insert new user into the database
            $sql_insert = "INSERT INTO login VALUES ('$name', '$mob', '$pass')";
            if ($link->query($sql_insert) === TRUE) {
                $exist = 2;
                header("Location: sec.php?name=John&mobile=1234567890");

            } else {
                echo "<p>Error: " . $link->error . "</p>";
            }
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
        <div id="signup">
            <form action="index.php" method="post">
                <input type="text" name="user" id="user" placeholder="username" required minlength="4">
                <input type="text" name="mob" id="mob" placeholder="mobile" required maxlength="10" minlength="10">
                <input type="password" name="pass" id="pass" placeholder="password" required minlength="4">
                <button type="submit" name="form" value="submit">Register</button>
                <p>Already have an account? <span onclick="form();">Login</span></p>
                <?php
                if ($exist == 1) {
                    echo '<p id="red">User already exists</p>';
                } else if ($exist == 2) {
                    echo '<p id="green">Registration successful! You can now log in.</p>';
                }
                $exist = 0;
                ?>
            </form>
        </div>

    </div>
    <Script>
    form = () => {
        window.location.href="login.php";
    }
</Script>
</body>
</html>
