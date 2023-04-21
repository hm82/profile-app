<?php // DO NOT out any HTML above this line
//index.php
require_once "pdo.php"; // DB connection
require_once "util.php"; // common utils

// continue the session
session_start();

// End Script / Die if username is NOT set
if ( ! isset($_SESSION['name']) || strlen($_SESSION['name']) < 1  ) {
    die("ACCESS DENIED");
}

// If the user cancleld the request, redirect back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

// POST Handling for Edit/Update - Remove Profile from the DB based on user inputs retrieved from the POST response
// Apply validation and suitabke redirects on POST request params
if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {

    // No Validations Required

    // Delete the requested automobile data
    $sql = "DELETE FROM Profile WHERE profile_id = :pid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':pid' => $_POST['profile_id']));
    $_SESSION['success'] = "Record Deleted";
    header("Location: index.php");
    return;
}

// Handling Invalid/ Blank Autos_Id
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

// Query record to populate the edit form based on autos_id
$stmt = $pdo->prepare("SELECT profile_id, first_name, last_name FROM Profile where profile_id = :pid");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Hasan Mujtaba's Resume Registry</title>
<?php require_once "bootstrap.php"; ?>
</head>

<body>
<div class="container">
<h1>Deleting Profile for <?= htmlentities($_SESSION['name']); ?></h1>

<?php
// No Code
?>

<p>First Name: <?= htmlentities($row['first_name']) ?></p>
<p>Last Name: <?= htmlentities($row['last_name']) ?></p>

<form method="post">
  <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
  <input type="submit" name="delete" value="Delete">
  <input type="submit" name="cancel" value="Cancel">
</form>

</div>
</body>
</html>
