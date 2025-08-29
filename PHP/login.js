// document.addEventListener('DOMContentLoaded', function () {
//     const loginForm = document.getElementById('login-form');
//     const errorMessage = document.getElementById('error-message');

//     loginForm.addEventListener('submit', function (e) {
//         e.preventDefault();

//         const formData = new FormData(loginForm);

//         fetch('login.php', {
//             method: 'POST',
//             body: formData,
//         })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 window.location.href = 'menu.php';
//             } else {
//                 errorMessage.innerHTML = data.message;
//             }
//         })
//         .catch(error => {
//             console.error('Error:', error);
//         });
//     });
// });

document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("login-form");
  const errorMessage = document.getElementById("error-message");

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(loginForm);

    fetch("login.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("🔍 RESPUESTA DEL SERVIDOR:", data);

        if (data.success) {
          if (data.sesionReemplazada) {
            console.log("✅ Sesión anterior detectada, mostrando modal...");

            const modal = new bootstrap.Modal(
              document.getElementById("modalSesionCerrada")
            );
            modal.show();

            document.getElementById("continuarBtn").onclick = () => {
              window.location.href = "menu.php";
            };
          } else {
            console.log("➡️ No hubo sesión anterior, redirigiendo directo.");
            window.location.href = "menu.php";
          }
        } else {
          console.log("❌ Login fallido:", data.message);
          errorMessage.innerHTML = `<span style="color: red;">${data.message}</span>`;
        }
      })
      .catch((error) => {
        console.error("⚠️ Error en la petición:", error);
        errorMessage.innerHTML = `<span style="color: red;">Error del servidor.</span>`;
      });
  });
});
