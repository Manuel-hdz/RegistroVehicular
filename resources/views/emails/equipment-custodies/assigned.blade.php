<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asignacion de resguardo</title>
</head>
<body style="font-family:Arial, Helvetica, sans-serif; color:#1f2937; line-height:1.45; margin:0; padding:24px; background:#f8fafc;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
        <tr>
            <td style="padding:20px;">
                <h2 style="margin:0 0 12px; font-size:20px; color:#065f46;">Se asigno un nuevo resguardo</h2>
                <p style="margin:0 0 16px;">
                    Se registro un resguardo de equipo para <strong>{{ $equipmentCustody->responsible }}</strong>.
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; width:40%; color:#6b7280;">Tipo de equipo</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">
                            {{ \App\Models\EquipmentCustody::typeLabels()[$equipmentCustody->equipment_type] ?? $equipmentCustody->equipment_type }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">Marca</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">{{ $equipmentCustody->brand }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">Modelo</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">{{ $equipmentCustody->model }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">Numero de serie</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">{{ $equipmentCustody->serial_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">Fecha de asignacion</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">{{ optional($equipmentCustody->assigned_at)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb; color:#6b7280;">Registro sistemas</td>
                        <td style="padding:8px 0; border-bottom:1px solid #e5e7eb;">{{ $equipmentCustody->registered_by_username }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#6b7280;">Accesorios</td>
                        <td style="padding:8px 0;">{{ $equipmentCustody->accessories ?: 'No especificados' }}</td>
                    </tr>
                </table>

                <p style="margin:18px 0 0; color:#6b7280; font-size:13px;">
                    Aviso generado automaticamente por el sistema de registro vehicular.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
