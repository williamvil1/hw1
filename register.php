<?php
include 'includes/auth.php';

if(check_login()) {
    header('Location: index.php');
    exit();
}

// Array per gli errori
$errors = array();

if (!empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["confirm_password"]) && !empty($_POST["email"]))
{
  $conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']) or die(mysqli_error($conn));

  # controllo username
  $usernameRegex = '/^(?!_)(?!.*__)[a-zA-Z0-9_]{5,15}(?<!_)$/';
  if(!preg_match($usernameRegex, $_POST['username'])) {
    $errors[] = "Username non valido";
  } else {
    $username = mysqli_real_escape_string($conn,$_POST['username']);

    $query = "SELECT username FROM utenti WHERE username = '".$username."'";

    $res = mysqli_query($conn,$query);
    if(mysqli_num_rows($res) > 0) {
      $errors[] = "Username già in uso";
    }
  }


  # Avatar
  $cartella_avatar = 'assets/images/avatar-utenti/';
  $avatar_disponibili = glob($cartella_avatar . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
  $percorso_avatar_scelto = 'assets/images/avatar.png';
  if(!empty($avatar_disponibili)) {
    $random_key = array_rand($avatar_disponibili);
    $percorso_avatar_scelto = $avatar_disponibili[$random_key];
  } else {
    $errors[] = "Nessun avatar disponibile";
  }

# controllo se la password contiene almeno un numero una lettera maiuscola un simbolo speciale e almeno 8 caratteri e anche confirm password




#controllo lettera maiuscola, numero caratteri, simbolo speciale e numero
$passwordRegex = '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,30}$/';
  if (!preg_match($passwordRegex, $_POST['password'])) {
  $errors[] = "La password deve contenere almeno 8 caratteri, una lettera maiuscola, un numero e un simbolo speciale";
}

  #controllo confirm_password

  if($_POST['password'] !== $_POST['confirm_password']){
    $errors[] = "Le password non coincidono";
  }

  #controllo email

  $email_originale = $_POST['email'];
  $email_normalizzata = strtolower($email_originale);

  if(!filter_var($email_normalizzata, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Indirizzo email non valido";
  } else {
    $email = mysqli_real_escape_string($conn,$email_normalizzata);
    $query = "SELECT email FROM utenti WHERE email = '".$email."'";
    $res = mysqli_query($conn,$query);
    if(mysqli_num_rows($res) > 0) {
      $errors[] = "Indirizzo email già in uso";
    }
  }


  if(count($errors) == 0) {
    $password_originale = $_POST['password'];
    $password_hashed = password_hash($password_originale, PASSWORD_BCRYPT);

    $query = "INSERT INTO utenti(username,password,email,avatar) VALUES ('".$username."','".$password_hashed."','".$email."','".$percorso_avatar_scelto."')";

    if(mysqli_query($conn,$query)) {
      $_SESSION['UTENTE'] = $username;
      $_SESSION['ID'] = mysqli_insert_id($conn);
      header("Location: index.php");
      mysqli_close($conn);
      exit();
    } else {
      $errors[] = "Errore con la connessione al database";
    }
 }
 mysqli_close($conn);
}




















?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrati - Reddit replica</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="assets/js/register.js" defer></script>
</head>
<body class="login-page">
  <header>
    <div class="header-container-login">
      <div class="header-sinistra">
        <span class="icona-reddit">
          <img src="assets/images/reddit-logo.png" alt="Reddit">
        </span>
        <h3 class="titolo-reddit">
          <a href="index.php">reddit</a>
        </h3>
      </div>
    </div>
  </header>

<div class="container login-container">
  <div class="register-box">
    <h2>Registrati</h2>
    <p class="policy">Continuando, accetti i nostri Termini di Utilizzo e <br> dichiari di aver compreso la nostra Privacy Policy.</p>
    <form id="register-form" name="register" method="POST">
      <div class="form-group">
        <input type="text" id="username" name="username" placeholder="Username" required <?php if(isset($_POST["username"])) echo "value=\"" . htmlspecialchars($_POST["username"]) . "\""; ?>>
        <small class="form-hint">L'username deve contenere minimo 5 massimo 15 caratteri (solo lettere, numeri e underscore)</small>
        <span class="feedback-message"></span>
      </div>
      <div class="form-group">
        <input type="password" id="password" name="password" placeholder="Password" required>
        <span class="toggle-password-visibility" data-target="password">👁️</span>
        <small class="form-hint">La password deve avere almeno: 8 caratteri, una lettera maiuscola, un carattere speciale, un numero</small>
      </div>
      <div class="form-group">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Conferma Password" required>
        <span class="toggle-password-visibility" data-target="confirm_password">👁️</span>
      </div>
      <div class="form-group">
        <input type="email" id="email" name="email" placeholder="Email" required <?php if(isset($_POST["email"])) echo "value=\"" . htmlspecialchars($_POST["email"]) . "\""; ?>>
        <span class="feedback-message"></span>
      </div>
      <div class="form-footer">
        <p>Hai già un account? <a href="login.php">Accedi</a></p>
      </div>
      <?php if(!empty($errors)): ?>
      <div class="error-container-bottom">

      <?php foreach($errors as $error): ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <button type="submit" class="login-register-button">Registrati</button>
    </form>
  </div>
</div>

</body>
</html>