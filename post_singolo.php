<?php
require_once 'includes/auth.php';

$conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']);
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

$post_data = null;
$commenti_data = [];
$error_message = '';

$reddit_id_param = $_GET['reddit_id'] ?? null;
$db_id_param = $_GET['db_id'] ?? null;

$user_id_loggato = $_SESSION['ID'] ?? null;

if ($reddit_id_param) {
    $reddit_id_param_escaped = mysqli_real_escape_string($conn, $reddit_id_param);
    $sql_post = "SELECT p.*, 
                        (SELECT v.tipo_voto FROM voti_utenti v WHERE v.post_id = p.id AND v.user_id = '$user_id_loggato' LIMIT 1) AS tipo_voto_utente
                 FROM post p 
                 WHERE p.reddit_id = '$reddit_id_param_escaped'";
} elseif ($db_id_param) {
    $db_id_param_escaped = mysqli_real_escape_string($conn, $db_id_param);
    $sql_post = "SELECT p.*,
                        (SELECT v.tipo_voto FROM voti_utenti v WHERE v.post_id = p.id AND v.user_id = '$user_id_loggato' LIMIT 1) AS tipo_voto_utente
                 FROM post p 
                 WHERE p.id = '$db_id_param_escaped'";
} else {
    $error_message = "Nessun ID post fornito.";
}

if (isset($sql_post)) {
    $result_post = mysqli_query($conn, $sql_post);
    if (mysqli_num_rows($result_post) > 0) {
        $post_data = mysqli_fetch_assoc($result_post);
        $post_db_id = $post_data['id'];

        $sql_commenti = "SELECT c.*, u.username, u.avatar AS user_avatar 
                         FROM commenti c 
                         JOIN utenti u ON c.user_id = u.id 
                         WHERE c.post_id = '$post_db_id' 
                         ORDER BY c.data_commento ASC";
        $result_commenti = mysqli_query($conn, $sql_commenti);
        
        if ($result_commenti) {
            while ($row = mysqli_fetch_assoc($result_commenti)) {
                $commenti_data[] = $row;
            }
        } else {
            $error_message = "Errore nel recupero dei commenti.";
        }
    } else {
        $error_message = "Post non trovato.";
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post_data ? htmlspecialchars($post_data['titolo']) : 'Post'; ?> Reddit </title>
    <link rel="stylesheet" href="style.css">
    <script src="assets/js/main.js" defer></script>
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
        <div class="contenuto-destro">
            <section class="sezione-main">
        <?php if ($error_message): ?>
            <p class="errore-messaggio"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($post_data): ?>
            <article class="post post-singolo" 
                     data-reddit-id="<?php echo htmlspecialchars($post_data['reddit_id']); ?>"
                     data-autore="<?php echo htmlspecialchars($post_data['autore']); ?>"
                     data-subreddit="<?php echo htmlspecialchars($post_data['subreddit']); ?>"
                     data-url="<?php echo htmlspecialchars($post_data['url'] ?? ''); ?>"
                     data-thumbnail="<?php echo htmlspecialchars($post_data['thumbnail'] ?? ''); ?>"
                     data-contenuto="<?php echo htmlspecialchars($post_data['contenuto'] ?? ''); ?>"
                     data-voto="<?php echo htmlspecialchars($post_data['voto'] ?? '0'); ?>">
                
                <div class="header-post">
                    <div class="post-info">
                        <h1 class="titolo-post"><?php echo htmlspecialchars($post_data['titolo']); ?></h1>
                        <div class="user-info">
                            <p class="utente-post">Pubblicato da <?php echo htmlspecialchars($post_data['autore']); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($post_data['subreddit'])): ?>
                    <div class="subreddit-container">
                        <div class="avatar-subreddit"><img src="assets/images/<?php echo htmlspecialchars($post_data['subreddit']); ?>.png" alt="logo <?php echo htmlspecialchars($post_data['subreddit']); ?>" onerror="this.src='assets/images/reddit-logo.png';"/></div>
                        <a class="pulsante-subreddit">r/<?php echo htmlspecialchars($post_data['subreddit']); ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="corpo-post">
                    <?php if (!empty($post_data['contenuto'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($post_data['contenuto'])); ?></p>
                    <?php endif; ?>

                    <?php 
                        $imageUrl = null;
                        $sourceUrlForImageCheck = $post_data['url'] ?? null;
                        if (!empty($post_data['immagine_base64'])) {
                            $imageUrl = 'data:image/jpeg;base64,' . $post_data['immagine_base64'];
                        } elseif (!empty($post_data['url']) && preg_match('/\.(jpeg|jpg|gif|png)$/i', $post_data['url'])) {
                            $imageUrl = $post_data['url'];
                        }
                    ?>
                    <?php if ($imageUrl): ?>
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($post_data['titolo']); ?>" class="immagine-post">
                    <?php elseif (!empty($post_data['url']) && empty($post_data['contenuto'])): ?>
                        <div class="post-link">
                            <a href="<?php echo htmlspecialchars($post_data['url']); ?>" class="btn-esterno" target="_blank" rel="noopener noreferrer">
                                <span class="icona-link">🔗</span>
                                <span> Apri contenuto esterno</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="post-voto">
                    <button class="pulsante-voto upvote <?php echo ($post_data['tipo_voto_utente'] ?? 0) == 1 ? 'votato' : ''; ?>">↑</button>
                    <span class="contatore-voto"><?php echo htmlspecialchars($post_data['voto'] ?? '0'); ?></span>
                    <button class="pulsante-voto downvote <?php echo ($post_data['tipo_voto_utente'] ?? 0) == -1 ? 'votato' : ''; ?>">↓</button>
                </div>
            </article>

            <section class="sezione-commenti-singolo">
                <h4 class="titolo-commenti">Commenti (<?php echo count($commenti_data); ?>)</h4>
                <div class="lista-commenti-caricati">
                    <?php if (empty($commenti_data)): ?>
                        <p class="nessun-commento-messaggio">Nessun commento ancora. Sii il primo!</p>
                    <?php else: ?>
                        <?php foreach ($commenti_data as $commento): ?>
                            <div class="commento">
                                <div class="commento-contenuto">
                                    <div class="header-commenti">
                                        <div class="avatar-commento">
                                            <img src="<?php echo htmlspecialchars(!empty($commento['user_avatar']) ? $commento['user_avatar'] : 'assets/images/reddit-logo.png'); ?>" alt="Avatar utente">
                                        </div>
                                        <div class="commenti-info">
                                            <h3 class="autore-commento"><?php echo htmlspecialchars($commento['username']); ?></h3>
                                            <span class="data-commento-inline"><?php echo date("d/m/Y H:i", strtotime($commento['data_commento'])); ?></span>
                                            <p class="testo-commento"><?php echo nl2br(htmlspecialchars($commento['contenuto'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (check_login()): ?>
                <div class="post-footer">
                    <div class="aggiungi-commento">
                        <div class="inserisci-commento-container">
                            <textarea class="inserisci-commento" placeholder="Scrivi un commento..."></textarea>
                        </div>
                        <div class="commento-sezione_voti">
                            <button class="pulsante_invia-commento">Invia Commento</button>
                            <button class="pulsante_genera-ai">🤖 Genera commento AI</button>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <p><a href="login.php">Accedi</a> per commentare.</p>
                <?php endif; ?>
        </section>
        <?php endif; ?>
        </section>
    </div>
    </main>
    
    
    <footer id="footer-telefono">
        <p>Sito creato da William Villari</p>
    </footer>
    
    <footer id="footer-pagina_principale">
        <p>Sito creato da<br><i>William Villari</i></p>
    </footer>
    
    <div id="immagine-modale" class="modale-immagine nascosto">
        <span class="chiudi-modale">&times;</span>
        <img class="contenuto-modale" id="img-modale">
        <div class="didascalia-modale"></div>
    </div>
    
    <?php
    if (isset($conn)) {
        mysqli_close($conn);
    }
    ?>
</body>
</html>
