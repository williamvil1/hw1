<?php
require_once 'includes/auth.php';

if(!check_login()) {
    header('Location: login.php');
    exit();
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
        <a href="crea_post.php" class="pulsante-crea-post">Crea Post</a>
        <a href="https://github.com/williamvil1" class="pulsante-github">Git Hub</a>
        <button id="tema-toggle" class="pulsante-tema" aria-label="Cambia tema">
          <span class="icona-tema">☀️</span>
        </button>
        <?php
        if(isset($_SESSION['UTENTE'])) {
          $conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']) or die(mysqli_error($conn));
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
          
          if ($res) {
            mysqli_free_result($res);
          }
          mysqli_close($conn);
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

  <div class="container">
    <nav class="categorie">
      <h2 class="home-link">
        <span class="icona-home"></span>
        <a href="index.php">Home</a>
      </h2>
      <div class="link-categorie">
        <h5 class="gaming-link">
          <span class="icona-gaming">
            <img src="assets/images/gaming.png" alt="Gaming">
          </span>
          <a href="#" data-subreddit="gaming">Gaming</a>
        </h5>
        <h5 class="link-sport">
          <span class="icona-sport">
            <img src="assets/images/sports.png" alt="Sport">
          </span>
          <a href="#" data-subreddit="sports">Sport</a>
        </h5>
        <h5 class="link-anime">
          <span class="icona-anime">
            <img src="assets/images/anime.png" alt="Anime">
          </span>
          <a href="#" data-subreddit="anime">Anime</a>
        </h5>
        <h5 class="link-film_e_serie">
          <span class="icona-film_e_serie">
            <img src="assets/images/movies.png" alt="Film">
          </span>
          <a href="#" data-subreddit="movies">Film e serie</a>
        </h5>
        <h5 class="link-musica">
          <span class="icona-musica">
            <img src="assets/images/Music.png" alt="Musica">
          </span>
          <a href="#" data-subreddit="music">Musica</a>
        </h5>
        <h5 class="link-scienze">
          <span class="icona-scienze">
            <img src="assets/images/science.png" alt="Scienze">
          </span>
          <a href="#" data-subreddit="science">Scienze</a>
        </h5>
      </div>
      <div id="div-informazioni">
        <h4 class="link-informazioni">
          <span class="icona-informazioni">
            <img src="assets/images/informazioni.png" alt="Informazioni">
          </span>
          <a href="">Informazioni</a>
        </h4>
      </div>
      <footer id="footer-pagina_principale">
        <p>Sito creato da<br><i>William Villari</i></p>
      </footer>
    </nav>

    <div class="contenuto-destro">
      <section id="sezione-main-index" class="sezione-main">
      </section>

      <div class="barra-laterale">
        <div class="box-laterale post-recenti">
          <h3>Post Recenti</h3>
          <button id="btn-carica-post-recenti" class="pulsante-carica-recenti">Carica Post Recenti</button>
          <ul class="lista-post-recenti">
            <li><p class="loading-message">Clicca il pulsante per caricare i post recenti.</p></li>
          </ul>
        </div>
    
        <div class="box-laterale community">
          <h3>Community Popolari</h3>
          <ul class="lista-community">
            <li>
              <a href="#" data-subreddit="gaming">
                <div class="avatar-community">
                  <img src="assets/images/gaming.png" alt="Gaming">
                </div>
                <div class="community-info">
                  <p class="community-nome">r/Gaming</p>
                  <p class="community-membri">5.2M membri</p>
                </div>
              </a>
            </li>
            <li>
              <a href="#" data-subreddit="sports">
                <div class="avatar-community">
                  <img src="assets/images/sports.png" alt="Sport">
                </div>
                <div class="community-info">
                  <p class="community-nome">r/Sport</p>
                  <p class="community-membri">3.8M membri</p>
                </div>
              </a>
            </li>
            <li>
              <a href="#" data-subreddit="anime">
                <div class="avatar-community">
                  <img src="assets/images/anime.png" alt="Anime">
                </div>
                <div class="community-info">
                  <p class="community-nome">r/Anime</p>
                  <p class="community-membri">2.6M membri</p>
                </div>
              </a>
            </li>
            <li>
                <a href="#" data-subreddit="movies">
                  <div class="avatar-community">
                    <img src="assets/images/movies.png" alt="Film">
                  </div>
                  <div class="community-info">
                    <p class="community-nome">r/Film</p>
                    <p class="community-membri">6.2M membri</p>
                  </div>
                </a>
            </li>
            <li>
                <a href="#" data-subreddit="Music">
                  <div class="avatar-community">
                    <img src="assets/images/Music.png" alt="Musica">
                  </div>
                  <div class="community-info">
                    <p class="community-nome">r/Musica</p>
                    <p class="community-membri">18.0M membri</p>
                  </div>
                </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <footer id="footer-telefono">
    <p>Sito creato da William Villari</p>
  </footer>

  <div id="immagine-modale" class="modale-immagine nascosto">
    <span class="chiudi-modale">&times;</span>
    <img class="contenuto-modale" id="img-modale">
    <div class="didascalia-modale"></div>
  </div>

</body>
</html>