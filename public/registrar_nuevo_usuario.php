<div class="w-full">

  <div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
      Registrar Nuevo Practicante
    </h2>
    <p class="text-sm text-gray-500">
      Ingresa los datos del practicante.
    </p>
  </div>

  <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">

    <form action="#" method="POST" class="space-y-6">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- NOMBRE -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Nombre *</label>
          <input type="text" name="nombre" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <!-- DNI -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">DNI *</label>
          <input type="text" name="dni" maxlength="8" required
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <!-- TELEFONO -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Teléfono *</label>
          <input type="text" name="telefono" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <!-- TELEFONO EMERGENCIA -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Teléfono Emergencia *</label>
          <input type="text" name="telefono_emergencia" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <!-- CARRERA -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Carrera *</label>

          <div class="flex gap-2">
            <select name="id_carrera"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
              <option value="">-- Seleccione --</option>
              <option value="1">Desarrollo de Software</option>
              <option value="2">Electrónica</option>
            </select>

            <!-- BOTON AGREGAR -->
            <button type="button"
              class="bg-blue-500 text-white px-3 rounded hover:bg-blue-600">
              +
            </button>
          </div>
        </div>

        <!-- HORARIO -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Horario *</label>
          <input type="text" name="horario" required
            placeholder="Ej: 08:00 - 12:00"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <!-- FECHA REGISTRO -->
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Fecha Registro</label>
          <input type="date" name="fecha_registro"
            value="<?= date('Y-m-d') ?>"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
        </div>

      </div>

      <!-- BOTON -->
      <div class="pt-2">
        <button type="submit"
          class="bg-teal-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-teal-700 transition">
          Registrar Practicante
        </button>
      </div>

    </form>

  </div>

</div>
