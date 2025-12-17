<?php

$segundos_cookie = 60;

session_set_cookie_params([
    'lifetime' => $segundos_cookie,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

$limite_sesion = 7200;
$intervalo_regen = 300;

if (isset($_SESSION['instante_login'])) {
    $tiempo_transcurrido = time() - $_SESSION['instante_login'];

    if ($tiempo_transcurrido > $limite_sesion) {
        header("Location: logout.php");
        exit();
    }
} else {
    header("Location: logout.php");
    exit();
}

if (isset($_SESSION['ultimo_regen'])) {
    $tiempo_desde_regen = time() - $_SESSION['ultimo_regen'];

    if ($tiempo_desde_regen > $intervalo_regen) {
        session_regenerate_id(true);

        $_SESSION['ultimo_regen'] = time();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Panel Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Mi Sistema</a>

            <div class="d-flex align-items-center">
                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header">
                Bienvenido
            </div>
            <div class="card-body">
                <h5 class="card-title">Has iniciado sesión correctamente.</h5>
                <p class="card-text">Esta es la página de inicio segura. Solo los usuarios autenticados pueden ver esto.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>