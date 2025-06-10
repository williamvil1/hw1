<?php
require_once 'includes/auth.php';
if(!check_login()) {
    header('Location: login.php');
    exit();
}
$conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']);


$user_id = $_SESSION['ID'];
$username = $_SESSION['UTENTE'];

$errori = [];
$titolo_valore = '';
$contenuto_testo_valore = '';
$contenuto_link_valore = '';
$tipo_contenuto_valore = 'text';
$subreddit_valore = '';


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!$conn) {
        $errori[] = 'Connessione al database fallita.';
    } else {
        $titolo = trim($_POST['titolo'] ?? '');
        $contenuto_testo = trim($_POST['contenuto_testo'] ?? '');
        $tipo_contenuto = $_POST['tipo_contenuto'] ?? 'text';
        $subreddit = trim($_POST['subreddit'] ?? '');

        $titolo_valore = $titolo;
        $contenuto_testo_valore = $contenuto_testo;
        $tipo_contenuto_valore = $tipo_contenuto;
        $subreddit_valore = $subreddit;

        if(empty($titolo)) {
            $errori[] = 'Il titolo è obbligatorio.';
        }

        if(strlen($titolo) > 500) {
            $errori[] = 'Il titolo non può superare i 500 caratteri.';
        }

        if(empty($subreddit)) {
            $errori[] = 'Il subreddit è obbligatorio.';
        } else if (strlen($subreddit) > 20) {
            $errori[] = 'Il nome del subreddit non può superare i 20 caratteri.';
        }

        $db_contenuto_finale = '';
        $db_url_post = null;
        $db_immagine_base64 = null;

        if($tipo_contenuto == 'text') {
            if(empty($contenuto_testo)) {
                $errori[] = 'Il contenuto del post è obbligatorio.';
            }
            $db_contenuto_finale = $contenuto_testo;
        } else if($tipo_contenuto == 'image') {
            if(isset($_FILES['contenuto_immagine']) && $_FILES['contenuto_immagine']['error'] == UPLOAD_ERR_OK) {
                $info_immagine = getimagesize($_FILES['contenuto_immagine']['tmp_name']);
                $immagini_consentite = ['image/jpeg', 'image/png', 'image/gif'];

                if($info_immagine === false || !in_array($info_immagine['mime'], $immagini_consentite)) {
                    $errori[] = 'Il file caricato non è un\'immagine valida (solo JPEG, PNG o GIF sono consentiti).';
                } else if($_FILES['contenuto_immagine']['size'] > 5 * 1024 * 1024) { // in pratica 5MB
                    $errori[] = 'L\'immagine non può superare i 5MB.';
                } else {
                    $immagine_data = file_get_contents($_FILES['contenuto_immagine']['tmp_name']);
                    if($immagine_data === false) {
                        $errori[] = 'Errore durante la lettura del file.';
                    } else {
                        $db_immagine_base64 = base64_encode($immagine_data);
                    }
                }
            } else {
                $errori[] = "È necessario caricare un'immagine per un post.";
                if(isset($_FILES['contenuto_immagine']) && $_FILES['contenuto_immagine']['error'] != UPLOAD_ERR_OK) {
                    error_log("Errore durante il caricamento dell'immagine: " . $_FILES['contenuto_immagine']['error']);
                }
            }
        }

        if(empty($errori)) {
            $titolo_escaped = mysqli_real_escape_string($conn, $titolo);
            $contenuto_escaped = mysqli_real_escape_string($conn, $db_contenuto_finale);
            $url_post_escaped = "NULL"; 
            $immagine_base64_escaped = $db_immagine_base64 ? "'" . mysqli_real_escape_string($conn, $db_immagine_base64) . "'" : "NULL";
            $tipo_contenuto_escaped = mysqli_real_escape_string($conn, $tipo_contenuto);
            $autore_escaped = mysqli_real_escape_string($conn, $username);
            $subreddit_escaped = mysqli_real_escape_string($conn, $subreddit);
            $reddit_id_generato = 'usr_' . uniqid(); 

            $sql = "INSERT INTO post (user_id, reddit_id, subreddit, titolo, autore, contenuto, tipo_contenuto, url, thumbnail, immagine_base64, voto, data_salvataggio) 
                    VALUES ('$user_id', '$reddit_id_generato', '$subreddit_escaped', '$titolo_escaped', '$autore_escaped', '$contenuto_escaped', '$tipo_contenuto_escaped', $url_post_escaped, NULL, $immagine_base64_escaped, 0, NOW())";



            if(mysqli_query($conn, $sql)) {
                $new_post_id = mysqli_insert_id($conn);
                header('Location: post_singolo.php?reddit_id=' .  $reddit_id_generato);
                exit();
            } else {
                $errori[] = 'Errore durante il salvataggio del post: ' . mysqli_error($conn);
                error_log("Errore SQL: " . mysqli_error($conn));
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Nuovo Post - Reddit</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="crea_post.css">
    <script src="assets/js/main.js" defer></script>
    <script src="assets/js/crea_post.js" defer></script>
</head>
<body>
    <header>
        <div class="header-container">
          <div class="header-sinistra">
            <span class="icona-reddit">
              <img src="assets/images/reddit-logo.png" alt="Reddit">
            </span>
            <h3 class="titolo-reddit">
              <a href="index.php">reddit</a>
            </h3>
          </div>
          <span class="ricerca">
            <input type="text" class="ricerca-input" placeholder="Cerca su Reddit">
          </span>
          <span class="pulsanti">
            <a href="https://github.com/williamvil1" class="pulsante-github">Git Hub</a>
            <button id="tema-toggle" class="pulsante-tema" aria-label="Cambia tema">
              <span class="icona-tema">☀️</span>
            </button>
            <?php
                if(isset($_SESSION['UTENTE'])) {
                    $id = $_SESSION['ID'];
                    $query = "SELECT avatar, username FROM utenti WHERE id = $id";
                    $res = mysqli_query($conn, $query);
                if(mysqli_num_rows($res) > 0) {
                    $row = mysqli_fetch_assoc($res);
                    $avatar = $row['avatar'];
                    $username = $row['username'];
                }
                    echo '<div class="avatar-container">';
                    echo "<img src='". htmlspecialchars($avatar) ."' alt='Avatar utente' class='avatar-utente'>";
                    echo '<div class="menu-utente nascosto">';
                    echo '<div class="menu-utente-header">';
                    echo "<img src='". htmlspecialchars($avatar) ."' alt='Avatar utente' class='avatar-menu-piccolo'>";
                    echo "<span class='nome-utente-display'>". htmlspecialchars($username) ."</span>";
                     echo '</div>';
                    echo '<div class="menu-utente-body">';
                    echo '<a href="profilo_utente.php" class="menu-link profilo_utente-link">Profilo Utente</a>';
                    echo '<a href="logout.php" class="menu-link logout-link">Logout</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
            }
            ?>
            <div class="menu-mobile-container">
              <button class="pulsante-menu-mobile">☰</button>
              <div class="dropdown-menu nascosto">
                <a href="https://github.com/williamvil1" class="pulsante-github-mobile">GitHub</a>
              </div>
            </div>
          </span>
        </div>
    </header>

    <main class="container">
        <div class="contenuto-principale form-creazione-post">
            <h1>Crea un Nuovo Post</h1>

            <form action="crea_post.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titolo">Titolo:</label>
                    <input type="text" name="titolo" id="titolo" class="form-control" value="<?php echo htmlspecialchars($titolo_valore); ?>" required>
                </div>

                <div class="form-group">
                    <label for="subreddit">Subreddit:</label>
                    <input type="text" name="subreddit" id="subreddit" class="form-control" value="<?php echo htmlspecialchars($subreddit_valore); ?>" required>
                    <small>Es: anime, gaming, Music...</small>
                </div>

                <div class="form-group">
                    <label>Tipo di Contenuto:</label>
                    <div>
                        <input type="radio" name="tipo_contenuto" id="tipo_testo" value="text" <?php if ($tipo_contenuto_valore == 'text') echo 'checked'; ?>>
                        <label for="tipo_testo">Testo</label>
                    </div>
                    <div>
                        <input type="radio" name="tipo_contenuto" id="tipo_immagine" value="image" <?php if ($tipo_contenuto_valore == 'image') echo 'checked'; ?>>
                        <label for="tipo_immagine">Immagine</label>
                    </div>
                </div>

                <div class="form-group campo-contenuto-dinamico" data-tipo="text" id="campo_contenuto_testo" <?php if ($tipo_contenuto_valore != 'text') echo 'style="display:none;"'; ?>>
                    <label for="contenuto_testo">Contenuto Testo:</label>
                    <textarea name="contenuto_testo" id="contenuto_testo" rows="5" class="form-control"><?php echo htmlspecialchars($contenuto_testo_valore); ?></textarea>
                </div>

                <div class="form-group campo-contenuto-dinamico" data-tipo="image" id="campo_contenuto_immagine" <?php if ($tipo_contenuto_valore != 'image') echo 'style="display:none;"'; ?>>
                    <label for="contenuto_immagine">Carica Immagine:</label>
                    <input type="file" name="contenuto_immagine" id="contenuto_immagine" class="form-control" accept="image/jpeg, image/png, image/gif">
                </div>

                <button type="submit" class="pulsante-primario">Crea Post</button>
            </form>

            <?php if (!empty($errori)): ?>
                <div class="messaggio-errore-contenitore">
                    <h4>Attenzione:</h4>
                    <ul>
                        <?php foreach ($errori as $errore): ?>
                            <li><?php echo htmlspecialchars($errore); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>


        </div>
    </main>

    <footer id="footer-telefono">
        <p>Sito creato da William Villari</p>
    </footer>
    
    <footer id="footer-pagina_principale">
        <p>Sito creato da<br><i>William Villari</i></p>
    </footer>

    <?php
        if (isset($conn)) {
            mysqli_close($conn);
        }
    ?>

    <div id="immagine-modale" class="modale-immagine nascosto">
        <span class="chiudi-modale">&times;</span>
        <img class="contenuto-modale" id="img-modale">
        <div class="didascalia-modale"></div>
    </div>

</body>
</html>


