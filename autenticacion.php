<?php
    $segundos_vida = 60;
    
    session_set_cookie_params([
        'lifetime' => $segundos_vida,
        'path' => '/',
        'domain' => '',
        'secure' => true,  
        'httponly' => true,   
        'samesite' => 'Strict' 
    ]);
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {

        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Error de seguridad (Token CSRF inválido).";
            header('Location: ./index.php');
            exit();
        }


        $host = 'localhost';
        $db   = 'login-php';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        // Data Source Name (DSN)
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,      
            PDO::ATTR_EMULATE_PREPARES   => false,            
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error de conexión con el sistema.";
            header('Location: ./index.php');
            exit();
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $limite_intentos = 5;
        $tiempo_bloqueo = 30; 

        $stmt = $pdo->prepare("SELECT intentos, ultimo_intento FROM intentos_acceso WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);
        $fila_bloqueo = $stmt->fetch();

        if ($fila_bloqueo) {
            $tiempo_transcurrido = time() - $fila_bloqueo->ultimo_intento;

            if ($fila_bloqueo->intentos >= $limite_intentos && $tiempo_transcurrido < $tiempo_bloqueo) {
                $minutos_restantes = ceil(($tiempo_bloqueo - $tiempo_transcurrido) / 60);
                $_SESSION['error'] = "Acceso bloqueado por demasiados intentos. Espera $minutos_restantes minutos.";
                header("Location: ./index.php");
                exit();
            }
        }

        $usuario = htmlspecialchars($_POST['usuario']);
        $password = htmlspecialchars($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE idusuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        $user_data = $stmt->fetch();


        if (!$user_data) {
            registrarFalloPDO($pdo, $ip);
            $_SESSION['error'] = "Credenciales incorrectas.";
            header("Location: ./index.php");
            exit();
        } else {

            if ($user_data->password == $password) {
                
                $stmt_reset = $pdo->prepare("DELETE FROM intentos_acceso WHERE ip_address = :ip");
                $stmt_reset->execute(['ip' => $ip]);

                session_regenerate_id(true);

                $_SESSION['nombre'] = $user_data->nombre;
                $_SESSION['apellidos'] = $user_data->apellidos;
                
                $_SESSION['instante_login'] = time();
                $_SESSION['ultimo_regen'] = time();

                header("Location: ./inicio.php");
                exit();

            } else {
                registrarFalloPDO($pdo, $ip);
                $_SESSION['error'] = "Credenciales incorrectas.";
                header("Location: ./index.php");
                exit();
            }
        }

    } else {
        $_SESSION['error'] = "Acceso no autorizado.";
        header('Location: ./index.php');
        exit();
    }

    function registrarFalloPDO($pdo, $ip) {
        $tiempo_actual = time();
        $sql = "INSERT INTO intentos_acceso (ip_address, intentos, ultimo_intento) 
                VALUES (:ip, 1, :tiempo) 
                ON DUPLICATE KEY UPDATE 
                intentos = intentos + 1, 
                ultimo_intento = VALUES(ultimo_intento)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'ip' => $ip,
            'tiempo' => $tiempo_actual
        ]);
    }
?>