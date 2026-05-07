<?php
    //FUNCIÓN PARA MOSTRAR ESTRELLAS DE CALIFICACIÓN
    function mostrarEstrellas($nota) {
        $html = "";
        $notaEntera = round($nota);

        for ($i = 1; $i <= 10; $i++) {
            $color = ($i <= $notaEntera) ? "#f5b50a" : "#444";
            $html .= "<i class='fas fa-star' style='color: $color;'></i>";
        }

        return $html;
    }
?>
