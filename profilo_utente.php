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
$bio_profile = '';
$data_registrazione_utente = null;
$email_utente = null;
$numero_commenti_utente = 0;

if ($conn) {
    $safe_user_id_session = intval($user_id_session);
    $query_info = "SELECT avatar, username, bio, data_registrazione, email FROM utenti WHERE id = $safe_user_id_session";
    $result_info = mysqli_query($conn, $query_info);

    if (mysqli_num_rows($result_info) > 0) {
        $row_info = mysqli_fetch_assoc($result_info);
        $avatar_profile = $row_info['avatar'];
        $username_profile = $row_info['username'];
        $bio_profile = $row_info['bio'] ?? '';
        $data_registrazione_utente = $row_info['data_registrazione'];
        $email_utente = $row_info['email'] ?? '';
        mysqli_free_result($result_info);
    } else if (!$result_info) {
        echo "Errore nella query: " . mysqli_error($conn);
    }

    $query_commenti_count = "SELECT COUNT(*) as commenti_totali FROM commenti WHERE user_id = $safe_user_id_session";
    $result_commenti_count = mysqli_query($conn, $query_commenti_count);
    if ($result_commenti_count) {
        $row_commenti_count = mysqli_fetch_assoc($result_commenti_count);
        $numero_commenti_utente = $row_commenti_count['commenti_totali'];
        mysqli_free_result($result_commenti_count);
    }
    mysqli_close($conn);
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo di <?php echo htmlspecialchars($username_profile); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="assets/js/main.js" defer></script>
    <script src="assets/js/profilo.js" defer></script>
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

    <div class="contenuto-destro profilo-contenuto-speciale"> 
        <div id="benvenuto-iniziale" class="benvenuto-container">
            <h3>👋 Benvenuto nel tuo profilo, <?php echo htmlspecialchars($username_profile); ?>!</h3>
            <p>Seleziona una scheda qui sotto per visualizzare le tue attività o modificare il tuo profilo.</p>
        </div>

        <div class="profilo-tabs">
            <button class="tab-button" data-tab="interazioni">🔄 Le Mie Interazioni</button>
            <button class="tab-button" data-tab="informazioni">ℹ️ Informazioni Profilo</button>
            <button class="tab-button" data-tab="modifica">⚙️ Modifica Profilo</button>
        </div>

        <div class="tab-content" id="tab-interazioni">
            <section class="sezione-main">
                <h3>Post con cui hai interagito</h3>
                <p class="info-testo">Visualizza tutti i post con cui hai interagito</p>
                <a href="interazioni_post.php" class="btn-salva btn-interazioni-link">🔄 Visualizza le Mie Interazioni</a>
            </section>
        </div>

        <div class="tab-content" id="tab-informazioni">
            <section class="sezione-main">
                <h3>Le Tue Informazioni</h3>
                <div class="info-profilo-container">
                    <div class="info-profilo-item">
                        <span class="info-label">Nome Utente:</span>
                        <span class="info-valore"><?php echo htmlspecialchars($username_profile); ?></span>
                    </div>

                    <div class="info-profilo-item bio-item">
                        <span class="info-label">Biografia:</span>
                            <div class="info-valore bio-display">
                                <?php echo !empty($bio_profile) ? htmlspecialchars($bio_profile) : '<em>Nessuna biografia impostata.</em>'; ?>
                            </div>
                    </div>
                    
                    <?php if (isset($data_registrazione_utente) && !empty($data_registrazione_utente)): ?>
                    <div class="info-profilo-item">
                        <span class="info-label">Membro Dal:</span>
                        <span class="info-valore"><?php echo htmlspecialchars(date("d/m/Y", strtotime($data_registrazione_utente))); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($email_utente) && !empty($email_utente)):?>
                    <div class="info-profilo-item">
                        <span class="info-label">Email:</span>
                        <span class="info-valore"><?php echo htmlspecialchars($email_utente); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($numero_commenti_utente)): ?>
                    <div class="info-profilo-item">
                        <span class="info-label">Commenti Creati:</span>
                        <span class="info-valore"><?php echo htmlspecialchars($numero_commenti_utente); ?></span>
                    </div>
                    <?php endif; ?>

                </div>
            </section>
        </div>

       <div class="tab-content" id="tab-modifica">
            <section class="sezione-main">
                <h3>Modifica il tuo profilo</h3>
                <form id="form-modifica-profilo" class="form-modifica" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nuova-bio">Aggiungi/modifica biografia:</label>
                        <textarea id="nuova-bio" name="bio" rows="4" placeholder="Scrivi qualcosa su di te..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="password">Cambio password:</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Nuova password (opzionale)">
                            <span class="toggle-password-visibility2" data-target="password">👁️</span>
                        </div>
                        <div class="input-wrapper">
                            <input type="password" id="confirm_password" name="conferma_password" placeholder="Conferma nuova password (opzionale)">
                            <span class="toggle-password-visibility2" data-target="confirm_password">👁️</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-salva">💾 Salva Modifiche</button>
                </form>
                <div id="messaggio-aggiornamento-profilo"></div>
            </section>
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