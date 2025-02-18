<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
session_start();

// Check if name and mob are passed via URL and set them in the session
if (isset($_GET['name']) && isset($_GET['mobile'])) {
    $_SESSION['name'] = $_GET['name'];
    $_SESSION['mobile'] = $_GET['mobile'];
    $_SESSION['token'] = $_GET['token'];
}

if (!isset($_SESSION['name']) || !isset($_SESSION['mobile']) || !isset($_SESSION['token'])) {
    die("Unauthorized access!");
}

// Validate the token
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check token sent with form submission (POST)
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        die("Invalid token!");
    }
} else {
    // Check token in GET request (page load)
    if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['token']) {
        die("Invalid token!");
    }
}

require("db.php");

// User is authenticated
$user = $_SESSION['name'];
$mobile = $_SESSION['mobile'];

// Fetch received messages (Inbox)
$query = "SELECT m.receiver, m.sender, m.message, m.time, l_sender.name AS sender_name, l_receiver.name AS receiver_name FROM message m JOIN login l_sender ON m.sender = l_sender.mobile JOIN login l_receiver ON m.receiver = l_receiver.mobile WHERE m.receiver = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

$gotmessages = [];
while ($row = $result->fetch_assoc()) {
    $gotmessages[] = $row;
}

// Fetch sent messages
$query = "SELECT m.receiver, m.sender, m.message, m.time, l_sender.name AS sender_name, l_receiver.name AS receiver_name FROM message m JOIN login l_sender ON m.sender = l_sender.mobile JOIN login l_receiver ON m.receiver = l_receiver.mobile WHERE m.sender = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

$sentmessages = [];
while ($row = $result->fetch_assoc()) {
    $sentmessages[] = $row;
}

// Encode the messages as JSON for use in JS
$inbox = json_encode($gotmessages);
$sent = json_encode($sentmessages);


// send new message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $rec = $_POST["recipient"];
    $rec = mysqli_real_escape_string($link, $rec);

    $mes = $_POST["message"];
    $mes = mysqli_real_escape_string($link, $mes);

    $mobile = trim($mobile);
    $rec = trim($rec);

    $query_check_sender = "SELECT mobile FROM login WHERE mobile = ?";
    $stmt_check_sender = $link->prepare($query_check_sender);
    $stmt_check_sender->bind_param("s", $rec);
    $stmt_check_sender->execute();
    $result_check_sender = $stmt_check_sender->get_result();

    // Check if sender exists in the login table
if ($result_check_sender->num_rows === 0) {
    echo "<script>alert('Error: Invalid Recipient');</script>";
} else {
    // Insert the message
    $sql_insert = "INSERT INTO message(message, sender, receiver) VALUES ('$mes', '$mobile', '$rec')";
    if ($link->query($sql_insert) === TRUE) {
        echo "<script>console.log('Spark sent'); reload();</script>";
    } else {
        echo "<script>console.log('Error: Something went wrong');</script>";
    }
}

header("Location: spark.php?name=" . urlencode($_SESSION['name']) . "&mobile=" . urlencode($_SESSION['mobile']) . "&token=" . $_SESSION['token']);
exit();
  

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spark</title>
    <link rel="stylesheet" href="chat.css">
</head>
<body>
    <div class="sidebar">
       <?php echo "<h2>Welcome $user</h2>" ?>
        <button onclick="showInbox()">Inbox</button>
        <button onclick="showSent()">Sent</button>
        <div id="messageList"></div>
    </div>
    <div class="content">
        <div class="messages" id="messageView">
            <h3>Select a message to view</h3>
        </div>
        <form action="spark.php" method="post" class="compose">
            <input type="text" id="recipient" name="recipient" placeholder="Recipient Mobile Number" style="width: 100%; padding: 5px;" required minlength="10" maxlength="10">
            <textarea id="message" name="message" placeholder="Write your message..." style="width: 100%; padding: 5px; height: 100px;" required></textarea>
            <input type="hidden" name="token" id="token" value="<?php echo $_SESSION['token']; ?>">
           <button type="submit">Send</button>
        </form>
    </div>
    
    <script>
        // Get the inbox and sent messages from PHP

const inboxMessages = <?php echo $inbox ?>;
const sentMessages = <?php echo $sent ?>;

function reload(){
    const inboxMessages = <?php echo $inbox ?>;
    const sentMessages = <?php echo $sent ?>;
}
// Function to load inbox messages
function loadInboxMessages() {
    const messageList = document.getElementById('messageList');
    messageList.innerHTML = ''; // Clear previous messages
    inboxMessages.forEach((message) => {
        const div = document.createElement('div');
        div.className = 'message-item';
        div.innerHTML = `<strong>${message.time}</strong>: ${message.sender_name}`;
        div.onclick = () => viewMessage(message);  // View message on click
        messageList.appendChild(div);
    });
}

// Function to load sent messages
function loadSentMessages() {
    const messageList = document.getElementById('messageList');
    messageList.innerHTML = ''; // Clear previous messages
    sentMessages.forEach((message) => {
        const div = document.createElement('div');
        div.className = 'message-item';
        div.innerHTML = `<strong>${message.time}</strong>: ${message.receiver_name}`;
        div.onclick = () => viewMessage(message);  // View message on click
        messageList.appendChild(div);
    });
}

// Show the inbox messages
function showInbox() {
    loadInboxMessages();
}

// Show the sent messages
function showSent() {
    loadSentMessages();
}

// View selected message
function viewMessage(message) {
    const messageView = document.getElementById('messageView');
    messageView.innerHTML = `
        <h3>Message Details</h3>
        <p><strong>From:</strong> ${message.sender_name}</p>
        <p><strong>To:</strong> ${message.receiver_name}</p>
        <p><strong>Message:</strong> ${message.message}</p>
        <p><strong>Time:</strong> ${message.time}</p>
    `;
}



// Initially show the inbox
showInbox();

    </script>
</body>
</html>
