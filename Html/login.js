(() => {
  'use strict';

  // Perfil administrador fijo
  const adminProfile = {
    usuario: "admin",
    password: "admin123",
    rol: "admin"
  };

  // Referencias
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  const showRegister = document.getElementById("show-register");
  const showLogin = document.getElementById("show-login");
  const titulo = document.getElementById("titulo");
  const mensaje = document.getElementById("mensaje");

  // Alternar entre login y registro
  showRegister.addEventListener("click", (e) => {
    e.preventDefault();
    loginForm.classList.remove("active");
    registerForm.classList.add("active");
    titulo.textContent = "Crear Cuenta";
    mensaje.textContent = "";
    loginForm.classList.remove("was-validated");
    registerForm.classList.remove("was-validated");
    clearLoginValidity();
    clearRegisterValidity();
  });

  showLogin.addEventListener("click", (e) => {
    e.preventDefault();
    registerForm.classList.remove("active");
    loginForm.classList.add("active");
    titulo.textContent = "Iniciar Sesión";
    mensaje.textContent = "";
    loginForm.classList.remove("was-validated");
    registerForm.classList.remove("was-validated");
    clearLoginValidity();
    clearRegisterValidity();
  });

  // Bootstrap-style validation trigger (exact pattern)
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // Solo números en teléfono
  const tel = document.getElementById("reg-telefono");
  tel.addEventListener("input", () => {
    tel.value = tel.value.replace(/[^0-9]/g, "");
  });

  // Limpiar errores al escribir (para que rojo -> verde funcione tras was-validated)
  const loginUser = document.getElementById("login-user");
  const loginPass = document.getElementById("login-pass");
  [loginUser, loginPass].forEach(inp => {
    inp.addEventListener("input", () => {
      inp.setCustomValidity("");
      // Con was-validated aplicado, tu CSS cambiará rojo/verde según :valid/:invalid
    });
  });

  const regNombre = document.getElementById("reg-nombre");
  const regApellido = document.getElementById("reg-apellido");
  const regCorreo = document.getElementById("reg-correo");
  const regTelefono = document.getElementById("reg-telefono");
  const regPass = document.getElementById("reg-pass");
  const regConfirm = document.getElementById("reg-confirm");
  [regNombre, regApellido, regCorreo, regTelefono, regPass, regConfirm].forEach(inp => {
    inp.addEventListener("input", () => {
      inp.setCustomValidity("");
    });
  });

  function clearLoginValidity() {
    loginUser.setCustomValidity("");
    loginPass.setCustomValidity("");
  }
  function clearRegisterValidity() {
    regCorreo.setCustomValidity("");
    regTelefono.setCustomValidity("");
    regConfirm.setCustomValidity("");
  }

  // Registro
  registerForm.addEventListener("submit", (e) => {
    if (!registerForm.checkValidity()) return;

    const nombre = regNombre.value.trim();
    const apellido = regApellido.value.trim();
    const correo = regCorreo.value.trim();
    const telefono = regTelefono.value.trim();
    const password = regPass.value.trim();
    const confirm = regConfirm.value.trim();

    // Contraseñas idénticas
    if (password !== confirm) {
      e.preventDefault();
      regConfirm.setCustomValidity("Las contraseñas no coinciden");
      registerForm.classList.add("was-validated");
      return;
    } else {
      regConfirm.setCustomValidity("");
    }

    // Duplicados
    let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];
    const existe = usuarios.some(u => u.correo === correo || u.telefono === telefono);
    if (existe) {
      e.preventDefault();
      regCorreo.setCustomValidity("Correo ya registrado");
      regTelefono.setCustomValidity("Teléfono ya registrado");
      registerForm.classList.add("was-validated");
      return;
    } else {
      regCorreo.setCustomValidity("");
      regTelefono.setCustomValidity("");
    }

    // Guardar médico
    usuarios.push({ nombre, apellido, correo, telefono, password, rol: "medico" });
    localStorage.setItem("usuarios", JSON.stringify(usuarios));

    mensaje.textContent = "Cuenta creada con éxito. Ahora puedes iniciar sesión.";
    mensaje.style.color = "green";

    registerForm.reset();
    registerForm.classList.remove("active");
    loginForm.classList.add("active");
    titulo.textContent = "Iniciar Sesión";
    registerForm.classList.remove("was-validated");
  });

  // Login
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const usuario = loginUser.value.trim();
    const password = loginPass.value.trim();

    // Aplica Bootstrap visual
    loginForm.classList.add("was-validated");

    // Admin fijo
    if (usuario === adminProfile.usuario && password === adminProfile.password) {
      clearLoginValidity();
      localStorage.setItem("usuarioActual", adminProfile.usuario);
      localStorage.setItem("rolUsuario", adminProfile.rol);
      setTimeout(() => window.location.href = "Principal.html", 600);
      return;
    }

    // Buscar médicos registrados
    const usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];
    const cuenta = usuarios.find(
      u =>
        (u.correo === usuario || u.telefono === usuario || u.nombre === usuario) &&
        u.password === password
    );

    if (cuenta) {
      clearLoginValidity();
      localStorage.setItem("usuarioActual", cuenta.nombre);
      localStorage.setItem("rolUsuario", cuenta.rol);
      setTimeout(() => window.location.href = "Principal.html", 600);
    } else {
      // Marca inválido para que el borde se vea rojo (y se limpie al escribir)
      loginUser.setCustomValidity("Usuario incorrecto");
      loginPass.setCustomValidity("Contraseña incorrecta");
      loginForm.classList.add("was-validated");
    }
  });

  // Exponer logout si lo necesitas en otras páginas
  window.logoutUsuario = function () {
    localStorage.removeItem("usuarioActual");
    localStorage.removeItem("rolUsuario");
    window.location.href = "login.html";
  };
})();
