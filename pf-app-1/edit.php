fn<?php // DO NOT out any HTML above this line
//index.php
require_once "pdo.php"; // DB connection
require_once "util.php"; // common utils

// continue the session
session_start();

// End Script / Die if username is NOT set
if ( ! isset($_SESSION['name']) || strlen($_SESSION['name']) < 1  ) {
    die("ACCESS DENIED");
}

// If the user cancelled the Edit request, redirect back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

// Handling Invalid/ Blank Profile ID
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

// POST Handling for Edit/Update - UPDATE PROFILE IN the DB based on user inputs retrieved from the POST response
// Apply validation and suitabke redirects on POST request params
if ( isset($_POST['update']) && isset($_POST['first_name']) &&
     isset($_POST['last_name']) && isset($_POST['email']) &&
     isset($_POST['headline']) && isset($_POST['summary'])) {

     // apply Profile validations, All Fields Required, Emails must have @
     $msg = validateProfile();
     if ( is_string($msg) ){
       $_SESSION['error'] = $msg;
       header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
       return;
     }

     // validate Position entries if present
     $msg = validatePos();
     if ( is_string($msg) ){
       $_SESSION['error'] = $msg;
       header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
       return;
     }

     $sql = "UPDATE Profile
             SET first_name = :fn,
                 last_name = :ln,
                 email = :em,
                 headline = :hl,
                 summary = :sum
             WHERE profile_id = :pid
             AND user_id=:uid";

     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(
          ':fn' => $_POST['first_name'],
          ':ln' => $_POST['last_name'],
          ':em' => $_POST['email'],
          ':hl' => $_POST['headline'],
          ':sum' => $_POST['summary'],
          ':pid' => $_REQUEST['profile_id'],
          ':uid' => $_SESSION['user_id'] ) );

     // Clear out the old Position Entries
     $sql = "DELETE from Position WHERE profile_id=:pid";
     $stmt = $pdo->prepare($sql);
     $stmt->execute(array(
       ':pid' => $_REQUEST['profile_id'] ) );

     // Insert the updated position entries
     // Insert the position entries in DB
     $rank = 1;
     for ($i = 1; $i <=9; $i++){
       if ( ! is_string($_POST['year'.$i] ) ) continue;
       if ( ! is_string($_POST['desc'.$i] ) ) continue;
       $year = $_POST['year'.$i];
       $desc = $_POST['desc'.$i];

       $stmt = $pdo->prepare('INSERT INTO Position
               (profile_id, rank, year, description)
               VALUES (:pid, :rank, :year, :desc)');

       $stmt->execute(array(
           ':pid' => $_REQUEST['profile_id'],
           ':rank' => $rank,
           ':year' => $year,
           ':desc' => $desc)
         );
       $rank ++;
     }

     $_SESSION['success'] = "Profile updated";
     header("Location: index.php");
     return;
}

// Query record to populate the edit form based on profile_id
$stmt = $pdo->prepare("SELECT profile_id, user_id, first_name, last_name,
        email, headline, summary FROM Profile
        where profile_id = :pid");
$stmt->execute( array(":pid" => $_REQUEST['profile_id'] ) );
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
// retrieve positions for given profile to load on dynamic position-form
$positions = loadPos($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>
<head>
<title>Hasan Mujtaba's Profile Editor</title>
<?php require_once "bootstrap.php"; ?>
</head>

<body>
<div class="container">
<h1>Editing Profile for <?= htmlentities($_SESSION['name']); ?></h1>

<?php
// Flash error message if form validations fail, 1x only
flashMessages();

// set values to populate the Edit form
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$hl = htmlentities($row['headline']);
$sum = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

// echo ('<p style="color: red;">'."Debug: Make ".$row['make']." Model ".$mod." Year ".$yy." Mileage ".$mil." ."."</p>\n");

?>

<form method="post">
  <p>First Name: <input type="text" name="first_name" value ="<?= $fn ?>" size="60"/></p>
  <p>Last Name: <input type="text" name="last_name" value ="<?= $ln ?>" size="60"/></p>
  <p>Email: <input type="text" name="email" value ="<?= $em ?>" /></p>
  <p>Headline: <input type="text" name="headline" value ="<?= $hl ?>" /></p>
  <p>Summary:<br/><textarea name="summary", rows="8", cols="80"><?= $sum ?></textarea></p>
  <input type="hidden" name="profile_id" value="<?= $profile_id ?>">

<?php
// populate dynamic position form
$pos = 0;
echo ('<p>Position:<input type="submit" id="addPos" value="+">'. "\n");
echo ('<div id="position_fields">'. "\n");
foreach ($positions as $position) {
  $pos ++;
  echo ('<div id=position'.$pos.">"."\n");
  echo ('<p>Year: <input type="text" name="year'.$pos.'"');
  echo (' value="'.$position['year'].'" />'."\n");
  echo ('<input type="button" value="-"');
  echo ('onclick="$(\'#position'.$pos.'\').remove(); return false;">'."\n");
  echo ("</p>\n");
  echo ('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
  echo (htmlentities($position['description'])."\n");
  echo ("\n </textarea> \n</div>\n");
}
echo ("</div></p>\n");
?>

<p>
  <input type="submit" name="update" value="Save">
  <input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script>
countPos = <?= $pos ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
            onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
});
</script>

</div>
</body>
</html>
