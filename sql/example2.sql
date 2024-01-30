/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     30/01/2024 9:54:26                           */
/*==============================================================*/
DROP DATABASE IF EXISTS example2;

CREATE DATABASE example2;

USE example2;
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
   constraint PK_PERSONAS primary key (CEDULA)
);
INSERT INTO PERSONAS (CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO)
VALUES ('1728563592', 'Jean', 'Cedeno', '1990-01-15', 1, 'Tics');
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
VALUES (1, '1728563592', 'jeanC', '$2y$10$jeTbyOelKGtqXlEktSx7cei0UvlLj9uvjOQzJA3DV66AeOdfKLkxS', 1, CURRENT_TIMESTAMP);
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
   IDACTIVIDAD        int AUTO_INCREMENT not null,
   IDREGISTRO           int,
   ACTDETALLE           varchar(255) not null,
     USER_ID INT NOT NULL,
   Foreign Key (USER_ID) REFERENCES USUARIOS(ID_USER),
   constraint PK_ACTIVIDADES primary key (IDACTIVIDAD)
);
/*==============================================================*/
/* Index: ACTIVIDADES_PK                                        */
/*==============================================================*/
create unique index ACTIVIDADES_PK on ACTIVIDADES (
IDACTIVIDAD ASC
);
/*==============================================================*/
/* Index: RELATIONSHIP_6_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_6_FK on ACTIVIDADES (
IDREGISTRO ASC
);

/*==============================================================*/
/* Table: KARDEX                                                */
/*==============================================================*/
create table KARDEX
(
   IDKARDEX             int AUTO_INCREMENT  not null,
   ID_USERKADEXX        int not null,
   KARACCION            int not null,
   KARTABLA             char(10) not null,
   KARIDROW             int not null,
   constraint PK_KARDEX primary key (IDKARDEX)
);
/*==============================================================*/
/* Index: KARDEX_PK                                             */
/*==============================================================*/
create unique index KARDEX_PK on KARDEX (
IDKARDEX ASC
);
/*==============================================================*/
/* Table: LUGARPRODUCCION                                       */
/*==============================================================*/
create table LUGARPRODUCCION
(
   IDLUGAR              int AUTO_INCREMENT  not null,
   CIUDAD               char(16) not null,
  constraint PK_LUGARPRODUCCION primary key (IDLUGAR)
);
/*==============================================================*/
/* Index: LUGARPRODUCCION_PK                                    */
/*==============================================================*/
create unique index LUGARPRODUCCION_PK on LUGARPRODUCCION (
IDLUGAR ASC
);

/*==============================================================*/
/* Table: OP                                                    */
/*==============================================================*/
create table OP
(
   IDOP                 int AUTO_INCREMENT  not null,
   CEDULA               char(10),
   IDLUGAR              int,
   OPCLIENTE            char(50) not null,
   OPCIUDAD             varchar(255) not null,
   OPDETALLE            varchar(255) not null,
   OPREGISTRO           datetime not null,
   OPNOTIFICACIONCORREO datetime not null,
   OPVENDEDOR           char(10) not null,
   OPDISENIADOR         char(10) not null,
   OPDIRECCIONLOCAL     varchar(255) not null,
   OPPERESONACONTACTO   varchar(100),
   TELEFONO             char(10),
   OPOBSERAVACIONES     varchar(255),
   OPESTADO             char(25),
   constraint PK_OP primary key (IDOP)
);
/*==============================================================*/
/* Index: OP_PK                                                 */
/*==============================================================*/
create unique index OP_PK on OP (
IDOP ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_2_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_2_FK on OP (
CEDULA ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_7_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_7_FK on OP (
IDLUGAR ASC
);

/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS
(
   IDPLANO              int AUTO_INCREMENT  not null,
   IDOP                 int,
   PLANNUMERO           int not null,
    constraint PK_PLANOS primary key (IDPLANO)
);
/*==============================================================*/
/* Index: PLANOS_PK                                             */
/*==============================================================*/
create unique index PLANOS_PK on PLANOS (
IDPLANO ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_3_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_3_FK on PLANOS (
IDOP ASC
);
/*==============================================================*/
/* Table: PRODUCCION                                            */
/*==============================================================*/
create table PRODUCCION
(
   IDPRODUCCION          int AUTO_INCREMENT  not null,
   IDPLANO              int,
   PROOBSERVACIONES     varchar(255) not null,
   PROAREA              int not null,
   PROPORCENTAJE        int not null,
   PROFECHA             datetime,
   constraint PK_PRODUCCION primary key (IDPRODUCCION)
);

/*==============================================================*/
/* Index: PRODUCCION_PK                                         */
/*==============================================================*/
create unique index PRODUCCION_PK on PRODUCCION (
IDPRODUCCION ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_4_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_4_FK on PRODUCCION (
IDPLANO ASC
);
/*==============================================================*/
/* Table: REGISTRO                                              */
/*==============================================================*/

create table REGISTROS
(
   IDREGISTRO           int AUTO_INCREMENT  not null,
   IDPRODUCCION          int,
   REGHORAINICIO        datetime not null,
   REGHORAFINAL         datetime,
   REGAVANCE            int,
   REGOBSERVACION       varchar(255),
   constraint PK_REGISTRO primary key (IDREGISTRO)
);
/*==============================================================*/
/* Index: REGISTROS_PK                                          */
/*==============================================================*/
create unique index REGISTROS_PK on REGISTROS (
IDREGISTRO ASC
);

/*==============================================================*/
/* Index: RELATIONSHIP_5_FK                                     */
/*==============================================================*/
create index RELATIONSHIP_5_FK on REGISTROS (
IDPRODUCCION ASC
);

alter table ACTIVIDADES
   add constraint FK_ACTIVIDA_RELATIONS_REGISTRO foreign key (IDREGISTRO)
      references REGISTROS (IDREGISTRO)
      on update restrict
      on delete restrict;

alter table OP
   add constraint FK_OP_RELATIONS_PERSONAS foreign key (CEDULA)
      references PERSONAS (CEDULA)
      on update restrict
      on delete restrict;

alter table OP
   add constraint FK_OP_RELATIONS_LUGARPRO foreign key (IDLUGAR)
      references LUGARPRODUCCION (IDLUGAR)
      on update restrict
      on delete restrict;

alter table PLANOS
   add constraint FK_PLANOS_RELATIONS_OP foreign key (IDOP)
      references OP (IDOP)
      on update restrict
      on delete restrict;

alter table PRODUCCION
   add constraint FK_PRODUCCI_RELATIONS_PLANOS foreign key (IDPLANO)
      references PLANOS (IDPLANO)
      on update restrict
      on delete restrict;

alter table REGISTROS
   add constraint FK_REGISTRO_RELATIONS_PRODUCCI foreign key (IDPRODUCCION)
      references PRODUCCION (IDPRODUCCION)
      on update restrict
      on delete restrict;

alter table USUARIOS
   add constraint FK_USUARIOS_RELATIONS_PERSONAS foreign key (CEDULA)
      references PERSONAS (CEDULA)
      on update restrict
      on delete restrict;

