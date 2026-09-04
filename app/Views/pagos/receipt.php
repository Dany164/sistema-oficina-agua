<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 30px;
            background: #f1f3f5;
            color: #212529;
        }
        .recibo {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }
        .encabezado {
            text-align: center;
            padding: 28px 30px;
            border-bottom: 3px solid #212529;
        }
        .encabezado h1 { margin: 0 0 6px; font-size: 26px; letter-spacing: 1px; }
        .encabezado p { margin: 0; color: #6c757d; font-size: 14px; }
        .contenido { padding: 25px 30px; }
        .seccion { margin-bottom: 20px; }
        .seccion:last-child { margin-bottom: 0; }
        .seccion h2 {
            margin: 0 0 12px;
            padding-bottom: 7px;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .dato {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 7px 0;
            font-size: 14px;
        }
        .dato strong { font-weight: 600; }
        .dato span { text-align: right; }
        .total {
            margin-top: 12px;
            padding: 15px 0 0;
            border-top: 2px solid #212529;
            font-size: 20px;
        }
        .total span { font-size: 22px; font-weight: bold; }
        .anulado {
            color: #dc3545;
            font-weight: bold;
            text-align: center;
            border: 2px solid #dc3545;
            padding: 10px;
        }
        .acciones { max-width: 720px; margin: 20px auto 0; text-align: center; }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            margin: 0 5px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-imprimir { background: #0d6efd; color: #ffffff; }
        .btn-volver { background: #6c757d; color: #ffffff; }
        @media print {
            @page { margin: 15mm; }
            body { padding: 0; background: #ffffff; }
            .recibo { max-width: none; border: none; border-radius: 0; box-shadow: none; }
            .acciones { display: none; }
            .encabezado { padding-top: 0; }
        }
        @media (max-width: 600px) {
            body { padding: 10px; }
            .contenido { padding: 20px; }
            .encabezado { padding: 22px 20px; }
            .dato { flex-direction: column; gap: 3px; }
            .dato span { text-align: left; }
        }
    </style>
</head>

<body>
    <div class="recibo">
        <div class="encabezado">
            <h1>OFICINA DE AGUA</h1>
            <p>Recibo de pago</p>
        </div>

        <div class="contenido">
            <?php if ((int) $pago['anulado'] === 1): ?>
                <div class="seccion anulado">ESTE RECIBO ESTÁ ANULADO</div>
            <?php endif; ?>

            <div class="seccion">
                <h2>Información del pago</h2>
                <div class="dato"><strong>Número de recibo:</strong><span><?= esc($pago['numero_recibo']) ?></span></div>
                <div class="dato"><strong>Fecha de pago:</strong><span><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></span></div>
                <div class="dato"><strong>Cliente:</strong><span><?= esc($pago['cliente']) ?></span></div>
                <div class="dato"><strong>Contador:</strong><span><?= esc($pago['numero_registro']) ?></span></div>
                <div class="dato"><strong>Concepto:</strong><span>Servicio de agua - lectura <?= esc($pago['lectura_id']) ?></span></div>
                <div class="dato"><strong>Método de pago:</strong><span><?= esc($pago['metodo']) ?></span></div>
                <div class="dato total"><strong>TOTAL:</strong><span>Q <?= number_format((float) $pago['monto'], 2) ?></span></div>
            </div>

            <?php if (! empty($pago['observaciones'])): ?>
                <div class="seccion">
                    <div class="dato"><strong>Observaciones:</strong><span><?= esc($pago['observaciones']) ?></span></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="acciones">
        <button type="button" class="btn btn-imprimir" onclick="window.print()">Imprimir recibo</button>
        <a href="<?= base_url('pagos') ?>" class="btn btn-volver">Volver a pagos</a>
    </div>
</body>

</html>
