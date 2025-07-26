<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta Médica - {{ $receta->paciente->persona->nombre_completo }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .receta-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .btn-print {
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-print:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Receta</button>
    </div>
    
    <div class="receta-container">
        @include('components.recetario-preview', [
            'medico' => $receta->medico,
            'receta' => $receta
        ])
        
        <!-- Pie de página con información adicional -->
        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 15px;">
            <p style="margin: 0;">Receta generada el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
            @if($receta->medico->persona)
            <p style="margin: 5px 0 0 0;">Dr. {{ $receta->medico->persona->nombre_completo }}</p>
            @endif
        </div>
    </div>

    <script>
        // Auto-focus para impresión
        window.onload = function() {
            document.querySelector('.btn-print').focus();
        }
    </script>
</body>
</html>
