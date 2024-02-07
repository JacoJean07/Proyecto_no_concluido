/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     31/01/2024 9:39:31                           */
/*==============================================================*/
DROP DATABASE IF EXISTS example;

CREATE DATABASE example;

USE example;
/*==============================================================*/
/* Table: PERSONAS                                              */
/*==============================================================*/
create table PERSONAS
(
   CEDULA               char(10) not null,
   PERNOMBRES           varchar(100) not null,
   PERAPELLIDOS         varchar(100) not null,
   PERFECHANACIMIENTO    date not null,
   PERESTADO            bool not null,
   PERAREATRABAJO       char(25) not null,
   PERCORREO            varchar(150) not null,
   constraint PK_PERSONAS primary key (CEDULA)
);
INSERT INTO PERSONAS (CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO,PERCORREO)
VALUES ('1728563592', 'Jean', 'Cedeno', '1990-01-15', 1, 'Tics','example@example.com'),
('1750541730', 'lenin jeerson', 'puetate obando', '1998-02-17', '1', 'Diseno Grafico','puetateobando@gamil.com');

/*==============================================================*/
/* Index: PERSONAS_PK                                           */
/*==============================================================*/
create unique index PERSONAS_PK on PERSONAS (
CEDULA ASC
);
/*==============================================================*/
/* Table: USUARIOS                                              */
/*==============================================================*/
create table USUARIOS
(
   ID_USER              int AUTO_INCREMENT  not null,
   CEDULA               char(10),
   USER                 char(10) not null,
   PASSWORD             varchar(255) not null,
   ROL                  int not null,
   REGISTRO             datetime not null,
   constraint PK_USUARIOS primary key (ID_USER)
);
INSERT INTO USUARIOS (ID_USER, CEDULA, USER, PASSWORD, ROL, REGISTRO)
VALUES (1, '1728563592', 'jeanC', '$2y$10$jeTbyOelKGtqXlEktSx7cei0UvlLj9uvjOQzJA3DV66AeOdfKLkxS', 1, CURRENT_TIMESTAMP),
(2, '1750541730', 'jefer', '$2y$10$WOw2hlz8Ts5Iu5ahK4cHq./ME6Fn/5B7JMug67gfs5NoGLT832Kpa', '1',CURRENT_TIMESTAMP);
/*==============================================================*/
/* Index: USUARIOS_PK                                           */
/*==============================================================*/
create unique index USUARIOS_PK on USUARIOS (
ID_USER ASC
);
/*==============================================================*/
/* Index: RELATIONSHIP_1_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_1_FK on USUARIOS (
CEDULA ASC
);

/*==============================================================*/
/* Table: ACTIVIDADES                                           */
/*==============================================================*/
create table ACTIVIDADES
(
   IDACTIVIDADES        int AUTO_INCREMENT not null,
   IDREGISTRO           int,
   ACTDETALLE           varchar(255) not null,
   primary key (IDACTIVIDADES)
);



/*==============================================================*/
/* Table: KARDEX                                                */
/*==============================================================*/
create table KARDEX
(
   IDKARDEX             int AUTO_INCREMENT not null,
   ID_USERKARDEX        int not null,
   KARUSER              VARCHAR(50),
   KARACCION            VARCHAR(20) not null,  -- 1 = CREO ; 2 = EDITO ; 3 ELIMINO ; 4 = RESTAURO
   KARTABLA             VARCHAR(50) not null,
   KARROW               VARCHAR(255) not null,
   KARFECHA DATETIME DEFAULT CURRENT_TIMESTAMP,
   primary key (IDKARDEX)
);

/*==============================================================*/
/* Table: LUGARPRODUCCION                                       */
/*==============================================================*/
create table LUGARPRODUCCION
(
   IDLUGAR              int AUTO_INCREMENT not null,
   CIUDAD               char(16) not null,
   primary key (IDLUGAR)
);

/*==============================================================*/
/* Table: OP                                                    */
/*==============================================================*/
create table OP
(
   IDOP                 int AUTO_INCREMENT not null,
   CEDULA               char(10),
   IDLUGAR              int,
   OPCLIENTE            char(50) not null,
   OPCIUDAD             varchar(255) not null,
   OPDETALLE            varchar(255) not null,
   OPREGISTRO           DATETIME DEFAULT CURRENT_TIMESTAMP,
   OPNOTIFICACIONCORREO datetime,
   OPVENDEDOR           char(20) not null,
   OPDIRECCIONLOCAL     varchar(255) not null,
   OPPERESONACONTACTO   varchar(100),
   TELEFONO             char(10),
   OPOBSERAVACIONES     varchar(255),
   OPESTADO             char(25),
   primary key (IDOP)
);


/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS
(
   IDPLANO              int AUTO_INCREMENT not null,
   IDOP                 int,
   PLANNUMERO           int not null,
   PLAESTADO            int not null,  /*1 = activo  2 = pausado 3 = anulado 4 = concluido*/
   PLANOTIFICACION      bool not null, /* 0 no hay notificacion, 1 si hay notificacion */
   PLAFECHANOTI            datetime,
   primary key (IDPLANO)
);

/*==============================================================*/
/* Table: PRODUCCION                                            */
/*==============================================================*/
create table PRODUCCION
(
  IDPRODUCION           int  AUTO_INCREMENT not null,
   IDPLANO              int,
   PROOBSERVACIONES     varchar(255) not null,
   PROFECHA             datetime not null,
   PROPORCENTAJE        int,
   primary key (IDPRODUCION)
);
/*==============================================================*/
/* Table: AREAS                                                 */
/*==============================================================*/
create table AREAS
(
   IDAREA               int AUTO_INCREMENT not null,
   IDPRODUCION          int,
   AREDETALLE           int not null,
   primary key (IDAREA)
);

/*==============================================================*/
/* Table: REGISTRO                                              */
/*==============================================================*/
create table REGISTRO
(
   IDREGISTRO           int AUTO_INCREMENT not null,
   IDAREA               int,
   REGHORAINICIO        datetime not null,
   REGHORAFINAL         datetime,
   REGAVANCE            int,
   REGOBSERVACION       varchar(255),
   REGAYUDA             bool not null,
   REGCEDULA            char(10) not null,
   primary key (IDREGISTRO)
);

/*==============================================================*/
/* Table: REGISTROPRODUCCION                                    */
/*==============================================================*/
create table REGISTROPRODUCCION
(
   IDREPR               int AUTO_INCREMENT not null,
   IDAREA               int,
   REPRFECHAHORA        datetime not null,
   REPRPORCENTAJE       int not null,
   REPROBSERVACIONES    varchar(255),
   primary key (IDREPR)
);

/*==============================================================*/
/* Table: LOGISTICA                                             */
/*==============================================================*/
create table LOGISTICA
(
   IDLOGISTICA          int AUTO_INCREMENT not null,
   IDPLANO              int,
   LOGAREATRABAJO       int not null,
   LOGHORAINCIO         datetime not null,
   LOGHORAFINAL         datetime,
   LOGOBSERVACIONES     varchar(255),
   LOGCEDULA            char(10) not null,
   LOGESTADO            char(25),
   primary key (IDLOGISTICA)
);

alter table ACTIVIDADES add constraint FK_RELATIONSHIP_7 foreign key (IDREGISTRO)
      references REGISTRO (IDREGISTRO) on delete restrict on update restrict;

alter table AREAS add constraint FK_RELATIONSHIP_8 foreign key (IDPRODUCION)
      references PRODUCCION (IDPRODUCION) on delete restrict on update restrict;

alter table LOGISTICA add constraint FK_RELATIONSHIP_11 foreign key (IDPLANO)
      references PLANOS (IDPLANO) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_2 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_3 foreign key (IDLUGAR)
      references LUGARPRODUCCION (IDLUGAR) on delete restrict on update restrict;

alter table PLANOS add constraint FK_RELATIONSHIP_4 foreign key (IDOP)
      references OP (IDOP) on delete restrict on update restrict;

alter table PRODUCCION add constraint FK_RELATIONSHIP_5 foreign key (IDPLANO)
      references PLANOS (IDPLANO) on delete restrict on update restrict;

alter table REGISTRO add constraint FK_RELATIONSHIP_10 foreign key (IDAREA)
      references AREAS (IDAREA) on delete restrict on update restrict;

alter table REGISTROPRODUCCION add constraint FK_RELATIONSHIP_9 foreign key (IDAREA)
      references AREAS (IDAREA) on delete restrict on update restrict;

alter table USUARIOS add constraint FK_RELATIONSHIP_1 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;