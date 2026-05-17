<?php
session_start();

// Reporte de errores para debug en el hosting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos MySQL (Hosting)
$host = 'localhost'; // En Hostinger esto debe mantenerse como 'localhost'
$dbname = 'u372417318_estampas';
$user = 'u372417318_Ferad';
$password = 'Faja052603050406';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error de conexión a MySQL. ¿Ya creaste la base de datos 'estampas' en phpMyAdmin? Detalles: " . $e->getMessage());
}

// Datos maestros de las secciones (Países y prefijos)
$sections = [
    'Intro y Museos' => 'FWC', 'Alemania' => 'GER', 'Arabia Saudita' => 'KSA', 'Argelia' => 'ALG',
    'Argentina' => 'ARG', 'Australia' => 'AUS', 'Austria' => 'AUT', 'Bélgica' => 'BEL',
    'Bosnia-Herzegovina' => 'BIH', 'Brasil' => 'BRA', 'Cabo Verde' => 'CPV', 'Canadá' => 'CAN',
    'Colombia' => 'COL', 'Corea del Sur' => 'KOR', 'Costa de Marfil' => 'CIV', 'Croacia' => 'CRO',
    'Curazao' => 'CUW', 'Ecuador' => 'ECU', 'Egipto' => 'EGY', 'Escocia' => 'SCO',
    'España' => 'ESP', 'Estados Unidos' => 'USA', 'Francia' => 'FRA', 'Ghana' => 'GHA',
    'Haití' => 'HAI', 'Inglaterra' => 'ENG', 'Irak' => 'IRQ', 'Irán' => 'IRN',
    'Japón' => 'JPN', 'Jordania' => 'JOR', 'Marruecos' => 'MAR', 'México' => 'MEX',
    'Noruega' => 'NOR', 'Nueva Zelanda' => 'NZL', 'Países Bajos' => 'NED', 'Panamá' => 'PAN',
    'Paraguay' => 'PAR', 'Portugal' => 'POR', 'Qatar' => 'QAT', 'RD Congo' => 'COD',
    'República Checa' => 'CZE', 'Senegal' => 'SEN', 'Sudáfrica' => 'RSA', 'Suecia' => 'SWE',
    'Suiza' => 'SUI', 'Túnez' => 'TUN', 'Turquía' => 'TUR', 'Uruguay' => 'URU', 'Uzbekistán' => 'UZB'
];
?>
