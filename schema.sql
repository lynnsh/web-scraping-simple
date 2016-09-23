drop table if exists recipes;

create table recipes (
    id integer primary key AUTO_INCREMENT,
    title varchar(50) default '' not null,
    reference varchar(250) default '' not null,
    description varchar(500) default '' not null,
    username varchar(50) default '' not null,
    views integer default 0 not null
);
