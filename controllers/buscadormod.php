<?php 
require '../models/conexion.php'; // tu archivo de conexión
session_start();
$idusu = $_SESSION['idusu'] ?? null;
$idper = $_SESSION['idper'] ?? null; // <- asegúrate que exista en tu sesión

if (!isset($_GET['q']) || $idusu === null) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q']);

$modelo = new conexion();
$conexion = $modelo->get_conexion();

if ($idusu === 1) {
    // Admin (ve todo)
    $sql = "SELECT p.idpag, p.nompag, p.icopag, p.idmod, m.nommod
            FROM pagina p
            INNER JOIN modulo m ON p.idmod = m.idmod
            WHERE p.mospas = 1
              AND (p.nompag LIKE :q OR m.nommod LIKE :q)
            ORDER BY p.ordpag
            LIMIT 10";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([":q" => "%$q%"]);
} else {
    // Usuario normal
    $sql = "SELECT p.idpag, p.nompag, p.icopag, p.idmod, m.nommod
            FROM pagina p
            INNER JOIN modulo m ON p.idmod = m.idmod
            INNER JOIN pagper j ON p.idpag = j.idpag
            LEFT JOIN accxuser a ON p.idpag = a.idpag AND a.idusu = :idusu
            WHERE p.mospas = 1
              AND j.idper = :idper
              AND (p.nompag LIKE :q OR m.nommod LIKE :q)
            ORDER BY p.ordpag
            LIMIT 10";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":idusu" => $idusu,
        ":idper" => $idper,
        ":q"     => "%$q%"
    ]);
}

// Obtener resultados en array asociativo
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($datos);
