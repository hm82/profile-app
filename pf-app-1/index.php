<?php // DO NOT out any HTML above this line
//index.php
require_once "pdo.php"; // DB connection
require_once "util.php"; // common utils

// continue the session
session_start();

// Query for lisiting Profiles
$stmt = $pdo->query("SELECT profile_id, user_id, first_name, last_name, headline FROM Profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$row_count = count($rows);

// The view code (HTML goes below)
?>

<!DOCTYPE html>
<html>
<head>
<!-- Name in <title> -->
<title>Hasan Mujtaba - CRUD/JS Resume Registry</title>
<?php require_once "bootstrap.php"; // CSS, styling ?>
<!-- View  -->
</head>

<div class="container">
<h1>Hasan Mujtaba's Resume Registry</h1>

<?php
// flash messages for returns from add.php, delete.php, update.php & logout.php
flashMessages();

// If Username is Not Set/ Invalid for Session, show default Index.php, including
// "Please log in" & Profile Table
if ( ! isset($_SESSION['name']) || strlen($_SESSION['name']) < 1  ) {
  echo '<p><a href="login.php">'."Please log in".'</a></p>';
  // show all records as they exist in Profile table
  if ($row_count == 0){
      echo "";
  }
  else {
    // show table headings
    echo '<style> th, td { padding: 5px;}
          table, th {border: 2px solid black;} </style>';
    echo '<table border="2px">';
    echo '<tr>';
    echo '<th>Name</th>';
    echo '<th>Headline</th>';
    echo '<tr>';
    foreach ( $rows as $row ) {
        echo "<tr><td>";
        echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.
              htmlentities($row['first_name'])." ".htmlentities($row['last_name']).
              '</a>');
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td></tr>\n");
    }
    echo '</table>';
  }
} else {
// If logged-in, show links to Logout, Add New and Profiles table
  echo '<p><a href="logout.php"'.'>Logout</a> </p>';
  // show Only Records owned by logged-in user in the Profile table
  if ($row_count == 0){
      echo "";
  }
  else {
    // show table headings
    echo '<table border="2px">';
    echo '<tr>';
    echo '<th>Name</th>';
    echo '<th>Headline</th>';
    echo '<th>Action</th>';
    echo '<tr>';
    foreach ( $rows as $row ) {
      if ($row['user_id'] == $_SESSION['user_id'] )
      {
        echo "<tr><td>";
        echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.
              htmlentities($row['first_name'])." ".htmlentities($row['last_name']).
              '</a>');
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td><td>");
        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
        echo("</td></tr>\n");
      }
    }
    echo '</table>';
  }
  echo '<p><a href="add.php"'.'>Add New Entry</a> </p>';

}


?>

</div>
</html>
