<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso denegado — Clínica Vida Salud</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; color: #1f2937;
               display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .caja { background: #fff; padding: 2.5rem; border-radius: .75rem; text-align: center;
                box-shadow: 0 1px 3px rgba(0,0,0,.1); max-width: 420px; }
        h1 { font-size: 1.5rem; margin: 0 0 .5rem; }
        p  { color: #6b7280; margin: 0 0 1.5rem; }
        a  { background: #4f46e5; color: #fff; padding: .6rem 1.2rem; border-radius: .4rem; text-decoration: none; }
    </style>
</head>
<body>
    <div class="caja">
        <h1>Acceso denegado</h1>
        <p>No tiene permiso para acceder a esta sección.</p>
        <a href="{{ url('/dashboard') }}">Volver al inicio</a>
    </div>
</body>
</html>