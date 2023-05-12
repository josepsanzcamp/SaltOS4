
/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

"use strict";

// Main code
(function ($) {
    saltos.init_error();
    $("body").append(saltos.navbar({
        id:saltos.uniqid(),
        logo:"img/logo.svg",
        name:"SaltOS",
        menu:[{
            active:true,
            disabled:false,
            name:"Home",
            onclick:function () {
                alert(1);
            },
        },{
            active:false,
            disabled:false,
            name:"Link",
            onclick:function () {
                alert(2);
            },
        },{
            active:false,
            disabled:false,
            name:"Dropdown",
            menu:[{
                item:true,
                name:"Action",
                onclick:function () {
                    alert(3);
                },
            },{
                item:true,
                name:"Another action",
                onclick:function () {
                    alert(4);
                },
            },{
                divider:true,
            },{
                item:true,
                name:"Something else here",
                onclick:function () {
                    alert(5);
                },
            }]
        },{
            active:false,
            disabled:true,
            name:"Disabled",
            onclick:function () {
                alert(6);
            },
        }],
    }));
    $("body").append(`<br/><br/><br/>`);
    var container = saltos.form_field({
        type:"container",
    });
    var row = saltos.form_field({
        type:"row",
    });
    var tipos = [
        "text",
        "integer",
        "float",
        "color",
        "date",
        "time",
        "datetime",
        "hidden",
        "textarea",
        "ckeditor",
        "codemirror",
        "multiselect",
        "label",
        "checkbox",
        "switch",
        "button",
        "password",
        "file",
        "link",
        "select",
        "image",
        "excel",
        "pdfjs",
        "iframe",
        "table",
        "alert",
        "chartjs",
        "chartjs",
        "chartjs",
        "chartjs",
        "card",
    ];
    var modes = [
        "bar",
        "line",
        "pie",
        "doughnut",
    ];
    for (var i in tipos) {
        var col = saltos.form_field({
            type:"col",
            class:"col-xl-3 col-md-4 col-sm-6 mb-3",
        });
        var tipo = tipos[i];
        var valor = "";
        var rows = "";
        var clase = "";
        var size = "";
        var onclick = "";
        var mode = "";
        var multiple = "";
        var height = "";
        var data = "";
        var header = "";
        var footer = "";
        var divider = "";
        var image = "";
        var title = "";
        var text = "";
        var body = "";
        if (tipo == "textarea") {
            valor = "Texto de prueba\n\nAdios";
        }
        if (tipo == "ckeditor") {
            valor = "Texto de prueba<br/><br/>Adios";
        }
        if (tipo == "codemirror") {
            valor = "<xml>\n\t<tag>valor</tag>\n</xml>";
            mode = "xml";
        }
        if (tipo == "iframe") {
            valor = "img/favicon.svg";
            height = "600px";
        }
        if (tipo == "select") {
            rows = [
                {label:"Uno",value:1},
                {label:"Dos",value:2},
                {label:"Tres",value:3},
            ];
            valor = "2";
        }
        if (tipo == "multiselect") {
            rows = [
                {label:"Uno",value:1},
                {label:"Dos",value:2},
                {label:"Tres",value:3},
                {label:"Cuatro",value:4},
                {label:"Cinco",value:5},
                {label:"Seis",value:6},
            ];
            valor = "2,3,5";
        }
        if (tipo == "button") {
            clase = "btn-primary";
            valor = "Button text here";
        }
        if (tipo == "multiselect") {
            size = 5;
        }
        if (tipo == "button") {
            onclick = function () {
                alert("button onclick");
            };
        }
        if (tipo == "link") {
            valor = "https://www.saltos.org/portal/es/estadisticas";
            onclick = function () {
                window.open("https://www.saltos.org/portal/es/estadisticas");
            };
        }
        if (tipo == "file") {
            multiple = true;
        }
        if (tipo == "image") {
            valor = "img/favicon.svg";
        }
        if (tipo == "pdfjs") {
            valor = "data/files/test-josep-1.pdf";
        }
        if (tipo == "table") {
            var data = [
                ["Josep","Sanz",`<a href="#">654 123 789</a>`],
                ["Jordi","Company","654 123 789"],
                ["Andres","Diaz","654 123 789"],
            ];
            var header = ["Name","Surname","Phone"];
            var footer = ["","Total","3"];
            var divider = [false,true,true];
        }
        if (tipo == "alert") {
            clase = "alert-success";
            title = "The alert message!!!";
            text = "This text can be used as you want";
            body = `<hr/>More text here`;
        }
        if (tipo == "card") {
            image = "data/files/bootstrap-card.svg";
            header = "Cabecera";
            footer = "Pie";
            title = "Titulo";
            text = "Texto del card";
            body = `<a href="#">More info</a>`;
        }
        if (tipo == "chartjs") {
            mode = modes.shift();
            data = {
                labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
                datasets: [{
                    label: 'First round',
                    data: [8, 5, 3, 5, 2, 3],
                    borderWidth: 1
                },{
                    label: 'Second round',
                    data: [7, 6, 4, 6, 9, 6],
                    borderWidth: 1
                },{
                    label: 'Third round',
                    data: [6, 7, 6, 7, 5, 4],
                    borderWidth: 1
                }]
            };
        }
        var campo = saltos.form_field({
            type:tipo,
            id:"campo" + i,
            label:"Campo " + i + " (" + tipo + ")",
            placeholder:"Escriba aqui",
            value:valor,
            mode:mode,
            size:size,
            rows:rows,
            class:clase,
            onclick:onclick,
            multiple:multiple,
            height:height,
            data:data,
            header:header,
            footer:footer,
            divider:divider,
            image:image,
            title:title,
            text:text,
            body:body,
        });
        $(col).append(campo);
        $(row).append(col);
    }
    container.append(row);
    $("body").append(container);
}(jQuery));
