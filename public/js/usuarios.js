// Usuarios: sugerir el cargo según el rol elegido
document.addEventListener('DOMContentLoaded', function () {
    const rol   = document.getElementById('rol_id');
    const cargo = document.getElementById('cargo');
    if (!rol || !cargo) return;

    rol.addEventListener('change', function () {
        if (cargo.value.trim() === '' && rol.selectedIndex > 0) {
            cargo.value = rol.options[rol.selectedIndex].text.trim();
        }
    });
});