<?php // DO NOT out any HTML above this line
//view.php
require_once "pdo.php"; // DB connection
require_once "util.php"; // common utils

// No Session Required here; this page is Public

// Query for Showing Profile details
$stmt = $pdo->prepare("SELECT profile_id, user_id, first_name, last_name,
        email, headline, summary FROM Profile
        where profile_id = :pid");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

// retrieve positions for selected profile
$positions = loadPos($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>
<head>
<title>Hasan Mujtaba's Resume Registry</title>
<?php require_once "bootstrap.php"; ?>
</head>

<body>
<div class="container">
<!-- Display requested profile -->
<h1>Profile Information</h1>
<?php
    echo '<p>'."First Name: ".htmlentities($row['first_name']).'</p>';
    echo '<p>'."Last Name: ".htmlentities($row['last_name']).'</p>';
    echo '<p>'."Email: ".htmlentities($row['email']).'</p>';
    echo '<p>'."Headline: <br/>".htmlentities($row['headline']).'</p>';
    echo '<p>'."Summary: <br/>".htmlentities($row['summary']).'</p>';

    // populate dynamic position form
    $pos = 0;
    echo ("<p>Position\n");
    echo ("<ul>\n");
    foreach ($positions as $position) {
      $pos ++;
      echo ('<li>'.$position['year'].": ".$position['description'].'</li>'."\n");
    }
    echo ("</ul>\n");
?>
<p> <a href="index.php">Done</a> </p>

</div>
</body>
</html>
