// Cargar opciones de medicamentos desde localStorage (sembradas por Farmacia)
(function cargarDatalist(){
  const meds = JSON.parse(localStorage.getItem("medicamentos")) || [];
  const dl = document.getElementById("lista-medicamentos");
  if (!dl) return;
  dl.innerHTML = meds.map(m => `<option value="${m.nombre}">${m.presentacion}</option>`).join('');
})();

// Guardar receta en localStorage
document.getElementById("receta-form")?.addEventListener("submit", function(e){
  e.preventDefault();

  const medicamento = document.getElementById("medicamento").value.trim();
  const cantidad = document.getElementById("cantidad").value.trim();
  const admin = document.getElementById("admin").value.trim();
  const periodo = document.getElementById("periodo").value.trim();
  const otro = document.getElementById("otro").value.trim();
  const correo = document.getElementById("correo").value.trim();

  if (!medicamento || !cantidad || !admin || !periodo || !correo) {
    document.getElementById("mensaje").textContent = "Completa los campos obligatorios.";
    document.getElementById("mensaje").style.color = "red";
    return;
  }

  const receta = {
    id: 'rec-' + Math.random().toString(36).slice(2,10),
    fecha: new Date().toISOString(),
    medicamento, cantidad, admin, periodo, otro, correo
  };

  const recetas = JSON.parse(localStorage.getItem("recetas")) || [];
  recetas.push(receta);
  localStorage.setItem("recetas", JSON.stringify(recetas));

  document.getElementById("mensaje").textContent =
    "✅ Receta guardada correctamente. El paciente debe adquirir el medicamento en su farmacia de preferencia.";
  document.getElementById("mensaje").style.color = "green";
  this.reset();
});
