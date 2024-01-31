/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     31/01/2024 9:39:31                           */
/*==============================================================*/


drop table if exists ACTIVIDADES;

drop table if exists AREAS;

drop table if exists KARDEX;

drop table if exists LUGARPRODUCCION;

drop table if exists OP;

drop table if exists PERSONAS;

drop table if exists PLANOS;

drop table if exists PRODUCCION;

drop table if exists REGISTRO;

drop table if exists USUARIOS;

/*==============================================================*/
/* Table: ACTIVIDADES                                           */
/*==============================================================*/
create table ACTIVIDADES
(
   IDACTIVIDADES        int not null,
   IDREGISTRO           int,
   ACTDETALLE           varchar(255) not null,
   primary key (IDACTIVIDADES)
);

/*==============================================================*/
/* Table: AREAS                                                 */
/*==============================================================*/
create table AREAS
(
   IDAREA               int not null,
   AREDETALLE           varchar(30) not null,
   primary key (IDAREA)
);

/*==============================================================*/
/* Table: KARDEX                                                */
/*==============================================================*/
create table KARDEX
(
   IDKARDEX             int not null,
   ID_USERKADEXX        int not null,
   KARACCION            int not null,
   KARTABLA             char(10) not null,
   KARIDROW             int not null,
   primary key (IDKARDEX)
);

/*==============================================================*/
/* Table: LUGARPRODUCCION                                       */
/*==============================================================*/
create table LUGARPRODUCCION
(
   IDLUGAR              int not null,
   CIUDAD               char(16) not null,
   primary key (IDLUGAR)
);

/*==============================================================*/
/* Table: OP                                                    */
/*==============================================================*/
create table OP
(
   IDOP                 int not null,
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
   primary key (IDOP)
);

/*==============================================================*/
/* Table: PERSONAS                                              */
/*==============================================================*/
create table PERSONAS
(
   CEDULA               char(10) not null,
   PERNOMBRES           varchar(100) not null,
   PERAPELLIDOS         varchar(100) not null,
   PERFECHANCIMINETO    date not null,
   PERESTADO            bool not null,
   PERAREATRABAJO       char(25) not null,
   primary key (CEDULA)
);

/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS
(
   IDPLANO              int not null,
   IDOP                 int,
   PLANNUMERO           int not null,
   primary key (IDPLANO)
);

/*==============================================================*/
/* Table: PRODUCCION                                            */
/*==============================================================*/
create table PRODUCCION
(
   IDPRODUCION          int not null,
   IDPLANO              int,
   IDAREA               int,
   PROOBSERVACIONES     varchar(255) not null,
   PROPORCENTAJE        int not null,
   PROFECHA             datetime not null,
   primary key (IDPRODUCION)
);

/*==============================================================*/
/* Table: REGISTRO                                              */
/*==============================================================*/
create table REGISTRO
(
   IDREGISTRO           int not null,
   IDPRODUCION          int,
   REGHORAINICIO        datetime not null,
   REGHORAFINAL         datetime,
   REGAVANCE            int,
   REGOBSERVACION       varchar(255),
   primary key (IDREGISTRO)
);

/*==============================================================*/
/* Table: USUARIOS                                              */
/*==============================================================*/
create table USUARIOS
(
   ID_USER              int not null,
   CEDULA               char(10),
   USER                 char(10) not null,
   PASSWORD             varchar(255) not null,
   ROL                  int not null,
   REGISTRO             datetime not null,
   primary key (ID_USER)
);

alter table ACTIVIDADES add constraint FK_RELATIONSHIP_7 foreign key (IDREGISTRO)
      references REGISTRO (IDREGISTRO) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_2 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;

alter table OP add constraint FK_RELATIONSHIP_3 foreign key (IDLUGAR)
      references LUGARPRODUCCION (IDLUGAR) on delete restrict on update restrict;

alter table PLANOS add constraint FK_RELATIONSHIP_4 foreign key (IDOP)
      references OP (IDOP) on delete restrict on update restrict;

alter table PRODUCCION add constraint FK_RELATIONSHIP_5 foreign key (IDPLANO)
      references PLANOS (IDPLANO) on delete restrict on update restrict;

alter table PRODUCCION add constraint FK_RELATIONSHIP_8 foreign key (IDAREA)
      references AREAS (IDAREA) on delete restrict on update restrict;

alter table REGISTRO add constraint FK_RELATIONSHIP_6 foreign key (IDPRODUCION)
      references PRODUCCION (IDPRODUCION) on delete restrict on update restrict;

alter table USUARIOS add constraint FK_RELATIONSHIP_1 foreign key (CEDULA)
      references PERSONAS (CEDULA) on delete restrict on update restrict;

