<?php
include 'conexion.php'; // asegúrate que esté bien enlazado

$sql = "SELECT * FROM reloj";
$resultado = mysqli_query($conn, $sql);

$cardsHTML = "";

while ($row = mysqli_fetch_assoc($resultado)) {
    $id = $row['id_reloj']; // ← Asegúrate que en tu SELECT estás trayendo el id
    $nombre = $row['nombre'];
    $img = $row['img'];
    $precio = number_format($row['precio'], 0, '', '.');
    $descuento = $row['descuento'];
    $disponible = $row['disponible'];
    $imgRuta = "../" . $img;

    // Calcular precio con descuento si aplica
    if ($descuento > 0) {
        $precioConDescuento = number_format($row['precio'] - ($row['precio'] * $descuento), 0, '', '.');
    }

    $cardsHTML .= '<div class="contenedor-card">';
    $cardsHTML .= '<div class="cuadro-card">';
    $cardsHTML .= '<img src="' . $imgRuta . '" class="zoom-img">';
    $cardsHTML .= '<div class="texto-card">';
    $cardsHTML .= '<h3>' . htmlspecialchars($nombre) . '</h3>';

    // Mostrar precios
    if ($disponible == 0) {
        $cardsHTML .= '<div class="precio">';
        $cardsHTML .= ($descuento > 0)
            ? '<p>$' . $precioConDescuento . '.000</p><p class="descuento">AGOTADO</p>'
            : '<p>$' . $precio . '.000</p><p class="descuento">AGOTADO</p>';
        $cardsHTML .= '</div>';
    } else {
        if ($descuento > 0) {
            $cardsHTML .= '<div class="precio">';
            $cardsHTML .= '<p>$' . $precioConDescuento . '.000</p>';
            $cardsHTML .= '<p class="descuento">$' . $precio . '.000</p>';
            $cardsHTML .= '</div>';
        } else {
            $cardsHTML .= '<p>$' . $precio . '.000</p>';
        }
    }

    // Botón
    $cardsHTML .= '<div class="boton-wh">';
    if ($disponible == 0) {
        $cardsHTML .= '<button class="btn-whatsapp" disabled style="opacity:0.5;cursor:not-allowed;">Explorar modelo</button>';
    } else {
        $cardsHTML .= '<button class="btn-whatsapp" onclick="window.location.href=\'../informacion/informacion.html?id_reloj=' . $row['id_reloj'] . '\'">Explorar modelo</button>';
    }
    $cardsHTML .= '</div>';

    $cardsHTML .= '
        <svg class="ornamento" width="60" height="10" viewBox="0 0 60 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="0" y1="5" x2="20" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
            <polygon points="27,5 30,0 33,5 30,10" fill="#FFCF66"/>
            <polygon points="20,5 22,2.5 24,5 22,7.5" fill="#FFCF66"/>
            <polygon points="36,5 38,2.5 40,5 38,7.5" fill="#FFCF66"/>
            <line x1="40" y1="5" x2="60" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
        </svg>
    ';

    $cardsHTML .= '</div></div></div>';
}

echo $cardsHTML;
?>