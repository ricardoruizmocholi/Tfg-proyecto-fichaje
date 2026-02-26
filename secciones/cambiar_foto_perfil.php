<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Verificar sesión
if(!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];

// Verificar que se subió un archivo
if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen']);
    exit();
}

$archivo = $_FILES['foto'];
$nombreArchivo = $archivo['name'];
$tmpName = $archivo['tmp_name'];
$tamano = $archivo['size'];
$error = $archivo['error'];

// Validar tipo de archivo
$extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if(!in_array($extension, $extensionesPermitidas)) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Solo JPG, PNG, GIF, WEBP']);
    exit();
}

// Validar tamaño (máximo 5MB)
if($tamano > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'La imagen no debe superar los 5MB']);
    exit();
}

// Crear carpeta uploads si no existe
$directorioUploads = __DIR__ . '/../secciones/uploads/';
if(!is_dir($directorioUploads)) {
    mkdir($directorioUploads, 0755, true);
}

// Generar nombre único para la imagen
$nuevoNombre = 'perfil_' . $id_usuario . '_' . time() . '.' . $extension;
$rutaDestino = $directorioUploads . $nuevoNombre;

// Mover archivo subido
if(move_uploaded_file($tmpName, $rutaDestino)) {
    
    // Obtener la foto anterior para eliminarla (opcional)
    $sqlObtenerFotoAnterior = "SELECT foto_perfil FROM USUARIO WHERE id_usuario = :id_usuario";
    $stmtAnterior = $pdo->prepare($sqlObtenerFotoAnterior);
    $stmtAnterior->execute(['id_usuario' => $id_usuario]);
    $fotoAnterior = $stmtAnterior->fetchColumn();
    
    // Eliminar foto anterior si existe y no es la default
    if($fotoAnterior && $fotoAnterior !== 'secciones/uploads/perfil_default.jpg' && file_exists(__DIR__ . '/../' . $fotoAnterior)) {
        unlink(__DIR__ . '/../' . $fotoAnterior);
    }
    
    // Actualizar en la base de datos
    $rutaRelativa = 'secciones/uploads/' . $nuevoNombre;
    $sqlActualizar = "UPDATE USUARIO SET foto_perfil = :foto_perfil WHERE id_usuario = :id_usuario";
    $stmtActualizar = $pdo->prepare($sqlActualizar);
    $actualizado = $stmtActualizar->execute([
        'foto_perfil' => $rutaRelativa,
        'id_usuario' => $id_usuario
    ]);
    
    if($actualizado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Foto actualizada correctamente',
            'nueva_foto' => $rutaRelativa
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
}
?>