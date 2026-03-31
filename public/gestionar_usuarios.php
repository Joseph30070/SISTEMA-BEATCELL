<?php
require_once __DIR__ . '/../config/auth.php';
require __DIR__ . '/../config/db.php';

checkRole(['ADMINISTRADOR']);

// SIMULACIÓN (luego BD)
$practicantes = [
    ["nombre"=>"Juan Pérez","dni"=>"12345678","telefono"=>"987654321","carrera"=>"Software","horario"=>"08:00-12:00"],
    ["nombre"=>"Ana López","dni"=>"87654321","telefono"=>"912345678","carrera"=>"Electrónica","horario"=>"14:00-18:00"]
];
?>

<div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">

  <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
    Gestionar Practicantes
  </h2>

  <div class="overflow-x-auto">
    <table class="w-full border border-gray-200 rounded-lg">

      <thead class="bg-gray-100 text-gray-700 text-sm">
        <tr>
          <th class="p-3">Nombre</th>
          <th class="p-3">DNI</th>
          <th class="p-3">Teléfono</th>
          <th class="p-3">Carrera</th>
          <th class="p-3">Horario</th>
          <th class="p-3 text-center">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($practicantes as $p): ?>
        <tr class="border-t hover:bg-gray-50 text-sm">
          <td class="p-3"><?= $p['nombre'] ?></td>
          <td class="p-3"><?= $p['dni'] ?></td>
          <td class="p-3"><?= $p['telefono'] ?></td>
          <td class="p-3"><?= $p['carrera'] ?></td>
          <td class="p-3"><?= $p['horario'] ?></td>

          <td class="p-3 text-center flex justify-center gap-2">
            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs">
              Editar
            </button>

            <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs">
              Eliminar
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>

</div>
