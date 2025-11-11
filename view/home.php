<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Studio 360</title>
  <link rel="stylesheet" href="../css/variaveis.css">
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/home.css">
</head>
<body>
  <header>
    <div class="container">
      <h1 class="logo">Studio 360</h1>
      <nav>
        <ul class="nav-links">
          <li><a href="#">Home</a></li>
          <li><a href="#">Sobre</a></li>
          <li><a href="#">Portfólio</a></li>
          <li><a href="./login.php">Login</a></li>
          <!-- <li><a href="../">Teste</a></li> -->
        </ul>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h2>Ideias que giram o mundo.</h2>
      <p>Design, código, fotos e criatividade — tudo em um só lugar.</p>
      <a href="#" class="btn">Conheça o portfólio</a>
    </div>
  </section>
  <section class="janela">
    <h2>Trabalhos Feitos</h2>
    <div class="cards-container">
      <?php for($i = 1; $i <= 10; $i++): ?>
        <!-- Card -->
        <div class="card" style="background-image: url('https://picsum.photos/600/400');">
          <div class="card-content">
            <h2 class="card-title">Título do Item <?= $i ?></h2>
            <p class="card-category">Categoria: Fotografia</p>
            <div class="profile">
              <img src="../img/teste-Perfil.png" alt="Foto do perfil">
              <span class="profile-name">Teste <?= $i ?></span>
            </div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </section>
</body>
</html>