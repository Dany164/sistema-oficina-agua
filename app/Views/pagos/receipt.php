<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <style>
        /*
         * SCRUM-29
         * Diseño de recibo de pago tipo ticket.
         * Solo se modifica la presentación.
         */

        @page {
            size: 80mm 230mm;
            margin: 10mm 4mm 15mm 4mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
        }

        body {
            width: 100%;
            font-family: "Courier New", Courier, monospace;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .recibo {
            width: 100%;
            max-width: 72mm;
            margin: 0 auto;
        }

        /* =========================
           ENCABEZADO
           ========================= */

        .encabezado {
            text-align: center;
            margin-bottom: 7mm;
        }

        .encabezado h1 {
            margin: 0 0 2mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .encabezado .subtitulo {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            font-weight: normal;
        }

        .separador {
            margin: 3mm 0;
            border-top: 1px dashed #000000;
            height: 0;
        }

        .separador-doble {
            margin: 3mm 0;
            border-top: 2px solid #000000;
            height: 0;
        }

        /* =========================
           DATOS GENERALES
           ========================= */

        .bloque {
            margin-bottom: 4mm;
        }

        .titulo-bloque {
            margin: 0 0 2mm;
            font-family: Georgia, "Times New Roman", Times, serif;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .dato {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 3mm;
            margin: 1.2mm 0;
        }

        .dato .etiqueta {
            flex: 0 0 auto;
            font-weight: bold;
        }

        .dato .valor {
            flex: 1;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .dato-vertical {
            margin: 1.5mm 0;
        }

        .dato-vertical .etiqueta {
            display: block;
            font-weight: bold;
        }

        .dato-vertical .valor {
            display: block;
            margin-top: 0.5mm;
        }

        /* =========================
           ESTADO ANULADO
           ========================= */

        .anulado {
            margin: 3mm 0;
            padding: 2.5mm 1mm;
            border: 1.5px solid #000000;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: bold;
        }

        /* =========================
           DETALLE DEL PAGO
           ========================= */

        .detalle-pago {
            margin-top: 4mm;
        }

        .linea-concepto {
            margin: 2mm 0;
        }

        .linea-concepto .concepto {
            display: block;
            font-weight: bold;
        }

        .linea-concepto .detalle {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin-top: 0.8mm;
        }

        .linea-concepto .monto {
            white-space: nowrap;
            text-align: right;
        }

        /* =========================
           TOTAL
           ========================= */

        .total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 4mm;
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 2px solid #000000;
            font-family: Georgia, "Times New Roman", Times, serif;
            font-weight: bold;
        }

        .total .etiqueta {
            font-size: 13pt;
        }

        .total .monto {
            font-size: 14pt;
            white-space: nowrap;
        }

        /* =========================
           OBSERVACIONES
           ========================= */

        .observaciones {
            margin-top: 4mm;
        }

        .observaciones .texto {
            margin-top: 1mm;
            overflow-wrap: anywhere;
        }

        /* =========================
           PIE
           ========================= */

        .pie {
            margin-top: 7mm;
            text-align: center;
            font-size: 7.5pt;
        }

        .pie p {
            margin: 1mm 0;
        }

        .pie .mensaje {
            margin-top: 4mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
        }

        /* =========================
           BOTONES
           ========================= */

        .acciones {
            width: 100%;
            max-width: 72mm;
            margin: 8mm auto 0;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 2.5mm 4mm;
            margin: 1mm;
            border: 1px solid #000000;
            border-radius: 3px;
            background: #ffffff;
            color: #000000;
            text-decoration: none;
            cursor: pointer;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
        }

        /* =========================
           IMPRESIÓN
           ========================= */

        @media print {

            body {
                background: #ffffff;
            }

            .recibo {
                max-width: none;
                margin: 0;
            }

            .acciones {
                display: none;
            }

            .anulado {
                color: #000000;
            }
        }

        /* =========================
           PANTALLA
           ========================= */

        @media screen {

            body {
                padding: 10mm 4mm;
            }

            .recibo {
                max-width: 72mm;
            }

        }
    </style>
</head>

<body>

    <div class="recibo">

        <!-- ENCABEZADO -->
        <div class="encabezado">
            <h1>OFICINA DE AGUA</h1>
            <p class="subtitulo">RECIBO DE PAGO</p>
        </div>

        <?php if ((int) $pago['anulado'] === 1): ?>
            <div class="anulado">
                ESTE RECIBO ESTÁ ANULADO
            </div>
        <?php endif; ?>

        <div class="separador"></div>

        <!-- INFORMACIÓN DEL PAGO -->
        <div class="bloque">

            <h2 class="titulo-bloque">Información del pago</h2>

            <div class="dato">
                <span class="etiqueta">No. Recibo:</span>
                <span class="valor">
                    <?= esc($pago['numero_recibo']) ?>
                </span>
            </div>

            <div class="dato">
                <span class="etiqueta">Fecha:</span>
                <span class="valor">
                    <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                </span>
            </div>

            <div class="dato">
                <span class="etiqueta">Método:</span>
                <span class="valor">
                    <?= esc($pago['metodo']) ?>
                </span>
            </div>

        </div>

        <div class="separador"></div>

        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="bloque">

            <h2 class="titulo-bloque">Cliente</h2>

            <div class="dato-vertical">
                <span class="etiqueta">Nombre:</span>
                <span class="valor">
                    <?= esc($pago['cliente']) ?>
                </span>
            </div>

        </div>

        <div class="separador"></div>

        <!-- INFORMACIÓN DEL SERVICIO -->
        <div class="bloque">

            <h2 class="titulo-bloque">Servicio</h2>

            <div class="dato">
                <span class="etiqueta">Contador:</span>
                <span class="valor">
                    <?= esc($pago['numero_registro']) ?>
                </span>
            </div>

            <div class="dato">
                <span class="etiqueta">Lectura:</span>
                <span class="valor">
                    #<?= esc($pago['lectura_id']) ?>
                </span>
            </div>

        </div>

        <div class="separador"></div>

        <!-- DETALLE DEL COBRO -->
        <div class="bloque detalle-pago">

            <h2 class="titulo-bloque">Detalle del cobro</h2>

            <div class="linea-concepto">

                <span class="concepto">
                    Servicio de agua
                </span>

                <div class="detalle">
                    <span>
                        Lectura #<?= esc($pago['lectura_id']) ?>
                    </span>

                    <span class="monto">
                        Q.<?= number_format((float) $pago['monto'], 2, ',', '.') ?>
                    </span>
                </div>

            </div>

        </div>

        <!-- TOTAL -->
        <div class="total">

            <span class="etiqueta">
                TOTAL
            </span>

            <span class="monto">
                Q.<?= number_format((float) $pago['monto'], 2, ',', '.') ?>
            </span>

        </div>

        <!-- OBSERVACIONES -->
        <?php if (! empty($pago['observaciones'])): ?>

            <div class="observaciones">

                <div class="separador"></div>

                <div class="dato-vertical">

                    <span class="etiqueta">
                        Observaciones:
                    </span>

                    <span class="texto">
                        <?= esc($pago['observaciones']) ?>
                    </span>

                </div>

            </div>

        <?php endif; ?>

        <!-- PIE -->
        <div class="pie">

            <div class="separador"></div>

            <p>
                Gracias por realizar su pago.
            </p>

            <p class="mensaje">
                Documento generado por el sistema.
            </p>

        </div>

    </div>

    <!-- ACCIONES: NO SE IMPRIMEN -->
    <div class="acciones">

        <button
            type="button"
            class="btn"
            onclick="window.print()">
            Imprimir recibo
        </button>

        <button
            type="button"
            class="btn"
            onclick="volverAPagos()">
            Volver a pagos
        </button>

    </div>

    <script>
        function volverAPagos() {
            if (window.opener && !window.opener.closed) {
                window.close();

                setTimeout(function() {
                    if (!window.closed) {
                        window.location.href = "<?= base_url('pagos') ?>";
                    }
                }, 100);
            } else {
                window.location.href = "<?= base_url('pagos') ?>";
            }
        }
    </script>

</body>

</html>