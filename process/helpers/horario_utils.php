<?php

function normalizarTextoHorario($texto) {
    $texto = trim((string)$texto);
    $texto = mb_strtolower($texto, 'UTF-8');

    $reemplazos = [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n'
    ];

    return strtr($texto, $reemplazos);
}

function obtenerDiaSemanaEspanol($fecha) {
    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return null;
    }

    $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    $numeroDia = (int)date('N', $timestamp);

    return $dias[$numeroDia] ?? null;
}

function formatearHoraSimple($hora) {
    $hora = trim((string)$hora);

    if (preg_match('/^(\d{1,2}):(\d{2})/', $hora, $m)) {
        return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
    }

    return $hora;
}

function extraerHorarioPorFecha($horarioTexto, $fecha) {
    $diaOriginal = obtenerDiaSemanaEspanol($fecha);

    if (!$diaOriginal) {
        return [
            'tiene_horario' => false,
            'dia_semana' => null,
            'horario_hoy' => null,
            'hora_inicio' => null,
            'hora_fin' => null
        ];
    }

    $diaNormalizado = normalizarTextoHorario($diaOriginal);
    $horarioTexto = trim((string)$horarioTexto);

    if ($horarioTexto === '') {
        return [
            'tiene_horario' => false,
            'dia_semana' => $diaOriginal,
            'horario_hoy' => null,
            'hora_inicio' => null,
            'hora_fin' => null
        ];
    }

    $partes = array_map('trim', explode('|', $horarioTexto));

    /*
        FORMATO A:
        Modalidad | 13:55-15:00 | Lunes, Martes, Jueves
    */
    if (count($partes) >= 3) {
        $posibleRangoHora = $partes[1];
        $posiblesDias = $partes[2];

        if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $posibleRangoHora, $m)) {
            $dias = array_map('trim', explode(',', $posiblesDias));

            foreach ($dias as $dia) {
                if (normalizarTextoHorario($dia) === $diaNormalizado) {
                    $horaInicio = formatearHoraSimple($m[1]);
                    $horaFin = formatearHoraSimple($m[2]);

                    return [
                        'tiene_horario' => true,
                        'dia_semana' => $diaOriginal,
                        'horario_hoy' => $diaOriginal . ' | ' . $horaInicio . ' - ' . $horaFin,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin
                    ];
                }
            }
        }
    }

    /*
        FORMATO B:
        Modalidad | Lunes 11:11-14:22; Martes 11:11-14:22; Jueves 11:11-14:22

        También soporta:
        Presencial | Martes 11:11-14:32; Miercoles 11:11-15:22
    */
    $textoDespuesModalidad = $horarioTexto;

    if (count($partes) >= 2) {
        array_shift($partes);
        $textoDespuesModalidad = implode(' | ', $partes);
    }

    $regex = '/\b(lunes|martes|mi[eé]rcoles|jueves|viernes|s[aá]bado|domingo)\b\s+(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/iu';

    if (preg_match_all($regex, $textoDespuesModalidad, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $diaEncontrado = $match[1];

            if (normalizarTextoHorario($diaEncontrado) === $diaNormalizado) {
                $horaInicio = formatearHoraSimple($match[2]);
                $horaFin = formatearHoraSimple($match[3]);

                return [
                    'tiene_horario' => true,
                    'dia_semana' => $diaOriginal,
                    'horario_hoy' => $diaOriginal . ' | ' . $horaInicio . ' - ' . $horaFin,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin
                ];
            }
        }
    }

    return [
        'tiene_horario' => false,
        'dia_semana' => $diaOriginal,
        'horario_hoy' => null,
        'hora_inicio' => null,
        'hora_fin' => null
    ];
}