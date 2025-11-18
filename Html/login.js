loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  loginForm.classList.add("was-validated");

  const usuario = loginUser.value.trim();
  const password = loginPass.value.trim();

  const formData = new FormData();
  formData.append("usuario", usuario);
  formData.append("contraseña", password);

  const res = await fetch("Php/login.php", {
    method: "POST",
    body: formData
  });

  const data = await res.json();

  if (data.success) {

    localStorage.setItem("usuarioActual", data.usuario);
    localStorage.setItem("rolUsuario", data.rol);

    mensaje.textContent = "Acceso concedido";
    mensaje.style.color = "green";

    setTimeout(() => window.location.href = "principal.html", 700);

  } else {
    mensaje.textContent = data.message;
    mensaje.style.color = "red";

    loginUser.setCustomValidity("Error");
    loginPass.setCustomValidity("Error");
  }
});
