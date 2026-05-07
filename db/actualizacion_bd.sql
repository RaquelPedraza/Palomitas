-- Seguridad de Login
ALTER TABLE usuarios 
ADD intentos_fallidos INT DEFAULT 0,
ADD bloqueado_hasta DATETIME NULL;

-- Sistema de Seguidores
CREATE TABLE seguidores (
    id_seguidor INT NOT NULL,
    id_seguido INT NOT NULL,
    fecha_seguimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_seguido) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);