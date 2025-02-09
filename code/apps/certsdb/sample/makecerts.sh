#!/bin/bash

mkdir -p certs
PASSWORD="1234"

NAMES=(
    "Juan Pérez" "María López" "Carlos Gómez" "Ana Rodríguez" "Pedro Martínez"
    "Sofía Ramírez" "Luis Fernández" "Carmen Torres" "Diego Castro" "Elena Moreno"
    "Andrés Vargas" "Patricia Ríos" "Gabriel Soto" "Fernando Álvarez" "Rosa Medina"
    "Héctor Herrera" "Laura Ortiz" "Javier Guzmán" "Beatriz Mendoza" "Manuel Navarro"
    "Clara Espinoza" "Alberto Vega" "Verónica León" "Francisco Domínguez" "Natalia Paredes"
    "Raúl Salazar" "Silvia Núñez" "Germán Flores" "Daniela Peña" "Esteban Reyes"
    "Isabel Cordero" "Rodrigo Acosta" "Camila Suárez" "Martín Rojas" "Lucía Del Valle"
    "José Ángel Fuentes" "Marta Ávila" "Oscar Pizarro" "Valentina Estrada" "Emilio Campos"
    "Andrea Garrido" "Felipe Araya" "Lorena Villalobos" "Cristóbal Miranda" "Julia Correa"
    "Tomás Cáceres" "Natalia Ocampo" "Agustín Muñoz" "Daniel Beltrán" "Victoria Solís"
)

ORGS=(
    "Tech Solutions S.A." "López Consultores Ltda." "Innovatech Inc." "Rodríguez & Asociados" "Martínez Global LLC"
    "Ramírez Consulting Group" "Fernández & Hijos S.L." "Torres Digital Services" "Castro Ingeniería S.A." "Moreno & Partners"
    "Vargas Technology" "Ríos Financial Consulting" "Soto & Co." "Álvarez Construcciones" "Medina Asesores"
    "Herrera Group LLC" "Ortiz IT Solutions" "Guzmán & Asociados" "Mendoza Design Studio" "Navarro Tech Solutions"
    "Espinoza Marketing" "Vega Industrial S.A." "León Creative" "Domínguez Electricidad S.L." "Paredes Innovación"
    "Salazar eCommerce" "Núñez Seguridad Informática" "Flores & Hermanos" "Peña Arquitectura" "Reyes Global Solutions"
    "Cordero Exportaciones" "Acosta Agroindustria" "Suárez Telecom" "Rojas & Asociados" "Del Valle Moda y Diseño"
    "Fuentes Logística Internacional" "Ávila Consultores" "Pizarro Medios Digitales" "Estrada Educación" "Campos Transporte S.A."
    "Garrido Tecnología" "Araya Software" "Villalobos Energía Renovable" "Miranda & Asociados" "Correa Producciones"
    "Cáceres & Co." "Ocampo Innovaciones" "Muñoz Inversiones" "Beltrán Sistemas Inteligentes" "Solís Consulting Group"
)

for i in {0..49}; do
    NAME="${NAMES[$i]}"
    ORG="${ORGS[$i]}"
    EMAIL="$(echo "$NAME" | awk '{print tolower($1)}').$(echo "$NAME" | awk '{print tolower($2)}')@example.com"
    EMAIL="$(echo "$NAME" | iconv -f UTF-8 -t ASCII//TRANSLIT | awk '{print tolower($1)}').$(echo "$NAME" | iconv -f UTF-8 -t ASCII//TRANSLIT | awk '{print tolower($2)}')@example.com"

    openssl genpkey -algorithm RSA -out certs/cert$i.key
    openssl req -new -key certs/cert$i.key -out certs/cert$i.csr -utf8 -subj "/C=US/ST=California/L=Los Angeles/O=$ORG/CN=$NAME/emailAddress=$EMAIL"
    openssl x509 -req -in certs/cert$i.csr -signkey certs/cert$i.key -out certs/cert$i.crt -days 365
    openssl pkcs12 -export -out certs/cert$i.p12 -inkey certs/cert$i.key -in certs/cert$i.crt -password pass:$PASSWORD -name "$NAME - $ORG" -legacy
done

for i in {0..9}; do
    mv certs/cert$i.p12 certs/cert0$i.p12
done
