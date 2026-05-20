<?php
require_once __DIR__ . '/../config/auth.php';
checkRole(['ADMINISTRADOR', 'SECRETARIO']); //  ajustado según tu sistema

$pdo = require __DIR__ . '/../config/db.php';

$nombre = trim($_POST['nombre'] ?? '');
$accion = $_POST['accion'] ?? '';
$id_curso = $_POST['id_curso'] ?? null;

if ($accion !== 'eliminar' && $nombre === '') {
    header("Location: ../public/asignar_cursos.php?error=El nombre del curso es obligatorio");
    exit;
}

try {

        // =========================
        // ELIMINAR CURSO
        // =========================
        if($accion === 'eliminar'){

            if(!$id_curso){
                exit("ID inválido");
            }

            // verificar si el curso tiene grupos
            $stmt = $pdo->prepare("
                SELECT id_grupo
                FROM grupos
                WHERE id_curso = ?
                LIMIT 1
            ");

            $stmt->execute([$id_curso]);

            if($stmt->fetch()){
                exit("No se puede eliminar porque el curso tiene grupos registrados");
            }

            $stmt = $pdo->prepare("
                DELETE FROM cursos
                WHERE id_curso = ?
            ");

            $stmt->execute([$id_curso]);

            exit("ok");
        }

        // =========================
        // EDITAR CURSO
        // =========================
        if($accion === 'editar'){

            if(!$id_curso || $nombre === ''){
                exit("Datos inválidos");
            }

            // validar duplicado
            $stmt = $pdo->prepare("
                SELECT id_curso
                FROM cursos
                WHERE nombre_curso = ?
                AND id_curso != ?
            ");

            $stmt->execute([$nombre, $id_curso]);

            if($stmt->fetch()){
                exit("Ya existe otro curso con ese nombre");
            }

            $stmt = $pdo->prepare("
                UPDATE cursos
                SET nombre_curso = ?
                WHERE id_curso = ?
            ");

            $stmt->execute([$nombre, $id_curso]);

            exit("ok");
        }

        // =========================
        // VERIFICAR DUPLICADO
        // =========================
        $stmt = $pdo->prepare("SELECT id_curso FROM cursos WHERE nombre_curso = ?");
        $stmt->execute([$nombre]);

        if ($stmt->fetch()) {
            header("Location: ../public/asignar_cursos.php?error=El curso ya existe");
            exit;
        }

        // =========================
        // INSERTAR CURSO
        // =========================
        $stmt = $pdo->prepare("INSERT INTO cursos (nombre_curso) VALUES (?)");
        $stmt->execute([$nombre]);

        header("Location: ../public/asignar_cursos.php?success=Curso agregado correctamente");
        exit;

    } catch (Exception $e) {

        header("Location: ../public/asignar_cursos.php?error=" . urlencode($e->getMessage()));
        exit;
    }
