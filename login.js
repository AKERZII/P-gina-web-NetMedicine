// Referencias a elementos del DOM
const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");
const showRegister = document.getElementById("show-register");
const showLogin = document.getElementById("show-login");
const titulo = document.getElementById("titulo");

// Cambiar entre login y registro
showRegister.addEventListener("click", (e) => {
  e.preventDefault();
  loginForm.classList.remove("active");
  registerForm.classList.add("active");
  titulo.textContent = "Crear Cuenta";
});

showLogin.addEventListener("click", (e) => {
  e.preventDefault();
  registerForm.classList.remove("active");
  loginForm.classList.add("active");
  titulo.textContent = "Iniciar Sesión";
});

// ✅ Permitir solo números en el campo de teléfono
const telefonoInput = registerForm.querySelector('input[type="tel"]');
if (telefonoInput) {
  telefonoInput.addEventListener("input", () => {
    telefonoInput.value = telefonoInput.value.replace(/[^0-9]/g, ""); // elimina todo lo que no sea número
  });
}

// Registrar usuario
registerForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const inputs = registerForm.querySelectorAll("input");
  const nombre = inputs[0].value.trim();
  const apellido = inputs[1].value.trim();
  const correo = inputs[2].value.trim();
  const telefono = inputs[3].value.trim();
  const password = inputs[4].value.trim();
  const confirm = inputs[5].value.trim();

  // ✅ Validación de contraseñas (al final)
  if (password !== confirm) {
    marcarError(inputs[4]);
    marcarError(inputs[5]);
    mostrarMensaje("Las contraseñas no coinciden", "error");
    return;
  }

  let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

  // Verifica si el usuario o correo ya existe
  const existe = usuarios.some((u) => u.correo === correo || u.telefono === telefono);
  if (existe) {
    mostrarMensaje("El correo o teléfono ya están registrados", "error");
    return;
  }

  usuarios.push({ nombre, apellido, correo, telefono, password });
  localStorage.setItem("usuarios", JSON.stringify(usuarios));

  mostrarMensaje("Cuenta creada con éxito. Ahora puedes iniciar sesión.", "success");
  registerForm.reset();

  // Regresa al login automáticamente
  registerForm.classList.remove("active");
  loginForm.classList.add("active");
  titulo.textContent = "Iniciar Sesión";
});

// Iniciar sesión
loginForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const inputs = loginForm.querySelectorAll("input");
  const usuario = inputs[0].value.trim();
  const password = inputs[1].value.trim();

  const usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];
  const cuenta = usuarios.find(
    (u) =>
      (u.correo === usuario || u.telefono === usuario || u.nombre === usuario) &&
      u.password === password
  );

  limpiarValidaciones(inputs);

  if (cuenta) {
    marcarCorrecto(inputs[0]);
    marcarCorrecto(inputs[1]);
    mostrarMensaje("Inicio de sesión correcto", "success");
    localStorage.setItem("usuarioActual", cuenta.nombre);
    setTimeout(() => {
      window.location.href = "Principal.html"; // Redirige a tu página principal
    }, 1000);
  } else {
    marcarError(inputs[0]);
    marcarError(inputs[1]);
    mostrarMensaje("Usuario o contraseña incorrectos", "error");
  }
});


// ------------------------ FUNCIONES AUXILIARES ------------------------

function marcarError(input) {
  input.style.border = "2px solid red";
}

function marcarCorrecto(input) {
  input.style.border = "2px solid green";
}

function limpiarValidaciones(inputs) {
  inputs.forEach((i) => (i.style.border = "none"));
}

function mostrarMensaje(texto, tipo) {
  let msg = document.querySelector(".mensaje-validacion");
  if (!msg) {
    msg = document.createElement("p");
    msg.classList.add("mensaje-validacion");
    document.querySelector(".login-box").appendChild(msg);
  }
  msg.textContent = texto;
  msg.style.color = tipo === "error" ? "red" : "green";
  msg.style.fontWeight = "bold";
  msg.style.marginTop = "10px";
}
