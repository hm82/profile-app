<?php // DO NOT out any HTML above this line
//index.php
require_once "pdo.php"; // DB connection
require_once "util.php"; // common utils

session_start();
unset($_SESSION['name']); // log the user out
unset($_SESSION['user_id']); // log the user out

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

// HM: Updated stored hash for new password
$salt = 'XyZzy12*_';
// $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
  // Apply validations on Email (who) and Password (pass)
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION['error'] = "User name and password are required";
        header("Location: login.php");
        return;
    }
    elseif (! (mb_strpos($_POST['email'], '@'))){
        // validate that the login name contains an at-sign (@)
        $_SESSION['error'] = "User name must have an at-sign (@)";
        header("Location: login.php");
        return;
    }
    else {
        $check = hash('md5', $salt.$_POST['pass']);
        $sql = "SELECT user_id, name
                FROM users
                WHERE email = :em AND password =:pw";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':em'=> $_POST['email'], ':pw'=>$check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // row found => passwords matches
        if ( $row !== false ) {
            // When Login suceeds, redirect the browser to autos.php with information in $SESSION vars
            error_log("Login success ".$_POST['email']);
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['success'] = "Logged in.";
            header("Location: index.php");
            return;
        }
        else // password doesn't match
        {
            error_log("Login fail ".$_POST['email']." $check");
            $_SESSION['error'] = "Incorrect password.";
            header("Location: login.php");
            return;
        }
    }
}

// Fall through into the View
?>
<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Hasan Mujtaba's Login Page</title>
</head>
<body>


<script>
function doValidate() {
    console.log('Validating...');
    try {
        email = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+email+" pw="+pw);
        if (email == null || email == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( email.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>

<div class="container">
<h1>Please log in</h1>

<?php

if ( isset($_SESSION['error']) ) {
    echo('<p style="color:red">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}

?>
<form method="POST">
<label for="email">User Name</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is the three character name of this programming language
(all lower case) followed by 123. -->
</p>
</div>

</body>
