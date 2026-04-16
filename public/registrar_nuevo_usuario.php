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

    <form id="formRegistrarPracticante" method="POST" class="space-y-6">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Nombre Completo</label>
          <input type="text" name="nombre" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">DNI *</label>
          <input type="text" name="dni" maxlength="8" required
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Edad</label>
          <input type="number" name="edad" min="1" max="120"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Correo electronico</label>
          <input type="email" name="email"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 font-semibold mb-1">Dirección</label>
          <textarea name="direccion" rows="2"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Teléfono</label>
          <input type="text" name="telefono"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Teléfono Emergencia</label>
          <input type="text" name="telefono_emergencia"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Modalidad *</label>
          <select name="modalidad_horario" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
            <option value="">-- Seleccione modalidad --</option>
            <option value="Presencial">Presencial</option>
            <option value="Virtual">Virtual</option>
            <option value="Semipresencial">Semipresencial</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Carrera *</label>
          <div class="flex gap-2">
            <select name="id_carrera"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
              <option value="">-- Seleccione --</option>
              <option value="1">Desarrollo de Software</option>
              <option value="2">Diseño Gráfico</option>
              <option value="3">Electrónica</option>
              <option value="5">Mecatrónica</option>
              <option value="4">Redes y Comunicaciones</option>
            </select>
            <button type="button"
              class="bg-blue-500 text-white px-3 rounded hover:bg-blue-600">
              +
            </button>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Horario *</label>
          <input type="text" name="horario" required
            placeholder="Ej: Lunes y Mié 18:00 - 19:30"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Fecha Registro</label>
          <input type="date" name="fecha_registro"
            value="<?= date('Y-m-d') ?>"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
        </div>

        <div class="md:col-span-2">
          <label class="block text-gray-700 font-semibold mb-1">Notificar en emergencia a</label>
          <select name="notificar_emergencia"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
            <option value="">-- Seleccione --</option>
            <option value="Padre">Padre</option>
            <option value="Madre">Madre</option>
            <option value="Abuelo">Abuelo</option>
            <option value="Abuela">Abuela</option>
            <option value="Tío">Tío</option>
            <option value="Tía">Tía</option>
            <option value="Apoderado">Apoderado</option>
          </select>
        </div>

        <div>
        <label class="block text-gray-700 font-semibold mb-1">Observación</label>
        <textarea name="observacion" rows="3"
          placeholder="Ej: Tiene preferencias horarias y falta los sábados..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
      </div>

        <div class="md:col-span-2">
          <h1 class="text-2xl font-semibold text-gray-800 mb-3">Datos del Apoderado</h1>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Nombres del Apoderado</label>
          <input type="text" name="nombre_apoderado"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">DNI del Apoderado</label>
          <input type="text" name="dni_apoderado" maxlength="8"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Correo del Apoderado</label>
          <input type="email" name="correo_apoderado"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">Teléfono del Apoderado</label>
          <input type="text" name="telefono_apoderado"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 outline-none">
        </div>

      </div>

      <div class="pt-2">
        <button type="submit"
          class="bg-teal-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-teal-700 transition">
          Registrar Practicante
        </button>
      </div>

      <input type="hidden" name="action" value="registrar">

    </form>

    <!-- Mensaje de resultado -->
    <div id="mensajeResultado" class="mt-4"></div>

  </div>

</div>

<script>
document.getElementById('formRegistrarPracticante').addEventListener('submit', async function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const mensajeDiv = document.getElementById('mensajeResultado');

  try {
    const response = await fetch('../process/process_practicantes.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      mensajeDiv.innerHTML = `
        <div class="bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
          ✅ ${data.message}
        </div>
      `;
      this.reset();
      document.querySelector('input[name="fecha_registro"]').value = new Date().toISOString().split('T')[0];
      
      // Recargar la tabla de gestionar practicantes después de 1.5 segundos
      setTimeout(() => {
        document.querySelector('[data-tab="gestionar"]').click();
        location.reload();
      }, 1500);

    } else {
      mensajeDiv.innerHTML = `
        <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200">
          ❌ ${data.message}
        </div>
      `;
    }

  } catch (error) {
    mensajeDiv.innerHTML = `
      <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200">
        ❌ Error: ${error.message}
      </div>
    `;
  }
});
</script>
