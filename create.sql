create database site;
use site;
create table users
(
    id int primary key auto_increment,
    login varchar(255) not null unique,
    phone varchar(255) not null unique,
    email varchar(255) not null unique,
    password varchar(255) not null
);