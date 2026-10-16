const sidebar = document.querySelector("#crm-sidebar");
const toggle = document.querySelector(".crm-menu-toggle");
const backdrop = document.querySelector(".crm-sidebar-backdrop");

function closeSidebar() {
  sidebar?.classList.remove("is-open");
  backdrop?.classList.remove("is-visible");
  backdrop?.setAttribute("hidden", "hidden");
  toggle?.setAttribute("aria-expanded", "false");
}

function openSidebar() {
  sidebar?.classList.add("is-open");
  backdrop?.removeAttribute("hidden");
  backdrop?.classList.add("is-visible");
  toggle?.setAttribute("aria-expanded", "true");
}

toggle?.addEventListener("click", () =>
  sidebar?.classList.contains("is-open") ? closeSidebar() : openSidebar(),
);

backdrop?.addEventListener("click", closeSidebar);

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeSidebar();
});
