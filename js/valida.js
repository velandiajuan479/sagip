document.addEventListener("DOMContentLoaded", function () {
  const track = document.querySelector(".slider-track");

  if (!track || track.children.length === 0) return;

  const originalItems = Array.from(track.children);

  // Duplica las tarjetas para crear el efecto infinito
  originalItems.forEach(item => {
    track.appendChild(item.cloneNode(true));
  });

  let index = 0;
  let interval;

  function getStep() {
    const item = track.children[0];
    const gap = parseFloat(getComputedStyle(track).gap) || 0;

    return item.getBoundingClientRect().width + gap;
  }

  function moveSlider() {
    index++;

    track.style.transition = "transform 0.6s ease-in-out";
    track.style.transform = `translateX(-${index * getStep()}px)`;

    // Al llegar a la copia del primer artículo,
    // vuelve al inicio sin animación visible
    if (index === originalItems.length) {
      setTimeout(() => {
        track.style.transition = "none";
        track.style.transform = "translateX(0)";
        index = 0;
      }, 600);
    }
  }

  function startSlider() {
    interval = setInterval(moveSlider, 3000);
  }

  function stopSlider() {
    clearInterval(interval);
  }

  startSlider();

  // Pausa al pasar el mouse sobre el slider
  track.parentElement.addEventListener("mouseenter", stopSlider);
  track.parentElement.addEventListener("mouseleave", startSlider);

  // Recalcula la posición cuando cambia el tamaño de pantalla
  window.addEventListener("resize", () => {
    track.style.transition = "none";
    track.style.transform = `translateX(-${index * getStep()}px)`;
  });
});

/* ----------------------Datatables Inicio ----------- */
// Inicializar DataTables para las tablas de videos y secundarias
$(document).ready(function () {
  const dtOptions = {
    "language": {
      "lengthMenu": "Mostrar _MENU_ registros",
      "zeroRecords": "No se encontraron resultados",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ videos",
      "infoEmpty": "0 videos disponibles",
      "infoFiltered": "(filtrado de _MAX_ registros)",
      "sSearch": "Buscar:",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      }
    },
    "order": [[0, "desc"]],
    dom: 'Bfrtip',
    buttons: [
      { extend: 'copyHtml5', className: 'btn btn-sm btn-outline-secondary' },
      { extend: 'csvHtml5', className: 'btn btn-sm btn-outline-secondary' },
      { extend: 'excelHtml5', className: 'btn btn-sm btn-outline-secondary' }
    ]
  };

  // Inicializar DataTables en todas las tablas de tarjetas de videos (#example, #myt, #tblTodos)
  if (typeof $.fn.DataTable !== 'undefined') {
    $('.table-vid-cards').each(function () {
      if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable(dtOptions);
      }
    });

    if ($('#example').length && !$.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable(dtOptions);
    }
    if ($('#myt').length && !$.fn.DataTable.isDataTable('#myt')) {
      $('#myt').DataTable(dtOptions);
    }
    if ($('#tblTodos').length && !$.fn.DataTable.isDataTable('#tblTodos')) {
      $('#tblTodos').DataTable(dtOptions);
    }
  }

  // Listener para sincronizar búsqueda en tiempo real
  $(document).on('input keyup change paste', '#customVideoSearch', function () {
    syncCustomSearch(this.value);
  });

  // Restaurar vista de cuadrícula o lista guardada
  const savedView = localStorage.getItem('sagip_vid_view');
  if (savedView === 'list') {
    switchCardView('list');
  }
});

// Alternar entre Vista Cuadrícula (Grid) y Vista Lista (List)
function switchCardView(mode) {
  const gridBtn = document.getElementById('btnViewGrid');
  const listBtn = document.getElementById('btnViewList');
  const tables = document.querySelectorAll('.table-vid-cards');

  if (mode === 'list') {
    tables.forEach(table => table.classList.add('is-list-view'));
    if (gridBtn) gridBtn.classList.remove('active');
    if (listBtn) listBtn.classList.add('active');
    localStorage.setItem('sagip_vid_view', 'list');
  } else {
    tables.forEach(table => table.classList.remove('is-list-view'));
    if (gridBtn) gridBtn.classList.add('active');
    if (listBtn) listBtn.classList.remove('active');
    localStorage.setItem('sagip_vid_view', 'grid');
  }

  setTimeout(function () {
    if (typeof $.fn.dataTable !== 'undefined') {
      $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    }
  }, 100);
}

// Control de cambio de píldoras / pestañas
function filterByTab(tabName) {
  document.querySelectorAll('.btn-filter-pill').forEach(function (pill) {
    pill.classList.remove('active');
  });
  const currentPill = document.getElementById(tabName + '-tab');
  if (currentPill) {
    currentPill.classList.add('active');
  }

  // Si hay una búsqueda activa en el input, asegurar que se aplique al cambiar de pestaña
  const searchInput = document.getElementById('customVideoSearch');
  if (searchInput && searchInput.value) {
    syncCustomSearch(searchInput.value);
  }

  setTimeout(function () {
    if (typeof $.fn.dataTable !== 'undefined') {
      $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    }
  }, 150);
}

// Sincronizar búsqueda personalizada con los DataTables activos
function syncCustomSearch(query) {
  if (typeof $.fn.dataTable !== 'undefined') {
    $('.table-vid-cards').each(function () {
      if ($.fn.DataTable.isDataTable(this)) {
        $(this).DataTable().search(query).draw();
      }
    });
  }
}

// Sincronizar ordenamiento ascendente / descendente
function syncCustomSort(orderDir) {
  if (typeof $.fn.dataTable !== 'undefined') {
    $('.table-vid-cards').each(function () {
      if ($.fn.DataTable.isDataTable(this)) {
        $(this).DataTable().order([0, orderDir]).draw();
      }
    });
  }
}

// Disparar botones de exportación DataTables
function triggerDTExport(type) {
  const visibleTable = $('.tab-pane.active .table-vid-cards');
  if (visibleTable.length && typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable(visibleTable[0])) {
    const dtApi = visibleTable.DataTable();
    if (type === 'copy') dtApi.button('.buttons-copy').trigger();
    else if (type === 'csv') dtApi.button('.buttons-csv').trigger();
    else if (type === 'excel') dtApi.button('.buttons-excel').trigger();
  }
}

window.switchCardView = switchCardView;
window.filterByTab = filterByTab;
window.syncCustomSearch = syncCustomSearch;
window.syncCustomSort = syncCustomSort;
window.triggerDTExport = triggerDTExport;

document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tabEl) {
  tabEl.addEventListener('shown.bs.tab', function (event) {
    if (typeof $.fn.dataTable !== 'undefined') {
      $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    }
  });
});

// Auto-pausar video cuando se cierre cualquier modal de video
document.addEventListener('hidden.bs.modal', function (event) {
  const video = event.target.querySelector('video');
  if (video) {
    video.pause();
  }
});

const fileVid = document.querySelector('input[name="rutvid"]');
if (fileVid) {
  fileVid.addEventListener('change', function (e) {
    const file = this.files[0];
    if (file) {
      const pesInput = document.getElementById('pesvid');
      if (pesInput) pesInput.value = file.size;

      const videoEl = document.createElement('video');
      videoEl.preload = 'metadata';
      videoEl.onloadedmetadata = function () {
        window.URL.revokeObjectURL(videoEl.src);
        const sec = Math.round(videoEl.duration);
        const h = Math.floor(sec / 3600).toString().padStart(2, '0');
        const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
        const s = (sec % 60).toString().padStart(2, '0');
        const durInput = document.getElementById('durvid');
        if (durInput) durInput.value = `${h}:${m}:${s}`;
      };
      videoEl.src = URL.createObjectURL(file);
    }
  });
}
/* ----------------------Datatables Fin --------------*/

$(function () {
  $.widget("custom.combobox", {
    _create: function () {
      this.wrapper = $("<span>")
        .addClass("custom-combobox")
        .insertAfter(this.element);

      this.element.hide();
      this._createAutocomplete();
      this._createShowAllButton();
    },

    _createAutocomplete: function () {
      var selected = this.element.children(":selected"),
        value = selected.val() ? selected.text() : "";

      this.input = $("<input>")
        .appendTo(this.wrapper)
        .val(value)
        .attr("title", "")
        .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left form-select")
        .autocomplete({
          delay: 0,
          minLength: 0,
          source: this._source.bind(this)
        })
        .tooltip({
          classes: {
            "ui-tooltip": "ui-state-highlight"
          }
        });

      this._on(this.input, {
        autocompleteselect: function (event, ui) {
          ui.item.option.selected = true;
          this._trigger("select", event, {
            item: ui.item.option
          });
        },

        autocompletechange: "_removeIfInvalid"
      });
    },

    _createShowAllButton: function () {
      var input = this.input,
        wasOpen = false;

      $("<a>")
        .attr("tabIndex", -1)
        .attr("title", "Show All Items")
        .tooltip()
        .appendTo(this.wrapper)
        .button({
          icons: {
            primary: "ui-icon-triangle-1-s"
          },
          text: false
        })
        .removeClass("ui-corner-all")
        .addClass("custom-combobox-toggle ui-corner-right")
        .on("mousedown", function () {
          wasOpen = input.autocomplete("widget").is(":visible");
        })
        .on("click", function () {
          input.trigger("focus");

          // Close if already visible
          if (wasOpen) {
            return;
          }

          // Pass empty string as value to search for, displaying all results
          input.autocomplete("search", "");
        });
    },

    _source: function (request, response) {
      var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
      response(this.element.children("option").map(function () {
        var text = $(this).text();
        if (this.value && (!request.term || matcher.test(text)))
          return {
            label: text,
            value: text,
            option: this
          };
      }));
    },

    _removeIfInvalid: function (event, ui) {

      // Selected an item, nothing to do
      if (ui.item) {
        return;
      }

      // Search for a match (case-insensitive)
      var value = this.input.val(),
        valueLowerCase = value.toLowerCase(),
        valid = false;
      this.element.children("option").each(function () {
        if ($(this).text().toLowerCase() === valueLowerCase) {
          this.selected = valid = true;
          return false;
        }
      });

      // Found a match, nothing to do
      if (valid) {
        return;
      }

      // Remove invalid value
      this.input
        .val("")
        .attr("title", value + " didn't match any item")
        .tooltip("open");
      this.element.val("");
      this._delay(function () {
        this.input.tooltip("close").attr("title", "");
      }, 2500);
      this.input.autocomplete("instance").term = "";
    },

    _destroy: function () {
      this.wrapper.remove();
      this.element.show();
    }
  });

  $("#combobox").combobox();
  $("#toggle").on("click", function () {
    $("#combobox").toggle();
  });
});

$(document).ready(function () {
  $('#tblca').DataTable({
    "order": [[0, "desc"]],
    "language": {
      "decimal": "",
      "emptyTable": "No hay información",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
      "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
      "infoFiltered": "(Filtrado de _MAX_ total entradas)",
      "infoPostFix": "",
      "thousands": ",",
      "lengthMenu": "Mostrar _MENU_ Entradas",
      "loadingRecords": "Cargando...",
      "processing": "Procesando...",
      "search": "Buscar:",
      "zeroRecords": "Sin resultados encontrados",
      "paginate": {
        "first": "Primero",
        "last": "Ultimo",
        "next": "Siguiente",
        "previous": "Anterior"
      }
    }
  });
});

function solonum(e) {
  key = e.keyCode || e.which;
  teclado = String.fromCharCode(key);
  numeros = "0123456789";
  var especiales = ["8", "45"];
  teclado_especial = false;
  for (var i in especiales) {
    if (key == especiales[i]) {
      teclado_especial = true;
    }
  }
  if (numeros.indexOf(teclado) == -1 && !teclado_especial) {
    return false;
  }
}

function sololet(f) {
  key = f.keyCode || f.which;
  teclado = String.fromCharCode(key);
  letras = " abcdefghijklmnñopqrstuvwxyzáéíóú" + " ABCDEFGHIJKLMNÑOPQRSTUVWXYZÁÉÍÓÚ";
  especiales = "8-37-38-46";
  teclado_especial = false;
  for (var u in especiales) {
    if (key == especiales[u]) {
      teclado_especial = true; break;
    }
  }
  if (letras.indexOf(teclado) == -1 && !teclado_especial) {
    return false;
  }
}

function validadat() {
  p1 = document.getElementById("pas1").value;
  p2 = document.getElementById("pas2").value;
  if (!p1 || !p2 || p1 != p2) {
    alert("La contraseña no coincide.");
    return false;
  }
}

function eliminar(link) {
  event.preventDefault(); // Previene el comportamiento por defecto del enlace

  Swal.fire({
    title: '¿Está seguro de eliminar este registro?',
    text: "Verifique antes de continuar",
    icon: 'question',
    width: '500px',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    confirmButtonColor: '#00af00',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = link.href;
    }
  });

  return false;
}

function Eliminar(link) {
  event.preventDefault();

  Swal.fire({
    title: '¿Está seguro de eliminar este registro?',
    text: "Verifique antes de continuar",
    icon: 'question',
    width: '500px',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    confirmButtonColor: '#00af00',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = link.href;
    }
  });

  return false;
}

function ocul(mos = 0, est = 0) {
  if (mos == 1) {
    if (est == 1) {
      document.getElementById("frmins").style.display = "inherit";
      document.getElementById("mas").style.display = "none";
      document.getElementById("menos").style.display = "inline-block";
    } else {
      document.getElementById("frmins").style.display = "none";
      document.getElementById("mas").style.display = "inline-block";
      document.getElementById("menos").style.display = "none";
    }
  }
}

function ocudt(nu, alto, cdvl) {
  if (nu == 1) {
    ocuall(cdvl);
  }
  if (nu == 1) {
    document.getElementById(cdvl).style.display = "block";
    // document.getElementById(cdvl).style.position = "relative";
    // document.getElementById(cdvl).style.width = "100%";
    // document.getElementById(cdvl).style.height = alto;
    // document.getElementById(cdvl).style.transition = "all 1s";
    // document.getElementById(cdvl).style.opacity = "1";
    // document.getElementById(cdvl).style.left = "0px";
    document.getElementById("ocu" + cdvl).style.display = "block";
    //document.getElementById("mos"+cdvl).style.display = "none";
  } else {
    document.getElementById(cdvl).style.display = "none";
    // document.getElementById(cdvl).style.position = "absolute";
    // document.getElementById(cdvl).style.width = "0px";
    // document.getElementById(cdvl).style.height = "0px";
    // document.getElementById(cdvl).style.transition = "all 2s";
    // document.getElementById(cdvl).style.opacity = "0";
    // document.getElementById(cdvl).style.left = "-1000px";
    document.getElementById("ocu" + cdvl).style.display = "none";
    //document.getElementById("mos"+cdvl).style.display = "block";
    window.scroll(0, 0);
  }
}

function ocuall(cdvl) {
  //1001,1002,1003,1004,1101,1102,1103,1104,1105,1106,1107,1108,1110,1111,1112,1113,1114,1115,1116,1117
  //alert(cdvl);
  for (let u = 1001; u <= 1020; u++) {
    if (cdvl != u) {
      document.getElementById(u).style.display = "none";
      // document.getElementById(u).style.position = "absolute";
      // document.getElementById(u).style.width = "0px";
      // document.getElementById(u).style.height = "0px";
      // document.getElementById(u).style.transition = "all 2s";
      // document.getElementById(u).style.opacity = "0";
      // document.getElementById(u).style.left = "-1000px";
      window.scroll(0, 0);
    }
  }
}

function recCiudad(value) {
  //alert("Si le llega "+value);
  var parametros = {
    "valor": value
  };
  $.ajax({
    data: parametros,
    url: 'selmun.php',
    type: 'post',
    success: function (response) {
      $("#reloadMun").html(response);
    }
  });
}

//------------- Creación controles JS -----------------------
res = new Array();
val = new Array();

function registra(v, n) {
  res.push(v);
  val.push(n);
  mostrar();
}
function mostrar() {
  //var arv = res.toString();
  var micapa = document.getElementById('camp');
  var controles = '<input type="hidden" value="' + res.length + '" name="cant" /><br>';
  controles += '<table width="100%">';
  for (i = 0; i < res.length; i++) {
    //tb = document.getElementById("titbtn"+val[i]).innerHTML;
    controles += '<tr>';
    controles += '<td>' + (i + 1) + '.</td>';
    controles += '<td><input type="radio" name="valres[]" checked></td>';
    controles += '<td><input type="text" name="txtres[]" maxlength="200" required class="form-control"></td>';
    controles += '</tr>';
  }
  controles += '</table>';
  micapa.innerHTML = controles;
  //alert(arv);
}

function eliarr(n) {
  var t = n.toString();
  var index = res.indexOf(t);
  //alert(n+" - "+t+" - "+index);
  if (index > -1) {
    res.splice(index, 1);
    val.splice(index, 1);
  }
  mostrar();
}

function enfoque() {
  document.getElementById("inic").focus();
}

///ComboBoxCustom
$(function () {
  $.widget("custom.combobox", {
    _create: function () {
      this.wrapper = $("<span>")
        .addClass("custom-combobox")
        .insertAfter(this.element);

      this.element.hide();
      this._createAutocomplete();
      this._createShowAllButton();
    },

    _createAutocomplete: function () {
      var selected = this.element.children(":selected"),
        value = selected.val() ? selected.text() : "";

      this.input = $("<input>")
        .appendTo(this.wrapper)
        .val(value)
        .attr("title", "")
        .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
        .attr("style", "background-color: #fff;")
        .attr("placeholder", "Seleccione producto")
        .autocomplete({
          delay: 0,
          minLength: 0,
          source: $.proxy(this, "_source")
        })
        .tooltip({
          classes: {
            "ui-tooltip": "ui-state-highlight"
          }
        });

      this._on(this.input, {
        autocompleteselect: function (event, ui) {
          ui.item.option.selected = true;
          this._trigger("select", event, {
            item: ui.item.option
          });
        },

        autocompletechange: "_removeIfInvalid"
      });
    },

    _createShowAllButton: function () {
      var input = this.input,
        wasOpen = false;

      $("<a>")
        .attr("tabIndex", -1)
        .attr("title", "Show All Items")
        .tooltip()
        .appendTo(this.wrapper)
        .button({
          icons: {
            primary: "ui-icon-triangle-1-s"
          },
          text: false
        })
        .removeClass("ui-corner-all")
        .addClass("custom-combobox-toggle ui-corner-right")
        .on("mousedown", function () {
          wasOpen = input.autocomplete("widget").is(":visible");
        })
        .on("click", function () {
          input.trigger("focus");

          // Close if already visible
          if (wasOpen) {
            return;
          }

          // Pass empty string as value to search for, displaying all results
          input.autocomplete("search", "");
        });
    },

    _source: function (request, response) {
      var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
      response(this.element.children("option").map(function () {
        var text = $(this).text();
        if (this.value && (!request.term || matcher.test(text)))
          return {
            label: text,
            value: text,
            option: this
          };
      }));
    },

    _removeIfInvalid: function (event, ui) {

      // Selected an item, nothing to do
      if (ui.item) {
        return;
      }

      // Search for a match (case-insensitive)
      var value = this.input.val(),
        valueLowerCase = value.toLowerCase(),
        valid = false;
      this.element.children("option").each(function () {
        if ($(this).text().toLowerCase() === valueLowerCase) {
          this.selected = valid = true;
          return false;
        }
      });

      // Found a match, nothing to do
      if (valid) {
        return;
      }

      // Remove invalid value
      this.input
        .val("")
        .attr("title", value + " didn't match any item")
        .tooltip("open");
      this.element.val("");
      this._delay(function () {
        this.input.tooltip("close").attr("title", "");
      }, 2500);
      this.input.autocomplete("instance").term = "";
    },

    _destroy: function () {
      this.wrapper.remove();
      this.element.show();
    }
  });

  $("#combobox").combobox();
  $("#toggle").on("click", function () {
    $("#combobox").toggle();
  });
});

// $( "#combobox1" ).combobox();
//     $( "#toggle1" ).on( "click", function() {
//       $( "#combobox1" ).toggle();
//     });
// } );

function nFfin(ff) {
  const fecha = new Date(ff);

  const fechaMas1Mes = new Date(fecha);
  fechaMas1Mes.setMonth(fechaMas1Mes.getMonth() + 1);
  const fechaMas3Meses = new Date(fecha);
  fechaMas3Meses.setMonth(fechaMas3Meses.getMonth() + 3);

  // Formatear en formato YYYY-MM-DD
  const formatearFecha = (f) => {
    const anio = f.getFullYear();
    const mes = String(f.getMonth() + 1).padStart(2, '0');
    const dia = String(f.getDate()).padStart(2, '0');
    return `${anio}-${mes}-${dia}`;
  };

  // Asignar al input
  const input = document.getElementById("feclin");
  if (input) {
    input.value = formatearFecha(fechaMas1Mes);
    input.max = formatearFecha(fechaMas3Meses);
  }
}

//Buscador...


let buscador = document.getElementById("buscador");
if (buscador) {
  let timer;
  let resultados = document.getElementById("resultados");
  buscador.addEventListener("keyup", function () {
    clearTimeout(timer);
    const query = this.value.trim();

    if (query.length < 2) {
      resultados.style.display = "none";
      resultados.innerHTML = '';
      return;
    }

    timer = setTimeout(() => {
      fetch("controllers/buscadormod.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
          const resultados = document.getElementById("resultados");
          resultados.innerHTML = "";

          if (data.length > 0) {
            resultados.style.display = "block";
            data.forEach(op => {
              const li = document.createElement("li");
              li.classList.add("result-item");
              li.textContent = op.nompag + " (Módulo: " + op.nommod + ")";
              li.onclick = () => {
                const form = document.getElementById("form-" + op.idmod);
                if (form) {
                  form.querySelector("input[name='pg']").value = op.idpag;
                  form.submit();
                }
              };
              resultados.appendChild(li);
            });
          } else {
            resultados.style.display = "block";
            resultados.innerHTML = "<li class='result-empty'>No se encontraron resultados para \"" + query + "\"</li>";
          }
        });
    }, 500);
  });
  // Ocultar resultados cuando se pierde el foco
  buscador.addEventListener("blur", () => {
    setTimeout(() => {
      resultados.style.display = "none";
    }, 200);
  });

  // Mostrar resultados si vuelve a tener foco y ya hay contenido
  buscador.addEventListener("focus", () => {
    if (resultados.innerHTML.trim() !== "") {
      resultados.style.display = "block";
    }
  });
}