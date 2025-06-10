<?php
require_once 'includes/auth.php';

if(!check_login()) {
    header('Location: login.php');
    exit();
}

$username_session = $_SESSION['UTENTE'];
$user_id_session = $_SESSION['ID'];

$conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']);
$avatar_profile = 'assets/images/avatar.png';
$username_profile = $username_session;

if ($conn) {
    $safe_user_id_session = intval($user_id_session);
    $query_avatar = "SELECT avatar, username FROM utenti WHERE id = $safe_user_id_session";
    $result_avatar = mysqli_query($conn, $query_avatar);

    if (mysqli_num_rows($result_avatar) > 0) {
        $row_avatar = mysqli_fetch_assoc($result_avatar);
        $avatar_profile = $row_avatar['avatar'];
        $username_profile = $row_avatar['username'];
        mysqli_free_result($result_avatar);
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Mie Interazioni - <?php echo htmlspecialchars($username_profile); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="assets/js/main.js" defer></script>
    <script src="assets/js/interazioni_post.js" defer></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-sinistra">
                <span class="icona-reddit"><img src="assets/images/reddit-logo.png" alt="Reddit"></span>
                <h3 class="titolo-reddit"><a href="index.php">reddit</a></h3>
            </div>
            <span class="pulsanti">
                <a href="https://github.com/williamvil1" class="pulsante-github">Git Hub</a>
                <button id="tema-toggle" class="pulsante-tema" aria-label="Cambia tema"><span class="icona-tema">☀️</span></button>
                <?php if(isset($_SESSION['UTENTE'])): ?>
                <div class="avatar-container">
                    <img src="<?php echo htmlspecialchars($avatar_profile); ?>" alt="Avatar utente" class="avatar-utente">
                    <div class="menu-utente nascosto">
                        <div class="menu-utente-header">
                            <img src="<?php echo htmlspecialchars($avatar_profile); ?>" alt="Avatar utente" class="avatar-menu-piccolo">
                            <span class="nome-utente-display"><?php echo htmlspecialchars($username_profile); ?></span>
                        </div>
                        <div class="menu-utente-body">
                            <a href="profilo_utente.php" class="menu-link profilo_utente-link">Profilo Utente</a>
                            <a href="logout.php" class="menu-link logout-link">Logout</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="menu-mobile-container">
                    <button class="pulsante-menu-mobile">☰</button>
                    <div class="dropdown-menu nascosto">
                      <a href="#" class="pulsante-scarica-app-mobile">Scarica App</a>
                      <a href="https://github.com/williamvil1" class="pulsante-github-mobile">GitHub</a>
                    </div>
                </div>
            </span>
        </div>
    </header>

    <div class="container">
        <div class="contenuto-destro">
            <div class="interazioni-header">
                <a href="profilo_utente.php" class="btn-torna-profilo">⬅️ Torna al Profilo</a>
                <h2>🔄 Le Mie Interazioni</h2>
                <p class="info-testo">Tutti i post con cui hai interagito</p>
            </div>
            <div class="sezione-main" id="sezione-main-interazioni">
            </div>
        </div>
    </div>

    <footer id="footer-pagina_principale">
        <p>Sito creato da<br><i>William Villari</i></p>
    </footer>
    
    <div id="immagine-modale" class="modale-immagine nascosto">
      <span class="chiudi-modale">&times;</span>
      <img class="contenuto-modale" id="img-modale">
      <div class="didascalia-modale"></div>
    </div>

    <footer id="footer-telefono">
        <p>Sito creato da William Villari</p>
    </footer>
</body>
</html>