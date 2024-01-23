/*==============================================================*/
/* DBMS name:      Sybase SQL Anywhere 12                       */
/* Created on:     23/01/2024 14:02:25                          */
/*==============================================================*/


if exists(select 1 from sys.sysforeignkey where role='FK_ACTIVIDA_RELATIONS_REGISTRO') then
    alter table ACTIVIDADES
       delete foreign key FK_ACTIVIDA_RELATIONS_REGISTRO
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_OP_RELATIONS_PERSONAS') then
    alter table OP
       delete foreign key FK_OP_RELATIONS_PERSONAS
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_OP_RELATIONS_LUGARPRO') then
    alter table OP
       delete foreign key FK_OP_RELATIONS_LUGARPRO
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_PLANOS_RELATIONS_OP') then
    alter table PLANOS
       delete foreign key FK_PLANOS_RELATIONS_OP
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_PRODUCCI_RELATIONS_PLANOS') then
    alter table PRODUCCION
       delete foreign key FK_PRODUCCI_RELATIONS_PLANOS
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_REGISTRO_RELATIONS_PRODUCCI') then
    alter table REGISTROS
       delete foreign key FK_REGISTRO_RELATIONS_PRODUCCI
end if;

if exists(select 1 from sys.sysforeignkey where role='FK_USUARIOS_RELATIONS_PERSONAS') then
    alter table USUARIOS
       delete foreign key FK_USUARIOS_RELATIONS_PERSONAS
end if;

drop index if exists ACTIVIDADES.RELATIONSHIP_6_FK;

drop index if exists ACTIVIDADES.ACTIVIDADES_PK;

drop table if exists ACTIVIDADES;

drop index if exists KARDEX.KARDEX_PK;

drop table if exists KARDEX;

drop index if exists LUGARPRODUCCION.LUGARPRODUCCION_PK;

drop table if exists LUGARPRODUCCION;

drop index if exists OP.RELATIONSHIP_7_FK;

drop index if exists OP.RELATIONSHIP_2_FK;

drop index if exists OP.OP_PK;

drop table if exists OP;

drop index if exists PERSONAS.PERSONAS_PK;

drop table if exists PERSONAS;

drop index if exists PLANOS.RELATIONSHIP_3_FK;

drop index if exists PLANOS.PLANOS_PK;

drop table if exists PLANOS;

drop index if exists PRODUCCION.RELATIONSHIP_4_FK;

drop index if exists PRODUCCION.PRODUCCION_PK;

drop table if exists PRODUCCION;

drop index if exists REGISTROS.RELATIONSHIP_5_FK;

drop index if exists REGISTROS.REGISTROS_PK;

drop table if exists REGISTROS;

drop index if exists USUARIOS.RELATIONSHIP_1_FK;

drop index if exists USUARIOS.USUARIOS_PK;

drop table if exists USUARIOS;

/*==============================================================*/
/* Table: ACTIVIDADES                                           */
/*==============================================================*/
create table ACTIVIDADES 
(
   IDACTIVIDAD          integer                        not null,
   IDREGISTRO           integer                        null,
   ACTDETALLE           varchar(255)                   not null,
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
   IDKARDEX             integer                        not null,
   ID_USERKARDEX        integer                        not null,
   KARACCION            integer                        not null,
   KARTABLA             char(10)                       not null,
   KARIDROW             integer                        not null,
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
   IDLUGAR              integer                        not null,
   CIUDAD               char(16)                       not null,
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
   IDOP                 integer                        not null,
   CEDULA               char(10)                       null,
   IDLUGAR              integer                        null,
   OPCLIENTE            char(50)                       not null,
   OPCIUDAD             varchar(255)                   not null,
   OPDETALLE            varchar(255)                   not null,
   OPREGISTRO           timestamp                      not null,
   OPNOTIFICACIONCORREO timestamp                      not null,
   OPVENDEDOR           char(10)                       not null,
   OPDISEADOR           char(10)                       not null,
   OPDIRECCIONLOCAL     varchar(255)                   not null,
   OPPERSONACONTACTO    varchar(100)                   null,
   OPTELEFONO           char(15)                       null,
   OPOBSERVACIONES      varchar(255)                   null,
   OPESTADO             char(25)                       null,
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
/* Table: PERSONAS                                              */
/*==============================================================*/
create table PERSONAS 
(
   CEDULA               char(10)                       not null,
   PERNOMBRES           varchar(100)                   not null,
   PERAPELLIDOS         varchar(100)                   not null,
   PERFECHANACIMIENTO   date                           not null,
   PERESTADO            smallint                       not null,
   PERAREATRABAJO       char(25)                       not null,
   constraint PK_PERSONAS primary key (CEDULA)
);

/*==============================================================*/
/* Index: PERSONAS_PK                                           */
/*==============================================================*/
create unique index PERSONAS_PK on PERSONAS (
CEDULA ASC
);

/*==============================================================*/
/* Table: PLANOS                                                */
/*==============================================================*/
create table PLANOS 
(
   IDPLANO              integer                        not null,
   IDOP                 integer                        null,
   PLANUMERO            integer                        not null,
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
   IDPRODUCCION         integer                        not null,
   IDPLANO              integer                        null,
   PROOBSERVACIONES     varchar(255)                   not null,
   ATTRIBUTE_32         integer                        not null,
   PROPORCENTAJE        integer                        not null,
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
/* Table: REGISTROS                                             */
/*==============================================================*/
create table REGISTROS 
(
   IDREGISTRO           integer                        not null,
   IDPRODUCCION         integer                        null,
   REGHORAINICIA        timestamp                      not null,
   REGHORAFINAL         timestamp                      null,
   REGAVANCE            integer                        null,
   REGOBSERVACION       varchar(255)                   null,
   constraint PK_REGISTROS primary key (IDREGISTRO)
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

/*==============================================================*/
/* Table: USUARIOS                                              */
/*==============================================================*/
create table USUARIOS 
(
   ID_USER              integer                        not null,
   CEDULA               char(10)                       null,
   "USER"               char(10)                       not null,
   PASSWORD             varchar(255)                   not null,
   ROL                  integer                        not null,
   REGISTRO             timestamp                      not null,
   constraint PK_USUARIOS primary key (ID_USER)
);

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

