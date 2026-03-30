<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Donación</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            width: 100%;
            height: 100%;
        }
        .background-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .content-container {
            position: absolute;
            top: 35%;
            left: 10%;
            right: 10%;
            text-align: center;
            color: #000;
            font-size: 18px;
            line-height: 1.6;
        }
        .bold {
            font-weight: bold;
        }
        .folio {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 10px;
            color: #555;
        }
        .atentamente {
            margin-top: 60px;
            font-weight: bold;
            font-size: 18px;
        }
        .titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <img src="{{ $fondo_imagen }}" class="background-image" alt="Fondo Certificado">
    </div>

    <!-- Opcional: Mostrar folio si se desea -->
    <!-- <div class="folio">Folio: {{ $certificado_uuid }}</div> -->

    <div class="content-container">
        <div class="titulo">
            ACTA DE RECEPCIÓN DE DONACIÓN
        </div>
        <p>
            En la ciudad de La Paz el <span class="bold">{{ $donacion_fecha_texto }}</span>, 
            <span class="bold">{{ strtoupper($donante_nombre) }}</span> HACE TRANSFERENCIA EN CALIDAD DE DONACION 
            a la Fundación Nuestra Esperanza de la suma de Bs <span class="bold">{{ $donacion_monto }}</span> 
            <br>
            Son: <span class="bold">{{ $donacion_monto_letras }} Bolivianos</span>
        </p>
        
        <p style="margin-top: 20px;">
            Donación que servirá para apoyar a los proyectos de la Fundación y apoyo al albergue, 
            agradecemos muchísimo su gesto de solidaridad y generosidad.
        </p>

        <div class="atentamente">
            Atentamente,
        </div>
    </div>
</body>
</html>