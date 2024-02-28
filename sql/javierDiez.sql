DROP DATABASE IF EXISTS javierDiez;
CREATE DATABASE javierDiez;
USE javierDiez;

CREATE TABLE personas (
    per_cedula VARCHAR(255) PRIMARY KEY,
    per_nombres VARCHAR(255) NOT NULL,
    per_apellidos VARCHAR(255) NOT NULL,
    per_fechaNacimiento DATE NOT NULL,
    per_estado BOOLEAN NOT NULL, -- 1 TRABAJANDO  0 NO TRABAJA EN LA EMPRESA
    per_areaTrabajo VARCHAR(255) NOT NULL,
    per_correo VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
    id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usu_user VARCHAR(255) NOT NULL,
    usu_password VARCHAR(255) NOT NULL,
    usu_rol INT NOT NULL,
    usu_registro DATETIME NOT NULL,
    usu_cedula VARCHAR(255) NOT NULL,
    Foreign Key (usu_cedula) REFERENCES personas(per_cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE orden_disenio(
    od_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_responsable VARCHAR(255) NOT NULL,  /*cedula*/
    od_producto VARCHAR(255) NOT NULL,
    od_marca VARCHAR(255) NOT NULL,
    od_fechaEntrega DATETIME NOT NULL,
    od_estado ENUM("PROPUESTA", "DESAPROBADA", "MATERIALIDAD", "OP") NOT NULL, 
    Foreign Key (od_responsable) REFERENCES personas(per_cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE registros_disenio(
    rd_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_id INT UNSIGNED NOT NULL,
    rd_diseniador VARCHAR(255) NOT NULL, /*cedula*/
    rd_hora_ini DATETIME NOT NULL,
    rd_hora_fin DATETIME NULL,
    rd_observaciones VARCHAR(255) NULL,
    Foreign Key (od_id) REFERENCES orden_disenio(od_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE ciudad_produccion (
    lu_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    lu_ciudad VARCHAR(255) NOT NULL
);

CREATE TABLE op (
    op_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    od_id INT UNSIGNED NOT NULL,
    lu_id INT UNSIGNED NOT NULL,
    op_cliente VARCHAR(255) NOT NULL,
    op_ciudad VARCHAR(255) NOT NULL,
    op_detalle VARCHAR(255) NOT NULL,
    op_registro DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    op_vendedor VARCHAR(255) NOT NULL,
    op_direccionLocal VARCHAR(255) NOT NULL,
    op_personaContacto VARCHAR(255) NOT NULL,
    op_telefono VARCHAR(255) NOT NULL,
    op_estado ENUM("OP CREADA", "OP EN PRODUCCIÓN", "OP PAUSADA", "OP FINALIZADA") NOT NULL,
    op_reproceso BOOLEAN NOT NULL,
    op_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    op_fechaFinalizacion DATETIME NOT NULL,
    Foreign Key (od_id) REFERENCES orden_disenio(od_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    Foreign Key (lu_id) REFERENCES ciudad_produccion(lu_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (proAre_porcentaje <= 100)
);/*AUTO_INCREMENT = 11353   antes del ;*/

    /*op_observaciones*/
CREATE TABLE op_observaciones (
    op_id INT UNSIGNED NOT NULL,
    opOb_estado VARCHAR(255) NOT NULL,
    opOb_obsevacion VARCHAR(255) NOT NULL,
    opOb_fecha DATETIME NOT NULL,
    Foreign Key (op_id) REFERENCES op(op_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE planos (
    pla_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    op_id INT UNSIGNED NOT NULL,
    pla_numero INT UNSIGNED NOT NULL,
    pla_estado ENUM("ACTIVO", "PAUSADO", "ANULADO", "CONCLUIDO") NOT NULL,
    pla_reproceso BOOLEAN NOT NULL, /* 0 no es reproceso, 1 si es reproceso */
    pla_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    Foreign Key (op_id) REFERENCES op(op_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (proAre_porcentaje <= 100)
);

    /* observaciones */
CREATE TABLE pla_observaciones (
    pla_id INT UNSIGNED NOT NULL,
    plaOb_estado VARCHAR(255) NOT NULL,
    plaOb_obsevacion VARCHAR(255) NOT NULL,
    plaOb_fecha DATETIME NOT NULL,
    Foreign Key (pla_id) REFERENCES planos(pla_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE produccion (
    pro_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pla_id INT UNSIGNED NOT NULL,
    pro_fecha DATETIME NOT NULL,
    Foreign Key (pla_id) REFERENCES planos(pla_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

    /* areaaaaaaaas */
CREATE TABLE pro_areas (
    pro_id INT UNSIGNED NOT NULL,
    proAre_detalle VARCHAR(255) NOT NULL,
    proAre_fechaIni VARCHAR(255) NOT NULL,
    proAre_fechaFin DATETIME NOT NULL,
    proAre_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    Foreign Key (pro_id) REFERENCES produccion(pro_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (proAre_porcentaje <= 100)
);

CREATE TABLE registro (
    reg_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pro_id INT UNSIGNED NOT NULL,
    reg_fecha DATETIME NOT NULL,
    reg_cedula VARCHAR(255) NOT NULL,
    reg_observacion VARCHAR(255) NOT NULL,
    op_id INT UNSIGNED NOT NULL,
    pla_id INT UNSIGNED NOT NULL,
    Foreign Key (pro_id) REFERENCES produccion(pro_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

/* sub tablas de registro */
CREATE TABLE registro_produccion (
    reg_id INT UNSIGNED NOT NULL,
    reg_porcentaje DECIMAL(5, 2) UNSIGNED NOT NULL,
    proAre_detalle VARCHAR(255) NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CHECK (proAre_porcentaje <= 100)
);
CREATE TABLE registro_reproceso (
    reg_id INT UNSIGNED NOT NULL,
    reg_reproceso BOOLEAN NOT NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
CREATE TABLE registro_empleado (
    reg_id INT UNSIGNED NOT NULL,
    reg_fechaFin DATETIME NOT NULL,
    reg_logistica BOOLEAN NOT NULL,
    reg_areaTrabajo VARCHAR(255) NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
/* atributo compuesto de registro_empleado */
CREATE TABLE registro_empleado_actividades (
    reg_id INT UNSIGNED NOT NULL,
    reg_detalle VARCHAR(255) NULL,
    Foreign Key (reg_id) REFERENCES registro(reg_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

CREATE TABLE notificaciones (
    noti_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    noti_cedula VARCHAR(255) NOT NULL, /* cedula */
    noti_fecha DATETIME NOT NULL,
    noti_detalle VARCHAR(255) NOT NULL,
    noti_destinatario INT NOT NULL, /* usando los roles como destinatarios */
    Foreign Key (noti_cedula) REFERENCES personas(per_cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
CREATE TABLE notificaciones_accionales (
    noti_id INT UNSIGNED NOT NULL,
    notiAc_estado BOOLEAN NOT NULL,  /* 0 sin notificacion, 1 con notificacion */
    notiAc_referencia VARCHAR(255) NOT NULL,
    Foreign Key (noti_id) REFERENCES notificaciones(noti_id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);

/*sub tabla notificaciones accionales*/

CREATE TABLE kardex (
    kar_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kar_cedula VARCHAR(255) NOT NULL,
    kar_accion VARCHAR(255) NOT NULL,
    kar_tabla VARCHAR(255) NOT NULL,
    kar_idRow VARCHAR(255) NOT NULL,
    kar_fecha DATETIME NOT NULL,
    Foreign Key (kar_cedula) REFERENCES personas(per_cedula)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
);
