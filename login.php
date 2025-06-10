<?php
include 'includes/auth.php';

if(check_login()) {
    header('Location: index.php');
    exit();
}

if(!empty($_POST["username"]) && !empty($_POST["password"]) )
{
    $conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']) or die(mysqli_error($conn));

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    $query = "SELECT * FROM utenti WHERE BINARY username = '".$username."'";

    $res = mysqli_query($conn, $query) or die(mysqli_error($conn));

    if(mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        if(password_verify($_POST['password'], $row['password'])) {

            $user_id = $row['id'];
            $update_query = "UPDATE utenti SET ultimo_accesso = NOW() WHERE id = $user_id";
            mysqli_query($conn, $update_query);

            $_SESSION['UTENTE'] = $row['username'];
            $_SESSION['ID'] = $row['id'];
            header("Location: index.php");
            mysqli_free_result($res);
            mysqli_close($conn);
            exit();
        } else {
            $error = "Username e/o password errati.";
        }
    }
    else {
        $error = "Username e/o password errati.";
    }
}
else if (isset($_POST["username"]) || isset($_POST["password"])) {
    $error = "Inserisci username e password.";
}












?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reddit replica</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <header>
    <div class="header-container-login">
      <div class="header-sinistra">
        <span class="icona-reddit">
          <img src="assets/images/reddit-logo.png" alt="Reddit">
        </span>
        <h3 class="titolo-reddit">
          <a href="mhw2.html">reddit</a>
        </h3>
      </div>
    </div>
  </header>


  <div class="container login-container">
   <div class="login-box">
     <h2>Accedi</h2>
     <p class="policy">Continuando, accetti i nostri Termini di Utilizzo e <br> dichiari di aver compreso la nostra Privacy Policy.</p>
     <?php

        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
                
     ?>
    <form name="login" method="POST">
      <div class="form-group">
        <input type="text" id="username" name="username" placeholder="Username" required <?php if(isset($_POST["username"])) echo "value=\"" . htmlspecialchars($_POST["username"]) . "\""; ?>>
      </div>
      <div class="form-group">
        <input type="password" id="password" name="password" placeholder="Password" required>
      </div>
      <div class="form-footer">
        <p>Non hai un account? <a href="register.php">Registrati</a></p>
      </div>
      <button type="submit" class="login-register-button">Accedi</button>
    </form>
  </div>
</div>

</body>
</html>