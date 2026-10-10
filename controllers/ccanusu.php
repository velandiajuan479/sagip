<?php
include('models/mcanusu.php');

$mcanusu = new Mcanusu();
$F = $mcanusu->getTotUsu("F");
$S = $mcanusu->getTotUsu("I");

// FUNCIÓN PARA BUSCAR ELEMENTOS 
function cantidad($ele,$tip){
    // EXTRAER LOS DATOS DE LAS CONSULTAS
    $mcanusu = new Mcanusu();
    $cicla = null;

    // VERIFICAR SI ES INGRESO O SALIDA
    if($tip=="I"){
        $cicla = $mcanusu->getCicla();
    }else{
        $cicla = $mcanusu->getSal();
    }

    // VERIFICAR SI VIENE CON DATOS LAS CONSULTAS
    if($cicla[0]['ciclas']){
        $string = $cicla[0]['ciclas']; // CONVERTIR CONSULTA EN UN STRING 
        $string=explode(";",$string); // CONVERTIR EL STRING EN UN VECTOR
        $can = [];

        // RECORRER EL VECTOR Y GUARDAR SOLO LOS ELEMENTOS DEL ID $ele 
        foreach ($string as $i) {
            if($mcanusu->solo($i,$ele)){
                $can[] = $i; 
            }
        $canti = count($can); // CONTAR CANTIDAD DE ELEMTOS CON ESE ID
        }
    }else{
        $canti = 0; // SI NO VIENEN MUESTRA UN 0
    }
    echo $canti;
}



//ELEMENTOS
// 71 - portatil
// 72 - portatil sena
// 73 - tablet
// 74 - tablet sena 
// 75 - carro
// 76 - moto 
// 77 - bicicleta
// 78 - scooter 
// 79 - otro 
// 80 - otro sena

function mostrarEntrSalForUsu($idusu) {
    global $mcanusu;  // Usa la instancia global del modelo

    // Llamamos la función del modelo
    $movimientos = $mcanusu->getEntrSalForFic($idusu);

    // Mostrar resultados (puedes devolver en JSON, o imprimir como prefieras)
    if (!empty($movimientos)) {
        foreach ($movimientos as $mov) {
            $tipo = ($mov['tipmin'] == 'I') ? 'Entrada' : 'Salida';
            echo "Usuario: {$mov['idusu']} - Tipo: {$tipo} - Fecha: {$mov['fechos']}<br>";
        }
    } else {
        echo "No hay movimientos registrados para el usuario con ID: $idusu hoy.";
    }
}

// CARGAR MOVIMIENTOS DEL USUARIO EN SESIÓN PARA LA VISTA
$movimientos = [];
if (isset($_SESSION['idusu'])) {
    $movimientos = $mcanusu->getEntrSalForFic($_SESSION['idusu']);
}

function usuFalt($idusu) {
    global $mcanusu;
    $mtots = new Mtots();

    // Total usuarios asignados a la ficha
    $total_usuarios = $mtots->getCantUsuFic($idusu);

    // Movimientos de hoy
    $movimientos = $mcanusu->getEntrSalForFic($idusu);

    // Calcular cantidad de entradas únicas
    $usuarios_que_llegaron = [];

    foreach ($movimientos as $mov) {
        if ($mov['tipmin'] == 'I') {
            $usuarios_que_llegaron[$mov['idusu']] = true; // Solo se registra una vez
        }
    }

    $cantidad_llegaron = count($usuarios_que_llegaron);
    $faltantes = $total_usuarios - $cantidad_llegaron;

    echo "<p><i class='fa-solid fa-user-check' style='color: #00af00; font-size: 21px;'></i> : <strong>$cantidad_llegaron</strong></p>";
    echo "<p><i class='fa-solid fa-user-xmark' style='color: red; font-size: 21px;'></i> : <strong>$faltantes</strong></p>";
}


?>