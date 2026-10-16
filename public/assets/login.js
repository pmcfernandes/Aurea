function setupPasswordToggle() {
  const button = document.querySelector(".login-password-toggle");
  const input = document.querySelector("#password");

  if (!button || !input) return;

  button.addEventListener("click", () => {
    const isVisible = input.type === "text";
    input.type = isVisible ? "password" : "text";
    button.setAttribute("aria-pressed", String(!isVisible));
    button.setAttribute(
      "aria-label",
      isVisible ? "Mostrar palavra-passe" : "Ocultar palavra-passe",
    );
    button.querySelector("i")?.classList.toggle("fa-eye", isVisible);
    button.querySelector("i")?.classList.toggle("fa-eye-slash", !isVisible);
  });
}

setupPasswordToggle();
