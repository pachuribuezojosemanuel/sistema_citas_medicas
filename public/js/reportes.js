// Botón "Imprimir" (también permite "Guardar como PDF" desde el navegador)
document.querySelectorAll('[data-imprimir]').forEach(function (boton) {
    boton.addEventListener('click', function () { window.print(); });
});