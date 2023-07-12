
/**
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
(function () {

    document.body.append(saltos.navbar({
        id:saltos.uniqid(),
        brand:{
            name:"SaltOS",
            logo:"img/logo_white.svg",
            width:25,
            height:25,
        },
        items:[
            saltos.menu({
                class:"navbar-nav me-auto mb-2 mb-lg-0",
                menu:[{
                    name:"Home",
                    disabled:false,
                    onclick:function () {
                        alert(1);
                    },
                },{
                    name:"Link",
                    disabled:false,
                    onclick:function () {
                        alert(2);
                    },
                },{
                    name:"Dropdown",
                    disabled:false,
                    menu:[{
                        name:"Action",
                        disabled:false,
                        onclick:function () {
                            alert(3);
                        },
                    },{
                        name:"Another action",
                        disabled:false,
                        onclick:function () {
                            alert(4);
                        },
                    },{
                        divider:true,
                    },{
                        name:"Something else here",
                        disabled:false,
                        onclick:function () {
                            alert(5);
                        },
                    }]
                },{
                    name:"Disabled",
                    disabled:false,
                    onclick:function () {
                        alert(6);
                    },
                }],
            }),
            function () {
                var obj = saltos.html(`<form class="d-flex" onsubmit="return false"></form>`);
                obj.append(saltos.form_field({
                    type:"text",
                    placeholder:"Search",
                }));
                obj.append(saltos.form_field({
                    type:"button",
                    value:"Search",
                    class:"btn-light mx-1",
                    onclick:function () {
                        alert(7);
                    },
                }));
                return obj;
            }(),
            saltos.menu({
                class:"navbar-nav mb-2 mb-lg-0",
                menu:[{
                    name:"Themes",
                    dropdown_menu_end:true,
                    menu:function () {
                        var menu = [
                            "bootstrap",
                            "cerulean",
                            "cosmo",
                            "cyborg",
                            "darkly",
                            "flatly",
                            "journal",
                            "litera",
                            "lumen",
                            "lux",
                            "materia",
                            "minty",
                            "morph",
                            "pulse",
                            "quartz",
                            "sandstone",
                            "simplex",
                            "sketchy",
                            "slate",
                            "solar",
                            "spacelab",
                            "superhero",
                            "united",
                            "vapor",
                            "yeti",
                            "zephyr"
                        ];
                        var current = document.querySelector("link[theme]").href;
                        for (var key in menu) {
                            var val = menu[key];
                            menu[key] = {
                                name:val,
                                active:current.includes(val),
                                onclick:function () {
                                    var theme = this.textContent;
                                    if (theme == "bootstrap") {
                                        document.querySelector("link[theme]").removeAttribute("integrity");
                                        document.querySelector("link[theme]").setAttribute("href", "lib/bootstrap/bootstrap.min.css");
                                    } else {
                                        document.querySelector("link[theme]").removeAttribute("integrity");
                                        document.querySelector("link[theme]").setAttribute("href", "lib/bootswatch/" + theme + ".min.css");
                                    }
                                    this.parentNode.querySelector("button.active").classList.remove("active");
                                    this.querySelector("button").classList.add("active");
                                },
                            }
                        }
                        return menu;
                    }()
                }, function () {
                    var theme = document.querySelector("html").getAttribute("data-bs-theme");
                    var icon = `<i class="bi bi-sun-fill"></i>`;
                    if (theme == "dark") {
                        icon = `<i class="bi bi-moon-stars-fill"></i>`;
                    }
                    return {
                        name:icon,
                        dropdown_menu_end:true,
                        menu:[{
                            name:`<i class="bi bi-sun-fill"></i> Light`,
                            active:(theme == ""),
                            onclick:function () {
                                document.querySelector("html").setAttribute("data-bs-theme", "");
                                this.parentNode.querySelector("button.active").classList.remove("active");
                                this.querySelector("button").classList.add("active");
                                this.parentNode.parentNode.querySelector("i").classList.remove("bi-moon-stars-fill");
                                this.parentNode.parentNode.querySelector("i").classList.add("bi-sun-fill");
                            },
                        },{
                            name:`<i class="bi bi-moon-stars-fill"></i> Dark`,
                            active:(theme == "dark"),
                            onclick:function () {
                                document.querySelector("html").setAttribute("data-bs-theme", "dark");
                                this.parentNode.querySelector("button.active").classList.remove("active");
                                this.querySelector("button").classList.add("active");
                                this.parentNode.parentNode.querySelector("i").classList.remove("bi-sun-fill");
                                this.parentNode.parentNode.querySelector("i").classList.add("bi-moon-stars-fill");
                            },
                        }]
                    };

                }()]
            }),
        ]
    }));

    document.body.append(saltos.html(`<br/><br/><br/>`));

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
        "tags",
        "button",
        "modal",
        "offcanvas",
        "toast",
        "textarea",
        "ckeditor",
        "codemirror",
        "multiselect",
        "label",
        "checkbox",
        "switch",
        "hidden",
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
        var rows = [];
        var clase = "";
        var size = "";
        var onclick = "";
        var mode = "";
        var multiple = "";
        var height = "";
        var data = [];
        var header = [];
        var footer = [];
        var divider = [];
        var image = "";
        var title = "";
        var text = "";
        var body = "";
        var datalist = [];
        var close = "";
        if (tipo == "text") {
            datalist = [
                "Uno",
                "Dos",
                "Tres",
                "Cuatro",
                "Cinco",
                "Seis",
                "Siete",
                "Ocho",
                "Nueve",
                "Diez",
            ];
        }
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
            valor = "apps/tester/files/philips-pm5544.svg";
            height = "500px";
        }
        if (tipo == "select") {
            rows = [
                {label:"Uno", value:1},
                {label:"Dos", value:2},
                {label:"Tres", value:3},
            ];
            valor = "2";
        }
        if (tipo == "multiselect") {
            rows = [
                {label:"Uno", value:1},
                {label:"Dos", value:2},
                {label:"Tres", value:3},
                {label:"Cuatro", value:4},
                {label:"Cinco", value:5},
                {label:"Seis", value:6},
            ];
            valor = [2,3,5].join(",");
            size = 5;
        }
        if (tipo == "button") {
            clase = "btn-primary";
            valor = "Button text here";
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
            valor = "apps/tester/files/philips-pm5544.svg";
        }
        if (tipo == "pdfjs") {
            valor = "apps/tester/files/philips-pm5544.pdf";
        }
        if (tipo == "table") {
            var data = [
                ["Josep", "Sanz", `<a href="#">654 123 789</a>`],
                ["Jordi", "Company", "654 123 789"],
                ["Andres", "Diaz", "654 123 789"],
            ];
            var header = ["Name", "Surname", "Phone"];
            var footer = ["", "Total", "3"];
            var divider = [false, true, true];
        }
        if (tipo == "alert") {
            clase = "alert-success";
            title = "The alert message!!!";
            text = "This text can be used as you want";
            body = `<hr/>More text here`;
            close = true;
        }
        if (tipo == "card") {
            image = "apps/tester/files/bootstrap-card.svg";
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
                },{
                    label: 'Second round',
                    data: [7, 6, 4, 6, 9, 6],
                },{
                    label: 'Third round',
                    data: [6, 7, 6, 7, 5, 4],
                }]
            };
        }
        if (tipo == "tags") {
            datalist = [
                "PHP",
                "JS",
                "CSS",
                "XML",
                "JavaScript",
                "GNU/Linux",
                "GNU",
                "Linut",
                "SaltOS",
                "RhinOS",
            ];
            valor = "SaltOS, PHP, JavaScript";
        }
        if (tipo == "modal") {
            tipo = "button";
            clase = "btn-primary";
            valor = "Modal test";
            onclick = function () {
                saltos.modal({
                    static:false,
                    //~ class:"modal-lg",
                    title:"Titulo",
                    close:"Cerrar",
                    body:`
                        <div>
                            Some text as placeholder. In real life you can have the elements you have chosen. Like, text, images, lists, etc.
                        </div>
                        <div class="dropdown mt-3">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Dropdown button
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </div>
                    `,
                    footer:function () {
                        var obj = saltos.html("<div></div>");
                        obj.append(saltos.form_field({
                            type:"button",
                            value:"Aceptar",
                            class:"btn-primary",
                            onclick:function () {
                                console.log("OK");
                                saltos.modal("close");
                            }
                        }));
                        obj.append(saltos.form_field({
                            type:"button",
                            value:"Cancelar",
                            class:"btn-primary ms-1",
                            onclick:function () {
                                console.log("KO");
                                saltos.modal("close");
                            },
                        }));
                        return obj;
                    }()
                });
            };
        }
        if (tipo == "offcanvas") {
            tipo = "button";
            clase = "btn-primary";
            valor = "Offcanvas test";
            onclick = function () {
                saltos.offcanvas({
                    static:false,
                    class:"offcanvas-start",
                    title:"Titulo",
                    close:"Cerrar",
                    body:`
                        <div>
                            Some text as placeholder. In real life you can have the elements you have chosen. Like, text, images, lists, etc.
                        </div>
                        <div class="dropdown mt-3">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Dropdown button
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </div>
                    `,
                });
            };
        }
        if (tipo == "toast") {
            tipo = "button";
            clase = "btn-primary";
            valor = "Toast test";
            onclick = function () {
                saltos.toast({
                    //class:"text-bg-primary",
                    close:"Cerrar",
                    title:"Hola mundo",
                    subtitle:"pues nada",
                    body:"Pues eso, hola mundo",
                });
            };
        }
        var campo = saltos.form_field({
            type:tipo,
            id:"campo" + i,
            label:"Campo " + i + " (" + tipo + ")",
            placeholder:"Escriba aqui",
            tooltip:"Tooltip " + i + " (" + tipo + ")",
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
            datalist:datalist,
            close:close,
        });
        col.append(campo);
        row.append(col);
    }
    container.append(row);
    document.body.append(container);

}());
