function showForm(formId) {
  document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
  document.getElementById(formId).classList.add("active");
}

// Mostrar el formulario de registro al cargar la página
window.onload = function() {
  showForm('register-form');
};
