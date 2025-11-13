<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/vehiculos_functions.php';

checkAuth();

if (!isChofer()) { 
    $_SESSION['error'] = "Acceso denegado.";
    header("Location: ../dashboard.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = "Vehículo no válido.";
    header("Location: vehiculos.php");
    exit;
}

$vehiculo = getVehiculoById($id);

if (!$vehiculo || $vehiculo['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "No tienes permiso para editar este vehículo.";
    header("Location: vehiculos.php");
    exit;
}

// Solo se edita el vehiculo si esta pendiente
if ($vehiculo['estado'] !== 'pendiente') {
    $_SESSION['error'] = "Solo los vehículos en estado pendiente pueden editarse.";
    header("Location: vehiculos.php");
    exit;
}

$errors = [];

$marca = $vehiculo['marca'];
$modelo = $vehiculo['modelo'];
$ano = $vehiculo['ano'];
$color = $vehiculo['color'];
$placa = $vehiculo['placa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $ano = intval($_POST['ano']);
    $color = trim($_POST['color']);
    $placa = trim($_POST['placa']);

    $foto = $vehiculo['foto'];
    if (!empty($_FILES['foto']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Formato de imagen no válido. Solo JPG, PNG, WEBP.";
        } else {
            $newName = uniqid('veh_') . "." . $ext;
            $uploadPath = "../uploads/vehiculos/" . $newName;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {

                if ($vehiculo['foto'] && file_exists("../uploads/vehiculos/" . $vehiculo['foto'])) {
                    unlink("../uploads/vehiculos/" . $vehiculo['foto']);
                }

                $foto = $newName;

            } else {
                $errors[] = "Error al subir la imagen.";
            }
        }
    }

    if (empty($marca)) $errors[] = "La marca es obligatoria.";
    if (empty($modelo)) $errors[] = "El modelo es obligatorio.";
    if ($ano < 1990 || $ano > intval(date("Y")) + 1) $errors[] = "Año no válido.";
    if (empty($color)) $errors[] = "El color es obligatorio.";
    if (empty($placa)) $errors[] = "La placa es obligatoria.";

    if (empty($errors)) {

        $updated = updateVehiculo($id, [
            'marca' => $marca,
            'modelo' => $modelo,
            'ano' => $ano,
            'color' => $color,
            'placa' => $placa,
            'foto' => $foto
        ]);

        if ($updated) {
            $_SESSION['success'] = "Vehículo actualizado correctamente.";
            header("Location: vehiculos.php");
            exit;
        } else {
            $errors[] = "Error al actualizar el vehículo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vehículo - <?php echo SITIO; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/vehiculos.css" rel="stylesheet">

    <style>
        .card-header {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
        }
        .veh-img-preview {
            max-width: 240px;
            border-radius: 10px;
        }
        .veh-ico {
            color: #ffc107;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<div class="veh-container">
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit"></i> Editar Vehículo
                        </h4>

                        <div>
                            <a href="vehiculos_view.php?id=<?= $id; ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> Ver Detalle</a>
                            <a href="vehiculos.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Mis Vehículos</a>
                        </div>
                    </div>

                    <div class="card-body veh-form">

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Corrige los siguientes errores:</strong>
                            <ul class="mt-2 mb-0">
                                <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-car veh-ico"></i> Marca *</label>
                                    <input type="text" name="marca" class="form-control"value="<?= htmlspecialchars($marca); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-car-side veh-ico"></i> Modelo *</label>
                                    <input type="text" name="modelo" class="form-control"value="<?= htmlspecialchars($modelo); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><i class="fas fa-calendar veh-ico"></i> Año *</label>
                                    <input type="number" name="ano" class="form-control"value="<?= htmlspecialchars($ano); ?>"min="1990" max="<?= date("Y")+1 ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><i class="fas fa-palette veh-ico"></i> Color *</label>
                                    <input type="text" name="color" class="form-control"value="<?= htmlspecialchars($color); ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><i class="fas fa-id-card veh-ico"></i> Placa *</label>
                                    <input type="text" name="placa" class="form-control"value="<?= htmlspecialchars($placa); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-image veh-ico"></i> Foto</label>
                                <input type="file" name="foto" class="form-control">

                                <?php if ($vehiculo['foto']): ?>
                                <div class="mt-2">
                                    <p class="small text-muted">Foto actual:</p>
                                    <img src="../uploads/vehiculos/<?= $vehiculo['foto']; ?>" 
                                         class="veh-img-preview shadow">
                                </div>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="vehiculos.php" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Cancelar</a>
                                <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>